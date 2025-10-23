<?php
// auth_controller.php

/**
 * Fungsi untuk memulai sesi dengan nama unik berdasarkan peran (role).
 * Ini mencegah sesi Admin dan Kabag saling menimpa.
 * @param string $role_name Nama peran (Contoh: 'Admin', 'Kabag')
 */
function startSessionByRole($role_name) {
    // Tentukan nama sesi berdasarkan role (Contoh: 'PKB_ADMIN', 'PKB_KABAG')
    $session_prefix = 'PKB_';
    $session_name = $session_prefix . strtoupper($role_name);

    // HANYA jika belum ada sesi yang berjalan
    if (session_status() === PHP_SESSION_NONE) {
        // PENTING: Panggil session_name() sebelum session_start()
        if (session_id() === '') {
            session_name($session_name);
            session_start();
        }
    }
}

require_once 'db_connection.php';
$base_path = '/upt-pkb/'; // SESUAIKAN DENGAN NAMA FOLDER PROYEK ANDA!
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
    global $pdo, $base_path;
    
    // 1. Ambil data user dari database berdasarkan username dan role
    $stmt = $pdo->prepare("SELECT id_user, username, password_hash, nama_lengkap, role 
                           FROM user_backend 
                           WHERE username = ? AND role = ?");
    $stmt->execute([$username, $required_role]);
    $user_data = $stmt->fetch();

    if ($user_data) {
        // 2. Verifikasi password ter-hash
        if (password_verify($password, $user_data['password_hash'])) {
            
            // 3. Set variabel sesi (Jika autentikasi berhasil)
            $_SESSION['is_logged_in'] = true;
            $_SESSION['user_id'] = $user_data['id_user'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['nama_lengkap'] = $user_data['nama_lengkap'];
            $_SESSION['role'] = $user_data['role']; // Simpan peran di sesi

            // 4. Lakukan Redirect
            $redirect_folder = ($user_data['role'] == 'Petugas') ? 'kabag' : 'admin';
            
            // PENTING: Gunakan variabel $base_path yang sudah didefinisikan global
            header("Location: {$base_path}{$redirect_folder}/index.php"); 
            exit();
        } else {
            // Password salah
            return ['status' => false, 'message' => 'Username atau Password salah.'];
        }
    } 
    // Username tidak ditemukan atau role tidak sesuai
    return ['status' => false, 'message' => 'Username atau Password salah atau Anda tidak memiliki akses.'];
}

/**
 * Fungsi untuk memeriksa status login dan hak akses.
 * @param string $required_role Peran yang dibutuhkan untuk mengakses halaman ini
 * @param string $redirect_path Path folder tempat user seharusnya berada ('admin' atau 'kabag')
 */
function checkLoginAndRole($required_role, $redirect_folder) {
    global $base_path; // <--- KRITIS: Akses base path yang sudah didefinisikan

    // 1. Cek status login
    if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
        
        // Jika belum login, arahkan ke halaman login di folder yang sesuai
        // Contoh: /upt-pkb/admin/login.php atau /upt-pkb/kabag/login.php
        header("Location: {$base_path}{$redirect_folder}/login.php"); 
        exit();
    }

    // 2. Cek hak akses (Role Guard)
    if ($_SESSION['role'] !== $required_role) {
        
        // Jika peran tidak sesuai, arahkan ke dashboard yang benar berdasarkan role user di sesi
        $correct_folder = ($_SESSION['role'] == 'Admin') ? 'admin' : 'kabag';
        
        // Contoh: /upt-pkb/admin/index.php atau /upt-pkb/kabag/index.php
        header("Location: {$base_path}{$correct_folder}/index.php"); 
        exit();
    }
}

/**
 * Fungsi untuk menghancurkan sesi dan mengakhiri status login.
 */
function logoutUser($redirect_folder) { // <-- Fungsi menerima folder tujuan
    global $base_path; 

    // Tidak perlu lagi membaca $_SESSION['role'] karena sudah dihancurkan atau tidak dapat diandalkan
    // $current_role = ($_SESSION['role'] ?? 'Admin'); 
    
    $current_session_name = session_name();
    
    // 1. Hancurkan sesi (tetap di sini)
    $_SESSION = array(); 
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie($current_session_name, '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    // 2. Redirect ke halaman login yang sesuai, menggunakan folder yang diteruskan
    header("Location: {$base_path}{$redirect_folder}/login.php"); 
    exit();
}