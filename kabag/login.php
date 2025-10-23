<?php
require_once '../auth_controller.php';

// PENTING: Panggil fungsi sesi unik untuk Kabag/Petugas
startSessionByRole('Petugas'); 

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Panggil fungsi login untuk Petugas/Kabag
    $result = loginUserBackend($username, $password, 'Petugas'); 

    if (!$result['status']) {
        // Jika gagal, tampilkan pesan error
        $error_message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas/Supervisor - UPT PKB</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background-color: #f0f8ff;
    }

    .login-container {
        background: white;
        padding: 20px 30px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        width: 350px;
        border-top: 5px solid #17a2b8;
    }

    h2 {
        text-align: center;
        color: #17a2b8;
    }

    h3 {
        text-align: center;
        color: #555;
        font-size: 1.1em;
        margin-bottom: 25px;
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
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-sizing: border-box;
    }

    button {
        width: 100%;
        padding: 12px;
        background-color: #17a2b8;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
    }

    button:hover {
        background-color: #138496;
    }

    .error {
        color: #dc3545;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        padding: 10px;
        border-radius: 4px;
        text-align: center;
        margin-bottom: 15px;
    }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>LOGIN SISTEM</h2>
        <h3>Petugas / Supervisor (Kabag)</h3>

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