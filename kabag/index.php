<?php
require_once '../auth_controller.php';
require_once '../controllerUserManagement.php';
require_once '../controllerDashboard.php';

// 1. GANTI ROLE NAME: Gunakan 'Petugas'
startSessionByRole('Petugas'); 

// 2. PROTEKSI HALAMAN
checkLoginAndRole('Petugas', 'kabag'); 

// --- Ambil Data yang Dibutuhkan untuk Dashboard ---

// 1. Data Ringkasan Global
$summary = getSummaryStatistics();

// 2. Data Kinerja Admin (untuk Grafik)
$daily_performance = getAdminPerformanceByPeriod('daily');
$weekly_performance = getAdminPerformanceByPeriod('weekly');
$monthly_performance = getAdminPerformanceByPeriod('monthly');

// 3. Data Kinerja Staf Pelayanan (SEKARANG: Data Kinerja Admin - untuk Tabel)
$staf_performance = getPerformanceByAdmin();

// 4. Data Jumlah User
function countTotalUsersByRole($role) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_backend WHERE role = ?");
    $stmt->execute([$role]);
    return $stmt->fetchColumn();
}

$total_admins = countTotalUsersByRole('Admin');
$total_kabag = countTotalUsersByRole('Petugas'); 

// Perbaikan Error: Menghapus Query ke Tabel yang Hilang (staf_pelayanan)
// Karena tabel staf_pelayanan tidak ada di skema DB Anda, kita ganti nilainya.
$total_staf = 'N/A'; // Ditetapkan sebagai N/A untuk menghindari error query.


// --- Fungsi Helper untuk menyiapkan data untuk JavaScript ---
function prepareChartData($performance_data) {
    $labels = [];
    $data = [];
    foreach ($performance_data as $item) {
        $labels[] = htmlspecialchars($item['admin_name']);
        $data[] = (int)$item['total_surveys'];
    }
    return ['labels' => $labels, 'data' => $data];
}

$chart_data_daily = json_encode(prepareChartData($daily_performance));
$chart_data_weekly = json_encode(prepareChartData($weekly_performance));
$chart_data_monthly = json_encode(prepareChartData($monthly_performance));

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Supervisor/Kabag - UPT PKB</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;700&family=Roboto:wght@300;400;500&display=swap"
        rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

    <style>
    :root {
        /* Warna Palet */
        --color-primary: #007bff;
        /* Biru Profesional */
        --color-secondary: #28a745;
        /* Hijau Sukses */
        --color-danger: #dc3545;
        --color-background: #f8f9fa;
        /* Latar Belakang Sangat Terang */
        --color-text-main: #343a40;
        /* Teks Hitam Gelap */
        --color-card-bg: #ffffff;
        --color-border-light: #e9ecef;
        --font-serif: 'Roboto Slab', serif;
        --font-sans: 'Roboto', sans-serif;
    }

    body {
        font-family: var(--font-sans);
        background-color: var(--color-background);
        padding: 0;
        margin: 0;
        color: var(--color-text-main);
    }

    .container {
        max-width: 1300px;
        margin: 40px auto;
        padding: 0 20px;
    }

    /* HEADER & NAVIGATION */
    .header-bar {
        background-color: var(--color-card-bg);
        border-bottom: 5px solid var(--color-primary);
        padding: 20px 40px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-title h1 {
        font-family: var(--font-serif);
        font-weight: 700;
        color: var(--color-primary);
        margin: 0;
        font-size: 1.8rem;
    }

    .user-info {
        text-align: right;
    }

    .user-info strong {
        display: block;
        font-weight: 500;
        color: var(--color-text-main);
    }

    .user-info small {
        color: #6c757d;
    }

    .logout-btn {
        background-color: var(--color-danger);
        color: white;
        padding: 8px 15px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: 500;
        transition: background-color 0.3s ease;
        margin-left: 15px;
        display: inline-block;
    }

    .logout-btn:hover {
        background-color: #c82333;
    }

    .nav-links {
        margin: 30px 0;
        padding: 0 20px;
        border-bottom: 1px solid var(--color-border-light);
        padding-bottom: 20px;
    }

    .nav-link {
        background-color: var(--color-primary);
        color: white;
        padding: 10px 18px;
        text-decoration: none;
        border-radius: 5px;
        margin-right: 10px;
        display: inline-block;
        font-family: var(--font-serif);
        font-weight: 400;
        transition: background-color 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .nav-link:hover {
        background-color: #0056b3;
    }

    .nav-link-special {
        background-color: var(--color-secondary) !important;
    }

    .nav-link-special:hover {
        background-color: #1e7e34 !important;
    }

    /* STATISTIC CARDS */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: var(--color-card-bg);
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-left: 5px solid var(--color-primary);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .stat-card h4 {
        font-family: var(--font-serif);
        font-weight: 400;
        color: #6c757d;
        margin-top: 0;
        font-size: 1.1rem;
    }

    .stat-card p {
        font-size: 2.8em;
        font-family: var(--font-serif);
        font-weight: 700;
        color: var(--color-primary);
        margin: 5px 0 0 0;
        line-height: 1;
    }

    .stat-card p.alert-error {
        color: var(--color-danger) !important;
    }

    /* PERFORMANCE SECTION (CHART & TABLE) */
    .section-title {
        font-family: var(--font-serif);
        font-weight: 700;
        color: var(--color-text-main);
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--color-border-light);
        margin-bottom: 15px;
        font-size: 1.5rem;
    }

    .performance-section {
        background: var(--color-card-bg);
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 40px;
    }

    .chart-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 30px;
        margin-top: 20px;
    }

    .chart-box {
        background: var(--color-background);
        padding: 20px;
        border-radius: 6px;
        box-shadow: inset 0 0 8px rgba(0, 0, 0, 0.05);
    }

    .chart-box h4 {
        font-family: var(--font-serif);
        font-weight: 700;
        font-size: 1.2rem;
        color: var(--color-primary);
        margin-top: 0;
        border-bottom: 1px solid var(--color-border-light);
        padding-bottom: 10px;
        margin-bottom: 15px;
    }

    /* TABLE STYLES */
    table {
        width: 100%;
        border-collapse: separate;
        /* Menggunakan separate untuk radius */
        border-spacing: 0;
        margin-top: 20px;
        font-size: 0.95rem;
    }

    th,
    td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid var(--color-border-light);
    }

    thead th {
        background-color: var(--color-primary);
        color: white;
        font-weight: 500;
        font-family: var(--font-serif);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    /* Radius untuk sudut tabel */
    thead tr:first-child th:first-child {
        border-top-left-radius: 6px;
    }

    thead tr:first-child th:last-child {
        border-top-right-radius: 6px;
    }

    tbody tr:last-child td {
        border-bottom: none;
    }

    tbody tr:nth-child(even) {
        background-color: #f3f3f3;
    }

    tbody tr:hover {
        background-color: #e2f0ff;
        cursor: default;
    }

    .avg-total-col {
        font-weight: 700;
        color: var(--color-secondary);
    }

    /* RESPONSIVENESS */
    @media (max-width: 768px) {
        .header-bar {
            flex-direction: column;
            padding: 20px;
        }

        .user-info {
            margin-top: 15px;
            text-align: center;
        }

        .header-title {
            text-align: center;
        }

        .logout-btn {
            margin-top: 10px;
            margin-left: 0;
        }

        .container {
            margin: 20px auto;
            padding: 0 10px;
        }

        .nav-links {
            text-align: center;
            margin-bottom: 20px;
        }

        .nav-link {
            margin-bottom: 10px;
        }

        .stats-container {
            grid-template-columns: 1fr;
        }

        .chart-container {
            grid-template-columns: 1fr;
        }

        .performance-section {
            padding: 20px;
        }

        table,
        thead,
        tbody,
        th,
        td,
        tr {
            display: block;
        }

        thead tr {
            position: absolute;
            top: -9999px;
            left: -9999px;
        }

        td {
            border: none;
            border-bottom: 1px solid var(--color-border-light);
            position: relative;
            padding-left: 50%;
            text-align: right;
        }

        td:before {
            position: absolute;
            top: 0;
            left: 6px;
            width: 45%;
            padding-right: 10px;
            white-space: nowrap;
            font-weight: 500;
            content: attr(data-label);
            text-align: left;
            padding-top: 15px;
            padding-bottom: 15px;
        }

        /* Menyesuaikan label pada mode responsif */
        td:nth-of-type(1):before {
            content: "No.";
        }

        td:nth-of-type(2):before {
            content: "Nama Admin";
        }

        td:nth-of-type(3):before {
            content: "Jml. Survey";
        }

        td:nth-of-type(4):before {
            content: "Avg. Pelayanan";
        }

        td:nth-of-type(5):before {
            content: "Avg. Fasilitas";
        }

        td:nth-of-type(6):before {
            content: "Avg. Kecepatan";
        }

        td:nth-of-type(7):before {
            content: "Rata-rata Total";
        }
    }
    </style>
</head>

<body>
    <div class="header-bar">
        <div class="header-title">
            <h1>Dashboard Supervisor/Kabag 📊</h1>
        </div>
        <div class="user-info">
            <strong>Halo, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>
            <small>(<?php echo htmlspecialchars($_SESSION['role']); ?>)</small>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="nav-links">
            <a href="manajemen_admin.php" class="nav-link">👥 Manajemen Akun Admin</a>
            <a href="manajemen_staf.php" class="nav-link">🧑‍🔧 Manajemen Staf Pelayanan</a>
            <a href="../admin/data_mentah.php" class="nav-link nav-link-special">📄 Data Mentah Survei</a>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <h4>Total Survei Selesai (Keseluruhan)</h4>
                <p><?php echo $summary['total_surveys'] ?? '0'; ?></p>
            </div>
            <div class="stat-card">
                <h4>Avg. Rating Global (Semua Aspek)</h4>
                <?php 
                    // Pastikan $summary ada nilainya sebelum dihitung
                    $avg_global = 0;
                    if ($summary && $summary['avg_pelayanan'] !== null) {
                        $avg_global = ($summary['avg_pelayanan'] + $summary['avg_fasilitas'] + $summary['avg_kecepatan']) / 3;
                    }
                ?>
                <p><?php echo round($avg_global, 2); ?>/5</p>
            </div>
            <div class="stat-card">
                <h4>Total Akun Admin</h4>
                <p><?php echo $total_admins; ?></p>
            </div>
            <div class="stat-card">
                <h4>Total Staf Pelayanan Tercatat</h4>
                <p class="alert-error"><?php echo $total_staf; ?></p>
            </div>
        </div>

        <div class="performance-section">
            <h2 class="section-title">Visualisasi Kinerja Admin: Jumlah Survei Dikerjakan</h2>
            <p style="color: #6c757d;">Grafik batang ini menunjukkan performa masing-masing Admin dalam menyelesaikan
                survei berdasarkan periode waktu yang berbeda.</p>

            <div class="chart-container">

                <div class="chart-box">
                    <h4>Kinerja Harian (Hari Ini)</h4>
                    <canvas id="dailyChart"></canvas>
                </div>

                <div class="chart-box">
                    <h4>Kinerja Mingguan (7 Hari Terakhir)</h4>
                    <canvas id="weeklyChart"></canvas>
                </div>

                <div class="chart-box">
                    <h4>Kinerja Bulanan (Bulan Ini)</h4>
                    <canvas id="monthlyChart"></canvas>
                </div>

            </div>
        </div>

        <div class="performance-section">
            <h2 class="section-title">Tabel Kinerja Admin: Rata-rata Rating Survei</h2>
            <p style="color: #6c757d;">Analisis ini menampilkan rata-rata nilai survei yang diinput berdasarkan Admin
                yang bertanggung jawab.</p>

            <?php if (empty($staf_performance)): ?>
            <p
                style="color: var(--color-danger); text-align: center; padding: 20px; border: 1px solid var(--color-danger); border-radius: 5px;">
                Belum ada data Admin atau survei yang selesai untuk dianalisis. 😔
            </p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Admin</th>
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
                        <td data-label="No."><?php echo $no++; ?></td>
                        <td data-label="Nama Admin"><strong><?php echo htmlspecialchars($data['nama_staf']); ?></strong>
                        </td>
                        <td data-label="Jml. Survey"><?php echo $data['total_survey_dikerjakan']; ?></td>
                        <td data-label="Avg. Pelayanan"><?php echo round($data['avg_rating_pelayanan'], 2); ?>/5</td>
                        <td data-label="Avg. Fasilitas"><?php echo round($data['avg_rating_fasilitas'], 2); ?>/5</td>
                        <td data-label="Avg. Kecepatan"><?php echo round($data['avg_rating_kecepatan'], 2); ?>/5</td>
                        <td data-label="Rata-rata Total" class="avg-total-col">
                            <?php echo round($data['avg_rating_total'], 2); ?>/5
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>


    <script>
    // Data PHP yang sudah diubah menjadi JSON
    const dataDaily = <?php echo $chart_data_daily; ?>;
    const dataWeekly = <?php echo $chart_data_weekly; ?>;
    const dataMonthly = <?php echo $chart_data_monthly; ?>;

    // Warna untuk grafik
    const colors = {
        daily: 'rgba(0, 123, 255, 0.8)', // Biru Primary
        weekly: 'rgba(40, 167, 69, 0.8)', // Hijau Secondary
        monthly: 'rgba(255, 193, 7, 0.8)' // Kuning/Emas
    };

    /**
     * Fungsi untuk membuat dan merender grafik batang (Bar Chart)
     */
    function renderBarChart(canvasId, chartData, title, color) {
        const ctx = document.getElementById(canvasId);

        // Cek apakah data tidak kosong
        if (chartData.labels.length === 0) {
            // Tampilkan pesan di canvas jika data kosong
            if (ctx) {
                const parent = ctx.parentElement;
                parent.innerHTML =
                    '<p style="text-align:center; color:var(--color-danger); margin-top: 20px;">Data belum tersedia untuk periode ini.</p>';
            }
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Total Survei',
                    data: chartData.data,
                    backgroundColor: color,
                    borderColor: color.replace('0.8', '1'), // Warna border lebih gelap
                    borderWidth: 1,
                    borderRadius: 4, // Efek modern pada batang
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Penting untuk layout responsif
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Survei',
                            font: {
                                family: 'Roboto Slab',
                                size: 14
                            }
                        },
                        ticks: {
                            // Memastikan sumbu Y hanya menampilkan bilangan bulat
                            callback: function(value) {
                                if (value % 1 === 0) {
                                    return value;
                                }
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Nama Admin',
                            font: {
                                family: 'Roboto Slab',
                                size: 14
                            }
                        },
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45,
                            font: {
                                family: 'Roboto',
                                size: 12
                            }
                        },
                        grid: {
                            display: false // Menghilangkan grid vertikal
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false // Judul sudah ada di h4 chart-box
                    },
                    tooltip: {
                        titleFont: {
                            family: 'Roboto Slab'
                        },
                        bodyFont: {
                            family: 'Roboto'
                        }
                    }
                }
            }
        });
    }

    // Render 3 grafik
    document.addEventListener('DOMContentLoaded', () => {
        // Atur tinggi canvas agar responsif
        const chartBoxes = document.querySelectorAll('.chart-box');
        chartBoxes.forEach(box => {
            const canvas = box.querySelector('canvas');
            if (canvas) {
                canvas.style.height = '300px'; // Tinggi tetap untuk visualisasi yang baik
            }
        });

        renderBarChart('dailyChart', dataDaily, 'Survei Harian', colors.daily);
        renderBarChart('weeklyChart', dataWeekly, 'Survei Mingguan', colors.weekly);
        renderBarChart('monthlyChart', dataMonthly, 'Survei Bulanan', colors.monthly);
    });
    </script>
</body>

</html>