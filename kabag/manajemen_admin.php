<?php
// kabag/manajemen_admin.php (CRUD Akun Admin)

require_once '../auth_controller.php';
require_once '../controllerUserManagement.php'; // Akan digunakan untuk menghitung total Admin

// Lindungi halaman: Pastikan user sudah login dan perannya adalah 'Petugas' (Supervisor/Kabag)
checkLoginAndRole('Petugas', 'kabag'); 

$message = '';
$message_type = '';
$action = $_GET['action'] ?? 'read';
$edit_data = null;

// --- 1. PROSES CRUD (CREATE, UPDATE, DELETE) ---

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_action = $_POST['action'] ?? '';
    
    // Proses CREATE
    if ($post_action === 'create') {
        $result = createUserBackend(
            $_POST['username'],
            $_POST['password'],
            $_POST['nama_lengkap'],
            'Admin' // Default role saat membuat akun di sini adalah Admin
        );
        $message = $result['message'];
        $message_type = $result['status'] ? 'success' : 'error';
    } 
    
    // Proses UPDATE
    elseif ($post_action === 'update' && isset($_POST['id_user'])) {
        $result = updateUserBackend(
            $_POST['id_user'],
            $_POST['username'],
            $_POST['nama_lengkap'],
            'Admin', // Pastikan role tetap Admin
            $_POST['password'] // Boleh kosong/null jika tidak ingin ganti password
        );
        $message = $result['message'];
        $message_type = $result['status'] ? 'success' : 'error';
        
        // Setelah update, kembali ke mode 'read'
        if ($result['status']) {
             $action = 'read';
        } else {
             $action = 'edit';
        }
    }
}

// Proses DELETE
if ($action === 'delete' && isset($_GET['id'])) {
    $result = deleteUserBackend($_GET['id']);
    $message = $result['message'];
    $message_type = $result['status'] ? 'success' : 'error';
    $action = 'read';
}

// Proses READ SINGLE (Untuk mengisi form edit)
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_data = readSingleUserBackend($_GET['id']);
    if (!$edit_data) {
        $message = "Data Admin tidak ditemukan.";
        $message_type = 'error';
        $action = 'read';
    }
}

// --- 2. AMBIL DATA UNTUK DITAMPILKAN (READ ALL) ---
$all_users = readAllUserBackend();
// Filter hanya untuk role 'Admin' (Karena supervisor hanya mengelola Admin)
$admin_list = array_filter($all_users, function($user) {
    return $user['role'] === 'Admin';
});

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Akun Admin - Supervisor</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #e9ecef;
        padding: 20px;
    }

    .container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h2 {
        border-bottom: 2px solid #ccc;
        padding-bottom: 10px;
    }

    .form-crud {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 6px;
        margin-bottom: 30px;
        border: 1px solid #dee2e6;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    button {
        padding: 10px 15px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background-color: #0056b3;
    }

    .message {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 4px;
        text-align: center;
        font-weight: bold;
    }

    .success {
        background-color: #d4edda;
        color: #155724;
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    .back-link {
        margin-top: 20px;
        display: block;
    }
    </style>
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-link">← Kembali ke Dashboard Supervisor</a>

        <h2>Manajemen Akun Admin</h2>
        <p>Halaman ini digunakan untuk menambah, mengubah, dan menghapus akun yang bertugas menginput data kendaraan.
        </p>

        <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="form-crud">
            <h3><?php echo ($action === 'edit' ? 'Edit Akun Admin' : 'Tambah Akun Admin Baru'); ?></h3>
            <form action="manajemen_admin.php" method="POST">

                <input type="hidden" name="action" value="<?php echo ($action === 'edit' ? 'update' : 'create'); ?>">

                <?php if ($action === 'edit'): ?>
                <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($edit_data['id_user']); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required
                        value="<?php echo htmlspecialchars($edit_data['nama_lengkap'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required
                        value="<?php echo htmlspecialchars($edit_data['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label
                        for="password"><?php echo ($action === 'edit' ? 'Password (Kosongkan jika tidak diubah)' : 'Password (Wajib diisi)'); ?></label>
                    <input type="password" id="password" name="password"
                        <?php echo ($action === 'create' ? 'required' : ''); ?>>
                </div>

                <button type="submit"><?php echo ($action === 'edit' ? 'Perbarui Akun' : 'Tambah Akun'); ?></button>

                <?php if ($action === 'edit'): ?>
                <a href="manajemen_admin.php" style="margin-left: 10px; color: #dc3545;">Batalkan</a>
                <?php endif; ?>
            </form>
        </div>

        <h3>Daftar Akun Admin (Penginput Data)</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($admin_list)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Belum ada akun Admin yang terdaftar.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($admin_list as $admin): ?>
                <tr>
                    <td><?php echo $admin['id_user']; ?></td>
                    <td><?php echo htmlspecialchars($admin['nama_lengkap']); ?></td>
                    <td><?php echo htmlspecialchars($admin['username']); ?></td>
                    <td>
                        <a href="manajemen_admin.php?action=edit&id=<?php echo $admin['id_user']; ?>">Edit</a> |
                        <a href="manajemen_admin.php?action=delete&id=<?php echo $admin['id_user']; ?>"
                            onclick="return confirm('Yakin ingin menghapus akun <?php echo htmlspecialchars($admin['username']); ?>? Ini dapat menyebabkan masalah jika akun ini sudah input data.');"
                            style="color: red;">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</body>

</html>