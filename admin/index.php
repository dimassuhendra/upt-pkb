<?php
require_once '../auth_controller.php';
require_once '../controllerDashboard.php'; // Digunakan untuk menampilkan statistik
require_once '../controllerStafPelayanan.php'; // Untuk menampilkan daftar staf yang ada

// Lindungi halaman: Pastikan user sudah login dan perannya adalah 'Admin'
// Jika tidak, user akan di-redirect ke admin/login.php
checkLoginAndRole('Admin', 'admin'); 

// Ambil data yang dibutuhkan untuk dashboard
$summary = getSummaryStatistics();
$staf_list = readAllStafPelayanan(); // Untuk mengetahui jumlah staf yang diawasi
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - UPT PKB</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f9;
        padding: 20px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #ccc;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .stats-container {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        flex: 1;
    }

    .stat-card h4 {
        margin-top: 0;
        color: #555;
    }

    .stat-card p {
        font-size: 2em;
        font-weight: bold;
        color: #007bff;
        margin: 5px 0 0 0;
    }

    .nav-link {
        background-color: #28a745;
        color: white;
        padding: 10px 15px;
        text-decoration: none;
        border-radius: 5px;
        margin-right: 10px;
    }

    .logout-btn {
        background-color: #dc3545;
        color: white;
        padding: 10px 15px;
        text-decoration: none;
        border-radius: 5px;
    }

    .main-task {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    </style>
</head>

<body>
    <div class="header">
        <h1>Dashboard Admin 👋</h1>
        <p>Selamat datang, **<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>**
            (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
        <div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div style="margin-bottom: 20px;">
        <a href="input_kendaraan.php" class="nav-link">➕ Input Data Kendaraan Baru</a>
        <a href="data_mentah.php" class="nav-link">📄 Lihat Data Mentah Survei</a>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <h4>Total Survei Selesai</h4>
            <p><?php echo $summary['total_surveys']; ?></p>
        </div>
        <div class="stat-card">
            <h4>Avg. Rating Pelayanan (Global)</h4>
            <p><?php echo $summary['avg_pelayanan']; ?>/5</p>
        </div>
        <div class="stat-card">
            <h4>Avg. Rating Kecepatan (Global)</h4>
            <p><?php echo $summary['avg_kecepatan']; ?>/5</p>
        </div>
        <div class="stat-card">
            <h4>Jumlah Staf Pelayanan Terdaftar</h4>
            <p><?php echo count($staf_list); ?></p>
        </div>
    </div>

    <div class="main-task">
        <h2>Tugas Utama Admin</h2>
        <p>Sebagai Admin, fokus utama Anda adalah memastikan setiap kendaraan yang akan diuji **tercatat dengan benar**
            dan **dikaitkan dengan Staf Pelayanan** yang bertugas, sehingga data tersebut siap untuk disurvei oleh
            pengguna di Kiosk.</p>
        <p>Gunakan tombol **"Input Data Kendaraan Baru"** untuk memulai proses hari ini.</p>
    </div>

</body>

</html>

<?php
// File logout.php (di root folder) akan berisi pemanggilan fungsi logoutUser() dari auth_controller.php
// Pastikan Anda membuat file logout.php di root folder jika belum, dengan kode ini:
/*
<?php 
require_once 'auth_controller.php';
logoutUser(); 
?>
*/
?>