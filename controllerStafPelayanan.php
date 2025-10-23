<?php
// staf_pelayanan_controller.php
require_once 'db_connection.php';
require_once 'auth_controller.php';

/**
 * --------------------------------
 * CRUD STAF PELAYANAN 
 * --------------------------------
 */

/**
 * C (Create): Menambahkan data staf pelayanan baru.
 * @param string $nama_staf Nama staf
 * @param string $jabatan Jabatan staf
 * @return array Hasil status operasi
 */
function createStafPelayanan($nama_staf, $jabatan = null) {
    global $pdo;
    
    if (empty($nama_staf)) {
        return ['status' => false, 'message' => 'Nama staf tidak boleh kosong.'];
    }

    try {
        $sql = "INSERT INTO staf_pelayanan (nama_staf, jabatan) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nama_staf, $jabatan]);
        
        return ['status' => true, 'message' => 'Data staf pelayanan berhasil ditambahkan.'];
        
    } catch (PDOException $e) {
        return ['status' => false, 'message' => 'Gagal menambahkan staf: ' . $e->getMessage()];
    }
}

/**
 * R (Read All): Mengambil semua data staf pelayanan.
 * @return array Daftar semua staf atau array kosong
 */
function readAllStafPelayanan() {
    global $pdo;
    
    try {
        $sql = "SELECT id_staf, nama_staf, jabatan FROM staf_pelayanan ORDER BY nama_staf ASC";
        $stmt = $pdo->query($sql);
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error reading staf pelayanan: " . $e->getMessage());
        return [];
    }
}

/**
 * R (Read Single): Mengambil satu data staf berdasarkan ID.
 * @param int $id_staf ID staf yang dicari
 * @return array Data staf atau false jika tidak ditemukan
 */
function readSingleStafPelayanan($id_staf) {
    global $pdo;

    if (!is_numeric($id_staf)) return false;
    
    try {
        $sql = "SELECT id_staf, nama_staf, jabatan FROM staf_pelayanan WHERE id_staf = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_staf]);
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Error reading single staf: " . $e->getMessage());
        return false;
    }
}

/**
 * U (Update): Memperbarui data staf pelayanan.
 * @param int $id_staf ID staf
 * @param string $nama_staf Nama staf baru
 * @param string $jabatan Jabatan baru
 * @return array Hasil status operasi
 */
function updateStafPelayanan($id_staf, $nama_staf, $jabatan) {
    global $pdo;

    if (!is_numeric($id_staf) || empty($nama_staf)) {
        return ['status' => false, 'message' => 'ID atau Nama staf tidak valid.'];
    }

    try {
        $sql = "UPDATE staf_pelayanan SET nama_staf = ?, jabatan = ? WHERE id_staf = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nama_staf, $jabatan, $id_staf]);
        
        if ($stmt->rowCount() > 0) {
            return ['status' => true, 'message' => 'Data staf berhasil diperbarui.'];
        } else {
            return ['status' => true, 'message' => 'Tidak ada perubahan data atau ID staf tidak ditemukan.'];
        }
        
    } catch (PDOException $e) {
        return ['status' => false, 'message' => 'Gagal memperbarui staf: ' . $e->getMessage()];
    }
}

/**
 * D (Delete): Menghapus data staf pelayanan.
 * @param int $id_staf ID staf yang akan dihapus
 * @return array Hasil status operasi
 */
function deleteStafPelayanan($id_staf) {
    global $pdo;

    if (!is_numeric($id_staf)) {
        return ['status' => false, 'message' => 'ID staf tidak valid.'];
    }

    try {
        $sql = "DELETE FROM staf_pelayanan WHERE id_staf = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_staf]);
        
        if ($stmt->rowCount() > 0) {
            return ['status' => true, 'message' => 'Staf berhasil dihapus.'];
        } else {
            return ['status' => false, 'message' => 'Staf tidak ditemukan.'];
        }
        
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') { 
             return ['status' => false, 'message' => 'Gagal menghapus! Staf ini masih terikat dengan data kendaraan yang sudah diinput.'];
        }
        return ['status' => false, 'message' => 'Gagal menghapus staf: ' . $e->getMessage()];
    }
}