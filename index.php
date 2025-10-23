<?php
// index.php (ROOT FOLDER)

// LANGKAH 1: Set header untuk mengarahkan pengguna ke folder user/kiosk
header('Location: user/index.php');

// LANGKAH 2: Pastikan tidak ada kode lain yang dieksekusi setelah redirect
exit();

// Catatan: Jika Anda ingin mempertahankan fungsionalitas sesi di root untuk alasan debugging 
// atau jika ada admin yang secara tidak sengaja mengakses root, Anda bisa menambahkan ini:
/*
session_start();
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    if ($_SESSION['role'] === 'Admin') {
        header('Location: admin/index.php');
        exit();
    } elseif ($_SESSION['role'] === 'Petugas') {
        header('Location: kabag/index.php');
        exit();
    }
}
header('Location: user/index.php');
exit();
*/
?>