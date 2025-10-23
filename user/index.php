<?php
// user/index.php (REVISI TOTAL untuk FIFO Kiosk)
require_once '../controllerKendaraan.php';
require_once '../controllerSurvey.php';

$message = '';
$message_type = '';
$kendaraan_data = readKendaraanFIFO(); // Panggil data yang paling tua menunggu

// --- 1. PROSES PENGISIAN SURVEI (Jika form disubmit) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'submit_survey') {
    $id_kendaraan = $_POST['id_kendaraan'] ?? 0;
    $rating_pelayanan = $_POST['rating_pelayanan'] ?? 0;
    $rating_fasilitas = $_POST['rating_fasilitas'] ?? 0;
    $rating_kecepatan = $_POST['rating_kecepatan'] ?? 0;
    $komentar = $_POST['komentar'] ?? null;

    // Panggil fungsi submitSurvey()
    $result = submitSurvey($id_kendaraan, $rating_pelayanan, $rating_fasilitas, $rating_kecepatan, $komentar);
    
    $message = $result['message'];
    $message_type = $result['status'] ? 'success' : 'error';
    
    // Jika sukses, status kendaraan akan berubah dan form survei akan menampilkan kendaraan berikutnya secara otomatis
    
    // Ambil data kendaraan berikutnya untuk refresh tampilan
    $kendaraan_data = readKendaraanFIFO(); 
}

// Tambahkan pesan jika redirect sukses dari POST sebelumnya
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $message = "Terima kasih! Survei Anda telah berhasil dikirim.";
    $message_type = 'success';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survei Kiosk FIFO - UPT PKB</title>
    <style>
    /* (Style yang sama dari user/survey-form.php bisa digunakan di sini) */
    body {
        font-family: Arial, sans-serif;
        background-color: #f0f0f5;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
        padding: 40px 20px;
    }

    .container {
        max-width: 800px;
        width: 100%;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    h1,
    h2 {
        text-align: center;
        color: #1c73a8;
    }

    .message {
        padding: 15px;
        margin-bottom: 25px;
        border-radius: 8px;
        font-weight: bold;
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

    .data-kendaraan {
        background-color: #e9ecef;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .data-kendaraan p {
        margin: 5px 0;
        font-size: 1.1em;
    }

    .rating-group {
        margin-bottom: 30px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
    }

    .rating-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
        font-size: 1.1em;
    }

    .rating-input {
        display: flex;
        justify-content: space-around;
        margin-top: 10px;
    }

    .rating-input input[type="radio"] {
        display: none;
    }

    .rating-input label {
        font-size: 1.5em;
        padding: 10px;
        border: 2px solid #ccc;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: all 0.2s;
        color: #ccc;
    }

    .rating-input input[type="radio"]:checked+label {
        background-color: #ffc107;
        border-color: #ffc107;
        color: white;
        box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
        transform: scale(1.1);
    }

    .plat-readonly {
        font-size: 2em;
        font-weight: bold;
        color: #dc3545;
        background: #fff;
        padding: 5px;
        border: 1px solid #dc3545;
        text-align: center;
        margin: 10px 0;
    }

    textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        min-height: 100px;
        box-sizing: border-box;
    }

    .submit-btn {
        width: 100%;
        padding: 15px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1.2em;
        font-weight: bold;
    }

    .submit-btn:hover {
        background-color: #1e7e34;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>Survei Kepuasan Pelanggan UPT PKB</h1>
        <p style="text-align: center; color: #555;">Mohon berikan penilaian Anda terhadap pelayanan kami.</p>

        <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <?php if ($kendaraan_data): ?>
        <h2>PENILAIAN UNTUK KENDARAAN</h2>
        <div class="data-kendaraan">
            <p style="text-align: center;">**Plat Nomor Anda:**</p>
            <div class="plat-readonly"><?php echo htmlspecialchars($kendaraan_data['plat_nomor']); ?></div>
            <p><strong>Jenis:</strong> <?php echo htmlspecialchars($kendaraan_data['jenis_kendaraan']); ?></p>
            <p><strong>Admin Penginput:</strong> <?php echo htmlspecialchars($kendaraan_data['nama_admin']); ?></p>
        </div>

        <form action="index.php" method="POST">
            <input type="hidden" name="action" value="submit_survey">
            <input type="hidden" name="id_kendaraan"
                value="<?php echo htmlspecialchars($kendaraan_data['id_kendaraan']); ?>">

            <div class="rating-group">
                <label>1. Bagaimana kepuasan Anda terhadap **Pelayanan Admin**?</label>
                <div class="rating-input">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <input type="radio" id="pelayanan-<?php echo $i; ?>" name="rating_pelayanan"
                        value="<?php echo $i; ?>" required>
                    <label for="pelayanan-<?php echo $i; ?>"><?php echo $i; ?></label>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="rating-group">
                <label>2. Bagaimana kepuasan Anda terhadap **Fasilitas** di lokasi?</label>
                <div class="rating-input">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <input type="radio" id="fasilitas-<?php echo $i; ?>" name="rating_fasilitas"
                        value="<?php echo $i; ?>" required>
                    <label for="fasilitas-<?php echo $i; ?>"><?php echo $i; ?></label>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="rating-group">
                <label>3. Bagaimana kepuasan Anda terhadap **Kecepatan** proses pengujian?</label>
                <div class="rating-input">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <input type="radio" id="kecepatan-<?php echo $i; ?>" name="rating_kecepatan"
                        value="<?php echo $i; ?>" required>
                    <label for="kecepatan-<?php echo $i; ?>"><?php echo $i; ?></label>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="komentar">Komentar Tambahan (Opsional):</label>
                <textarea id="komentar" name="komentar" rows="4"
                    placeholder="Berikan masukan atau saran Anda..."></textarea>
            </div>

            <button type="submit" class="submit-btn">Kirim Survei</button>
        </form>
        <?php else: ?>
        <div style="text-align: center; margin-top: 50px;">
            <h2>✅ Tidak Ada Antrian Survei Aktif Saat Ini</h2>
            <p>Silakan menunggu sampai data kendaraan Anda selesai diinput oleh Admin.</p>
            <button onclick="window.location.reload();"
                style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px;">Segarkan
                Halaman</button>
        </div>
        <?php endif; ?>
    </div>
</body>

</html>