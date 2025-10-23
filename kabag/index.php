<?php
require_once '../auth_controller.php';
require_once '../controllerUserManagement.php'; // Mengelola CRUD user_backend (Admin/Petugas)
require_once '../controllerDashboard.php';      // Digunakan untuk menampilkan statistik

// 1. GANTI ROLE NAME: Gunakan 'Petugas' karena itu adalah nilai ENUM di database
startSessionByRole('Petugas'); 

// 2. PROTEKSI HALAMAN
// Lindungi halaman: Pastikan user sudah login dan perannya adalah 'Petugas'
checkLoginAndRole('Petugas', 'kabag'); 

// --- Ambil Data yang Dibutuhkan untuk Dashboard ---

// 1. Data Ringkasan Global (total survey, total kendaraan selesai/menunggu)
$summary = getSummaryStatistics();

// 2. Data Kinerja Per Admin (Admin adalah yang diukur kinerjanya, bukan Staf Pelayanan)
$admin_performance = getPerformanceByAdmin(); // Fungsi ini harus ada di dashboard_controller.php

// 3. Data Jumlah User (Menggunakan user_management_controller.php)

/** * Fungsi Helper untuk menghitung total user berdasarkan role
 * (Diletakkan di sini untuk sementara, idealnya di user_management_controller.php)
 */
function countTotalUsersByRole($role) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_backend WHERE role = ?");
    $stmt->execute([$role]);
    return $stmt->fetchColumn();
}

$total_admins = countTotalUsersByRole('Admin');
$total_kabag = countTotalUsersByRole('Petugas'); // Total Kabag/Petugas
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Supervisor/Kabag - UPT PKB</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #e9ecef;
        padding: 20px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 3px solid #17a2b8;
        padding-bottom: 10px;
        margin-bottom: 30px;
    }

    .stats-container {
        display: flex;
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        flex: 1;
        border-left: 5px solid #17a2b8;
    }

    .stat-card h4 {
        margin-top: 0;
        color: #555;
    }

    .stat-card p {
        font-size: 2.2em;
        font-weight: bold;
        color: #17a2b8;
        margin: 5px 0 0 0;
    }

    .nav-link {
        background-color: #17a2b8;
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

    .performance-section {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }
    </style>
</head>

<body>
    <div class="header">
        <h1>Dashboard Supervisor/Kabag 👑</h1>
        <p>Selamat datang, **<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>**
            (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
        <div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div style="margin-bottom: 20px;">
        <a href="manajemen_admin.php" class="nav-link">👥 Manajemen Akun Admin</a>
        <a href="manajemen_staf.php" class="nav-link">🧑‍🔧 Manajemen Staf Pelayanan</a>
        <a href="../admin/data_mentah.php" class="nav-link" style="background-color: #28a745;">📄 Data Mentah Survei</a>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <h4>Total Survei Selesai</h4>
            <p><?php echo $summary['total_surveys']; ?></p>
        </div>
        <div class="stat-card">
            <h4>Avg. Rating Global (Keseluruhan)</h4>
            <?php 
                $avg_global = ($summary['avg_pelayanan'] + $summary['avg_fasilitas'] + $summary['avg_kecepatan']) / 3;
            ?>
            <p><?php echo round($avg_global, 2); ?>/5</p>
        </div>
        <div class="stat-card">
            <h4>Total Akun Admin</h4>
            <p><?php echo $total_admins; ?></p>
        </div>
        <div class="stat-card">
            <h4>Total Staf Pelayanan Tercatat</h4>
            <p><?php echo $total_staf; ?></p>
        </div>
    </div>

    <div class="performance-section">
        <h2>Tabel Kinerja Staf Pelayanan (Berdasarkan Rating Pengguna)</h2>
        <p>Analisis ini menunjukkan rata-rata nilai survei yang terkait dengan setiap Staf Pelayanan.</p>

        <?php if (empty($staf_performance)): ?>
        <p style="color: red;">Belum ada data survei yang selesai untuk dianalisis.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Staf</th>
                    <th>Jml. Survey</th>
                    <th>Avg. Pelayanan</th>
                    <th>Avg. Fasilitas</th>
                    <th>Avg. Kecepatan</th>
                    <th>Rata-rata Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($staf_performance as $data): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td>**<?php echo htmlspecialchars($data['nama_staf']); ?>**</td>
                    <td><?php echo $data['total_survey_dikerjakan']; ?></td>
                    <td><?php echo $data['avg_rating_pelayanan']; ?>/5</td>
                    <td><?php echo $data['avg_rating_fasilitas']; ?>/5</td>
                    <td><?php echo $data['avg_rating_kecepatan']; ?>/5</td>
                    <td>**<?php echo $data['avg_rating_total']; ?>/5**</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</body>

</html>