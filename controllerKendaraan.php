<?php
require_once 'db_connection.php';
require_once 'auth_controller.php';
require_once 'controllerStafPelayanan.php'; // Diperlukan untuk mengambil daftar staf

/**
 * --------------------------------
 * FUNGSI UNTUK ADMIN (Input Data)
 * --------------------------------
 */

/**
 * R (Read Single FIFO): Mengambil data kendaraan yang paling lama menunggu survei.
 * @return array|false Data kendaraan dengan nama Admin (JOIN) atau false jika tidak ada yang menunggu.
 */
function readKendaraanFIFO() {
    global $pdo;

    try {
        // Query mengambil kendaraan dengan waktu_input terlama (First-In) dan status 'Menunggu'
        $sql = "SELECT k.id_kendaraan, k.plat_nomor, k.jenis_kendaraan, k.waktu_input,
                       u.nama_lengkap AS nama_admin, u.id_user
                FROM kendaraan k
                JOIN user_backend u ON k.id_user = u.id_user
                WHERE k.status_survey = 'Menunggu'
                ORDER BY k.waktu_input ASC
                LIMIT 1";
        
        $stmt = $pdo->query($sql);
        $data = $stmt->fetch();
        
        return $data;

    } catch (PDOException $e) {
        error_log("Error reading FIFO data: " . $e->getMessage());
        return false;
    }
}

/**
 * C (Create): Menambahkan data kendaraan baru (diinput oleh Admin).
 * @param string $plat_nomor Plat nomor kendaraan
 * @param string $jenis_kendaraan Jenis kendaraan
 * @param int $id_staf ID Staf Pelayanan yang bertugas
 * @return array Hasil status operasi
 */
function createKendaraan($plat_nomor, $jenis_kendaraan, $id_user) {
    global $pdo;
    
    // Validasi dasar
    if (empty($plat_nomor) || empty($jenis_kendaraan) || !is_numeric($id_user)) {
        // Pesan error diubah sesuai kebutuhan
        return ['status' => false, 'message' => 'Data input atau ID Admin tidak valid.'];
    }

    try {
        // Query diubah, sekarang menyimpan id_user (Admin yang login)
        $sql = "INSERT INTO kendaraan (plat_nomor, jenis_kendaraan, id_user, status_survey) 
                VALUES (?, ?, ?, 'Menunggu')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$plat_nomor, $jenis_kendaraan, $id_user]);
        
        return ['status' => true, 'message' => 'Data kendaraan berhasil diinput dan siap untuk disurvei.'];
        
    } catch (PDOException $e) {
        return ['status' => false, 'message' => 'Gagal menginput data: ' . $e->getMessage()];
    }
}

/**
 * --------------------------------
 * FUNGSI UNTUK KIOSK USER (Read Data)
 * --------------------------------
 */

/**
 * R (Read All Menunggu): Mengambil semua data kendaraan yang statusnya 'Menunggu' survey.
 * Data ini ditampilkan di layar Kiosk utama.
 * @return array Daftar kendaraan yang menunggu
 */
function readKendaraanMenunggu() {
    global $pdo;
    
    try {
        // Mengambil data kendaraan yang statusnya 'Menunggu'
        // dan melakukan JOIN untuk menampilkan NAMA STAF yang melayani
        $sql = "SELECT k.id_kendaraan, k.plat_nomor, k.jenis_kendaraan, s.nama_staf 
                FROM kendaraan k
                JOIN staf_pelayanan s ON k.id_staf = s.id_staf
                WHERE k.status_survey = 'Menunggu'
                ORDER BY k.waktu_input ASC"; 
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error reading waiting vehicles: " . $e->getMessage());
        return [];
    }
}

/**
 * R (Read Single): Mengambil satu data kendaraan berdasarkan ID.
 * Digunakan saat user memilih plat nomor untuk survei.
 * @param int $id_kendaraan ID kendaraan yang dipilih
 * @return array Data kendaraan dan staf yang melayani
 */
function readSingleKendaraan($id_kendaraan) {
    global $pdo;

    if (!is_numeric($id_kendaraan)) return false;
    
    try {
        $sql = "SELECT k.id_kendaraan, k.plat_nomor, k.jenis_kendaraan, s.nama_staf 
                FROM kendaraan k
                JOIN staf_pelayanan s ON k.id_staf = s.id_staf
                WHERE k.id_kendaraan = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_kendaraan]);
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Error reading single vehicle: " . $e->getMessage());
        return false;
    }
}

// Catatan: Fungsi Update dan Delete Kendaraan (CRUD Admin) dapat ditambahkan di sini, 
// namun prioritas utama Anda adalah Input dan Read untuk Survei.
?>