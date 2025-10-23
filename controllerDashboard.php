<?php
// dashboard_controller.php

require_once 'db_connection.php';
require_once 'auth_controller.php'; 

/**
 * --------------------------------
 * FUNGSI ANALISIS & DASHBOARD
 * --------------------------------
 */

/**
 * Mendapatkan ringkasan statistik umum (Total Survey, Rata-rata Rating Global).
 * Fungsi ini cocok untuk Dashboard Admin dan Petugas (Supervisor).
 * @return array Data ringkasan statistik
 */

const SURVEY_TABLE = 'survey'; 
const KENDARAAN_TABLE = 'kendaraan';

// =========================================================================

/**
 * Mengambil data kinerja Admin (jumlah survei yang diselesaikan)
 * berdasarkan rentang waktu yang spesifik.
 *
 * @param string $period 'daily', 'weekly', atau 'monthly'
 * @return array Data kinerja per admin
 */

function getAdminPerformanceByPeriod($period) {
    global $pdo;
    $surveyTable = SURVEY_TABLE;
    $kendaraanTable = KENDARAAN_TABLE;
    $where_period = '';

    // 1. Tentukan batasan waktu (Logika tanggal sudah diperbaiki dan dipertahankan)
    switch ($period) {
        case 'daily':
            $where_period = "AND s.tanggal_survey >= DATE(NOW()) AND s.tanggal_survey < DATE_ADD(DATE(NOW()), INTERVAL 1 DAY)";
            break;
        case 'weekly':
            $where_period = "AND s.tanggal_survey >= DATE_SUB(DATE(NOW()), INTERVAL 7 DAY)";
            break;
        case 'monthly':
            $where_period = "AND YEAR(s.tanggal_survey) = YEAR(NOW()) AND MONTH(s.tanggal_survey) = MONTH(NOW())";
            break;
        default:
            return [];
    }
    
    // 2. Query Utama dengan JOIN 3 Tabel
    $sql = "SELECT 
                u.nama_lengkap AS admin_name,
                COUNT(s.id_survey) AS total_surveys
            FROM 
                user_backend u
            LEFT JOIN 
                $kendaraanTable k ON u.id_user = k.id_user -- User Input Kendaraan
            LEFT JOIN 
                $surveyTable s ON k.id_kendaraan = s.id_kendaraan -- Kendaraan Punya Survey
                $where_period -- Filter waktu diterapkan pada tabel survey
            WHERE 
                u.role = 'Admin'
            GROUP BY 
                u.id_user, u.nama_lengkap
            ORDER BY 
                total_surveys DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error di getAdminPerformanceByPeriod: " . $e->getMessage());
        return [];
    }
}


function getSummaryStatistics() {
    global $pdo;
    $surveyTable = SURVEY_TABLE;

    // Statistik Ringkasan Global tidak perlu JOIN 3 tabel
    try {
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) AS total_surveys,
                COALESCE(AVG(rating_pelayanan), 0) AS avg_pelayanan,
                COALESCE(AVG(rating_fasilitas), 0) AS avg_fasilitas,
                COALESCE(AVG(rating_kecepatan), 0) AS avg_kecepatan
            FROM $surveyTable
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
         error_log("Error di getSummaryStatistics: " . $e->getMessage());
        return ['total_surveys' => 0, 'avg_pelayanan' => 0, 'avg_fasilitas' => 0, 'avg_kecepatan' => 0];
    }
}

/**
 * FUNGSI INI MENGUKUR KINERJA ADMIN (bukan Staf Pelayanan)
 * Hasilnya akan digunakan di tabel bagian bawah dashboard.
 * * FIX: Menggunakan JOIN 3 tabel (User -> Kendaraan -> Survey)
 * * @return array Data kinerja per Admin
 */
function getPerformanceByAdmin() {
    global $pdo;
    $surveyTable = SURVEY_TABLE;
    $kendaraanTable = KENDARAAN_TABLE;
    
    $sql = "
        SELECT 
            u.nama_lengkap AS nama_staf, -- Admin dianggap 'staf' yang melakukan input
            COUNT(s.id_survey) AS total_survey_dikerjakan,
            ROUND(COALESCE(AVG(s.rating_pelayanan), 0), 2) AS avg_rating_pelayanan,
            ROUND(COALESCE(AVG(s.rating_fasilitas), 0), 2) AS avg_rating_fasilitas,
            ROUND(COALESCE(AVG(s.rating_kecepatan), 0), 2) AS avg_rating_kecepatan,
            ROUND(COALESCE( (AVG(s.rating_pelayanan) + AVG(s.rating_fasilitas) + AVG(s.rating_kecepatan)) / 3, 0), 2) AS avg_rating_total
        FROM user_backend u
        LEFT JOIN $kendaraanTable k ON u.id_user = k.id_user -- Join 1: User ke Kendaraan
        LEFT JOIN $surveyTable s ON k.id_kendaraan = s.id_kendaraan -- Join 2: Kendaraan ke Survey
        WHERE u.role = 'Admin'
        GROUP BY u.id_user, u.nama_lengkap
        ORDER BY avg_rating_total DESC
    ";
    
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error di getPerformanceByAdmin: " . $e->getMessage());
        return [];
    }
}

/**
 * Mendapatkan data kinerja per Staf Pelayanan.
 * Cocok untuk Dashboard Petugas (Supervisor) untuk evaluasi kinerja.
 * @return array Daftar staf dengan rata-rata rating masing-masing
 */
function getPerformanceByStaf() {
    global $pdo;

    try {
        $sql = "SELECT 
                    s.nama_staf,
                    COUNT(sv.id_survey) AS total_survey_dikerjakan,
                    ROUND(AVG(sv.rating_pelayanan), 2) AS avg_rating_pelayanan,
                    ROUND(AVG(sv.rating_fasilitas), 2) AS avg_rating_fasilitas,
                    ROUND(AVG((sv.rating_pelayanan + sv.rating_fasilitas + sv.rating_kecepatan) / 3), 2) AS avg_rating_total
                FROM staf_pelayanan s
                JOIN kendaraan k ON s.id_staf = k.id_staf
                JOIN survey sv ON k.id_kendaraan = sv.id_kendaraan
                GROUP BY s.id_staf, s.nama_staf
                ORDER BY avg_rating_total DESC"; // Urutkan berdasarkan kinerja terbaik
                
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log("Error in getPerformanceByStaf: " . $e->getMessage());
        return [];
    }
}

/**
 * Mendapatkan semua data mentah hasil survey (dengan JOIN ke kendaraan dan staf).
 * Cocok untuk keperluan laporan Admin dan fitur Export.
 * @param array $filters Array filter (e.g., ['date_start' => '2025-01-01', 'id_staf' => 1])
 * @return array Daftar data survey lengkap
 */
function getRawSurveyData($filters = []) {
    global $pdo;

    try {
        $sql = "SELECT 
                    sv.waktu_isi, 
                    k.plat_nomor, 
                    k.jenis_kendaraan, 
                    s.nama_staf,
                    sv.rating_pelayanan,
                    sv.rating_fasilitas,
                    sv.rating_kecepatan,
                    sv.komentar
                FROM survey sv
                JOIN kendaraan k ON sv.id_kendaraan = k.id_kendaraan
                JOIN staf_pelayanan s ON k.id_staf = s.id_staf
                WHERE 1=1";
        
        $params = [];
        
        // Menambahkan filter
        if (!empty($filters['date_start'])) {
            $sql .= " AND DATE(sv.waktu_isi) >= ?";
            $params[] = $filters['date_start'];
        }
        if (!empty($filters['date_end'])) {
            $sql .= " AND DATE(sv.waktu_isi) <= ?";
            $params[] = $filters['date_end'];
        }
        if (!empty($filters['id_staf'])) {
            $sql .= " AND s.id_staf = ?";
            $params[] = $filters['id_staf'];
        }

        $sql .= " ORDER BY sv.waktu_isi DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log("Error in getRawSurveyData: " . $e->getMessage());
        return [];
    }
}

/**
 * Mengambil data kinerja Admin (jumlah survei yang diselesaikan)
 * berdasarkan rentang waktu yang spesifik.
 *
 * @param string $period 'daily', 'weekly', atau 'monthly'
 * @return array Data kinerja per admin
 */
// function getAdminPerformanceByPeriod($period) {
//     global $pdo;
//     $sql = '';
    
//     // Tentukan batasan waktu (semua periode dibandingkan dengan tanggal hari ini)
//     switch ($period) {
//         case 'daily':
//             // Total hari ini
//             $sql = "SELECT 
//                         u.nama_lengkap AS admin_name,
//                         COUNT(s.id_survey) AS total_surveys
//                     FROM 
//                         user_backend u
//                     LEFT JOIN 
//                         survey_data s ON u.id_user = s.id_admin 
//                         AND DATE(s.tanggal_survey) = CURDATE()
//                     WHERE 
//                         u.role = 'Admin'
//                     GROUP BY 
//                         u.id_user, u.nama_lengkap
//                     ORDER BY 
//                         total_surveys DESC";
//             break;

//         case 'weekly':
//             // Total 7 hari terakhir
//             $sql = "SELECT 
//                         u.nama_lengkap AS admin_name,
//                         COUNT(s.id_survey) AS total_surveys
//                     FROM 
//                         user_backend u
//                     LEFT JOIN 
//                         survey_data s ON u.id_user = s.id_admin 
//                         AND s.tanggal_survey >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
//                     WHERE 
//                         u.role = 'Admin'
//                     GROUP BY 
//                         u.id_user, u.nama_lengkap
//                     ORDER BY 
//                         total_surveys DESC";
//             break;

//         case 'monthly':
//             // Total bulan ini
//             $sql = "SELECT 
//                         u.nama_lengkap AS admin_name,
//                         COUNT(s.id_survey) AS total_surveys
//                     FROM 
//                         user_backend u
//                     LEFT JOIN 
//                         survey_data s ON u.id_user = s.id_admin 
//                         AND YEAR(s.tanggal_survey) = YEAR(CURDATE())
//                         AND MONTH(s.tanggal_survey) = MONTH(CURDATE())
//                     WHERE 
//                         u.role = 'Admin'
//                     GROUP BY 
//                         u.id_user, u.nama_lengkap
//                     ORDER BY 
//                         total_surveys DESC";
//             break;
            
//         default:
//             return [];
//     }

//     $stmt = $pdo->prepare($sql);
//     $stmt->execute();
//     return $stmt->fetchAll(PDO::FETCH_ASSOC);
// }