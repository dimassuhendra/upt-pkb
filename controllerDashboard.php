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
function getSummaryStatistics() {
    global $pdo;

    try {
        // Query 1: Total Survey Selesai
        $sql_total = "SELECT COUNT(*) AS total_surveys FROM survey";
        $total_surveys = $pdo->query($sql_total)->fetchColumn();

        // Query 2: Rata-rata Rating Global (dari semua aspek)
        $sql_avg = "SELECT 
                        AVG(rating_pelayanan) AS avg_pelayanan,
                        AVG(rating_fasilitas) AS avg_fasilitas,
                        AVG(rating_kecepatan) AS avg_kecepatan
                    FROM survey";
        $avg_ratings = $pdo->query($sql_avg)->fetch();

        return [
            'total_surveys' => (int)$total_surveys,
            'avg_pelayanan' => round((float)$avg_ratings['avg_pelayanan'] ?? 0, 2),
            'avg_fasilitas' => round((float)$avg_ratings['avg_fasilitas'] ?? 0, 2),
            'avg_kecepatan' => round((float)$avg_ratings['avg_kecepatan'] ?? 0, 2),
        ];

    } catch (PDOException $e) {
        error_log("Error in getSummaryStatistics: " . $e->getMessage());
        return [
            'total_surveys' => 0, 'avg_pelayanan' => 0, 
            'avg_fasilitas' => 0, 'avg_kecepatan' => 0
        ];
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