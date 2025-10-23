<?php
require_once '../auth_controller.php';

// PENTING: Panggil fungsi sesi unik untuk Admin
startSessionByRole('Admin'); 

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Panggil fungsi login yang akan melakukan autentikasi dan redirect jika sukses
    // Catatan: Fungsi loginUserBackend AKAN OTOMATIS REDIRECT jika sukses.
    $result = loginUserBackend($username, $password, 'Admin'); 

    if (!$result['status']) {
        // Jika gagal (result['status'] == false), tampilkan pesan error
        $error_message = $result['message'];
    }
    // Jika sukses, kode tidak akan mencapai baris ini karena sudah di-redirect di dalam fungsi.
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - UPT PKB</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background-color: #f4f4f9;
    }

    .login-container {
        background: white;
        padding: 20px 30px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 300px;
    }

    h2 {
        text-align: center;
        color: #333;
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
        width: 100%;
        padding: 10px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }

    button:hover {
        background-color: #0056b3;
    }

    .error {
        color: red;
        text-align: center;
        margin-bottom: 15px;
    }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Login Admin</h2>
        <h3>Input Data Kendaraan</h3>

        <?php if ($error_message): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">LOGIN</button>
        </form>
    </div>
</body>

</html>