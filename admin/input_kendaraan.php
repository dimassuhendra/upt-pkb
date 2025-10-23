<?php
// admin/input_kendaraan.php (REVISI TOTAL)

require_once '../auth_controller.php';
require_once '../controllerKendaraan.php'; 
startSessionByRole('Admin');// Jika tidak, user akan di-redirect ke admin/login.php
// Lindungi halaman: Pastikan user sudah login dan perannya adalah 'Admin'
checkLoginAndRole('Admin', 'admin'); 

$message = '';
$message_type = ''; // 'success' atau 'error'

// AMBIL DATA AKUN ADMIN YANG SEDANG AKTIF DARI SESSION
$current_admin_id = $_SESSION['user_id'];
$current_admin_name = $_SESSION['nama_lengkap'];

// 1. Proses Form Input Kendaraan jika ada POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $plat_nomor = $_POST['plat_nomor'] ?? '';
    $jenis_kendaraan = $_POST['jenis_kendaraan'] ?? '';
    
    // Panggil fungsi createKendaraan() dengan ID Admin yang sedang login
    $result = createKendaraan($plat_nomor, $jenis_kendaraan, $current_admin_id);
    
    $message = $result['message'];
    $message_type = $result['status'] ? 'success' : 'error';
    
    if ($result['status']) {
        $_POST = array(); // Mengosongkan variabel POST
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Kendaraan - Admin UPT PKB</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f9;
        padding: 20px;
    }

    .container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
        color: #333;
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #555;
    }

    input[type="text"],
    select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 16px;
    }

    button {
        background-color: #007bff;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
    }

    button:hover {
        background-color: #0056b3;
    }

    .message {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 4px;
        text-align: center;
    }

    .success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .read-only-field {
        background-color: #e9ecef;
        color: #495057;
        border: 1px dashed #ced4da;
        padding: 10px;
        border-radius: 4px;
        font-weight: bold;
    }

    .back-link {
        display: block;
        margin-top: 20px;
        text-align: center;
        text-decoration: none;
        color: #007bff;
    }
    </style>
</head>

<body>
    <div class="container">
        <h2>Input Data Kendaraan Uji</h2>
        <p>Data yang diinput akan dicatat sebagai tanggung jawab akun Anda.</p>

        <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <form action="input_kendaraan.php" method="POST">

            <div class="form-group">
                <label>Admin yang Bertugas (Otomatis)</label>
                <div class="read-only-field">
                    <?php echo htmlspecialchars($current_admin_name); ?>
                </div>
                <input type="hidden" name="id_user" value="<?php echo $current_admin_id; ?>">
            </div>

            <div class="form-group">
                <label for="plat_nomor">Plat Nomor Kendaraan</label>
                <input type="text" id="plat_nomor" name="plat_nomor"
                    value="<?php echo htmlspecialchars($_POST['plat_nomor'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="jenis_kendaraan">Jenis Kendaraan</label>
                <input type="text" id="jenis_kendaraan" name="jenis_kendaraan"
                    value="<?php echo htmlspecialchars($_POST['jenis_kendaraan'] ?? ''); ?>" required>
            </div>

            <button type="submit">Catat dan Siapkan Survei</button>
        </form>

        <a href="index.php" class="back-link">← Kembali ke Dashboard</a>
    </div>
</body>

</html>