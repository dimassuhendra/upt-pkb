<?php
// admin/login.php

// PENTING: Hubungkan ke controller yang ada di root folder (tingkat atas)
require_once '../auth_controller.php';

$error_message = '';

// 1. Proses Form Login jika ada POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Panggil fungsi login dengan peran yang spesifik: 'Admin'
    $result = loginUserBackend($username, $password, 'Admin');
    
    if ($result['status'] === true) {
        // Login berhasil, arahkan ke dashboard Admin di folder yang sama (index.php)
        header('Location: index.php'); 
        exit();
    } else {
        // Login gagal, tampilkan pesan error
        $error_message = $result['message'];
    }
}

// 2. Jika Admin sudah login saat mengakses halaman login.php, arahkan ke dashboard
// Ini mencegah admin yang sudah login kembali ke halaman login
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true && $_SESSION['role'] === 'Admin') {
    header('Location: index.php'); 
    exit();
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