<?php
// user_management_controller.php

require_once 'db_connection.php';

/**
 * --------------------------------
 * CRUD User Backend (Admin/Petugas)
 * --------------------------------
 */

/**
 * C (Create): Menambahkan pengguna backend baru.
 * Digunakan oleh Kabag untuk membuat akun Admin baru.
 * @param string $username Username
 * @param string $password Password (belum di-hash)
 * @param string $nama_lengkap Nama lengkap
 * @param string $role Peran ('Admin' atau 'Petugas')
 * @return array Hasil status operasi
 */
function createUserBackend($username, $password, $nama_lengkap, $role) {
    global $pdo;
    
    // Validasi input
    if (empty($username) || empty($password) || empty($nama_lengkap) || !in_array($role, ['Admin', 'Petugas'])) {
        return ['status' => false, 'message' => 'Semua field wajib diisi dan peran harus valid.'];
    }

    try {
        // Hashing Password sebelum disimpan
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO user_backend (username, password_hash, nama_lengkap, role) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $password_hash, $nama_lengkap, $role]);
        
        return ['status' => true, 'message' => 'Akun baru berhasil ditambahkan.'];
        
    } catch (PDOException $e) {
        // Cek error duplikasi username (kode SQLSTATE 23000)
        if ($e->getCode() == 23000) {
            return ['status' => false, 'message' => 'Gagal: Username sudah digunakan.'];
        }
        return ['status' => false, 'message' => 'Gagal menambahkan user: ' . $e->getMessage()];
    }
}

/**
 * R (Read All): Mengambil semua data pengguna backend.
 * Cocok untuk menampilkan tabel manajemen akun Admin.
 * @return array Daftar semua user atau array kosong
 */
function readAllUserBackend() {
    global $pdo;
    
    try {
        // Tidak mengambil password_hash untuk keamanan
        $sql = "SELECT id_user, username, nama_lengkap, role FROM user_backend ORDER BY role ASC, nama_lengkap ASC";
        $stmt = $pdo->query($sql);
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error reading users: " . $e->getMessage());
        return [];
    }
}

/**
 * R (Read Single): Mengambil satu data pengguna berdasarkan ID.
 * Digunakan untuk mengisi form Update.
 * @param int $id_user ID user yang dicari
 * @return array Data user atau false jika tidak ditemukan
 */
function readSingleUserBackend($id_user) {
    global $pdo;

    if (!is_numeric($id_user)) {
        return false;
    }
    
    try {
        // Tidak mengambil password_hash
        $sql = "SELECT id_user, username, nama_lengkap, role FROM user_backend WHERE id_user = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_user]);
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Error reading single user: " . $e->getMessage());
        return false;
    }
}

/**
 * U (Update): Memperbarui data pengguna (nama, username, peran, dan/atau password).
 * @param int $id_user ID user yang akan diubah
 * @param string $username Username baru
 * @param string $nama_lengkap Nama lengkap baru
 * @param string $role Peran baru
 * @param string|null $new_password Password baru (opsional, jika tidak diisi biarkan null)
 * @return array Hasil status operasi
 */
function updateUserBackend($id_user, $username, $nama_lengkap, $role, $new_password = null) {
    global $pdo;

    if (!is_numeric($id_user) || empty($username) || empty($nama_lengkap) || !in_array($role, ['Admin', 'Petugas'])) {
        return ['status' => false, 'message' => 'Data input tidak valid.'];
    }
    
    try {
        $sql = "UPDATE user_backend SET username = ?, nama_lengkap = ?, role = ?";
        $params = [$username, $nama_lengkap, $role];

        // Jika ada password baru, tambahkan ke query dan parameter
        if (!empty($new_password)) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql .= ", password_hash = ?";
            $params[] = $password_hash;
        }

        $sql .= " WHERE id_user = ?";
        $params[] = $id_user;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() > 0) {
            return ['status' => true, 'message' => 'Akun berhasil diperbarui.'];
        } else {
            return ['status' => true, 'message' => 'Tidak ada perubahan data atau ID user tidak ditemukan.'];
        }
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            return ['status' => false, 'message' => 'Gagal: Username sudah digunakan oleh akun lain.'];
        }
        return ['status' => false, 'message' => 'Gagal memperbarui user: ' . $e->getMessage()];
    }
}

/**
 * D (Delete): Menghapus data pengguna dari database.
 * @param int $id_user ID user yang akan dihapus
 * @return array Hasil status operasi
 */
function deleteUserBackend($id_user) {
    global $pdo;

    if (!is_numeric($id_user)) {
        return ['status' => false, 'message' => 'ID user tidak valid.'];
    }

    try {
        $sql = "DELETE FROM user_backend WHERE id_user = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_user]);
        
        if ($stmt->rowCount() > 0) {
            return ['status' => true, 'message' => 'Akun berhasil dihapus.'];
        } else {
            return ['status' => false, 'message' => 'Akun tidak ditemukan.'];
        }
        
    } catch (PDOException $e) {
        // PENTING: Jika user yang dihapus adalah Admin yang pernah input kendaraan
        if ($e->getCode() == '23000') { 
             return ['status' => false, 'message' => 'Gagal menghapus! Akun ini masih terikat sebagai penanggung jawab input data kendaraan.'];
        }
        return ['status' => false, 'message' => 'Gagal menghapus user: ' . $e->getMessage()];
    }
}