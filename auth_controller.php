<?php
// auth_controller.php

require_once 'db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * --------------------------------
 * FUNGSI OTENTIKASI
 * --------------------------------
 */

/**
 * Fungsi untuk memverifikasi kredensial dan memulai sesi berdasarkan peran.
 * @param string $username Username yang dimasukkan
 * @param string $password Password yang dimasukkan
 * @param string $required_role Peran yang diharapkan ('Admin' atau 'Petugas')
 * @return array Hasil status login
 */
function loginUserBackend($username, $password, $required_role) {
    global $pdo;
    
    // 1. Ambil data user dari database berdasarkan username dan role
    $stmt = $pdo->prepare("SELECT id_user, username, password_hash, nama_lengkap, role 
                           FROM user_backend 
                           WHERE username = ? AND role = ?");
    $stmt->execute([$username, $required_role]);
    $user_data = $stmt->fetch();

    if ($user_data) {
        // 2. Verifikasi password ter-hash
        if (password_verify($password, $user_data['password_hash'])) {
            
            // 3. Set variabel sesi
            $_SESSION['is_logged_in'] = true;
            $_SESSION['user_id'] = $user_data['id_user'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['nama_lengkap'] = $user_data['nama_lengkap'];
            $_SESSION['role'] = $user_data['role']; // Simpan peran di sesi

            return ['status' => true, 'message' => 'Login Berhasil!'];

        } else {
            return ['status' => false, 'message' => 'Username atau Password salah.'];
        }
    } else {
        return ['status' => false, 'message' => 'Username atau Password salah atau Anda tidak memiliki akses ke halaman ini.'];
    }
}

/**
 * Fungsi untuk memeriksa status login dan hak akses.
 * @param string $required_role Peran yang dibutuhkan untuk mengakses halaman ini
 * @param string $redirect_path Path folder tempat user seharusnya berada ('admin' atau 'kabag')
 */
function checkLoginAndRole($required_role, $redirect_path) {
    // Cek status login
    if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
        // Jika belum login, arahkan ke halaman login di folder yang sesuai
        header("Location: {$redirect_path}/login.php"); 
        exit();
    }

    // Cek hak akses
    if ($_SESSION['role'] !== $required_role) {
        // Jika peran tidak sesuai, arahkan ke dashboard yang benar atau halaman error
        $correct_path = ($_SESSION['role'] == 'Admin') ? 'admin' : 'kabag';
        header("Location: /{$correct_path}/index.php"); // Ganti dengan path root yang sesuai
        exit();
    }
}

/**
 * Fungsi untuk menghancurkan sesi dan mengakhiri status login.
 */
function logoutUser() {
    $current_role = $_SESSION['role'] ?? 'Admin'; // Ambil role untuk redirect

    $_SESSION = array();
    session_destroy();
    
    // Tentukan folder redirect
    $redirect_folder = ($current_role == 'Admin') ? 'admin' : 'kabag';

    // PERBAIKAN PATH REDIRECT: Menggunakan path absolut dari root server
    // Ganti '/upt-pkb/' jika nama folder proyek Anda berbeda
    $base_path = '/upt-pkb/'; 
    
    header("Location: {$base_path}{$redirect_folder}/login.php");
    exit();
}