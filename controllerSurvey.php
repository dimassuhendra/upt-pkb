<?php
// survey_controller.php

require_once 'db_connection.php';

/**
 * --------------------------------
 * FUNGSI UNTUK KIOSK USER (Input Respon)
 * --------------------------------
 */

/**
 * C (Create): Menyimpan hasil survey dari pengguna dan memperbarui status kendaraan.
 * @param int $id_kendaraan ID kendaraan yang disurvei
 * @param int $r_pelayanan Rating Pelayanan (1-5)
 * @param int $r_fasilitas Rating Fasilitas (1-5)
 * @param int $r_kecepatan Rating Kecepatan (1-5)
 * @param string $komentar Komentar tambahan (opsional)
 * @return array Hasil status operasi
 */
function submitSurvey($id_kendaraan, $pelayanan, $fasilitas, $kecepatan, $komentar = null) {
    global $pdo;

    // 1. Validasi Dasar
    if (!is_numeric($id_kendaraan) || $id_kendaraan <= 0) {
        return ['status' => false, 'message' => 'ID Kendaraan tidak valid.'];
    }
    if ($pelayanan < 1 || $pelayanan > 5 || $fasilitas < 1 || $fasilitas > 5 || $kecepatan < 1 || $kecepatan > 5) {
        return ['status' => false, 'message' => 'Rating harus antara 1 sampai 5.'];
    }

    try {
        $pdo->beginTransaction();

        // 2. INSERT Data ke Tabel survey
        $sql_insert = "INSERT INTO survey 
                       (id_kendaraan, rating_pelayanan, rating_fasilitas, rating_kecepatan, komentar) 
                       VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$id_kendaraan, $pelayanan, $fasilitas, $kecepatan, $komentar]);

        // 3. UPDATE Status di Tabel kendaraan
        $sql_update = "UPDATE kendaraan 
                       SET status_survey = 'Selesai' 
                       WHERE id_kendaraan = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$id_kendaraan]);

        $pdo->commit();
        
        return ['status' => true, 'message' => 'Survei berhasil dikirim.'];

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Survey Submission Error: " . $e->getMessage());
        return ['status' => false, 'message' => 'Gagal menyimpan data survei: ' . $e->getMessage()];
    }
}