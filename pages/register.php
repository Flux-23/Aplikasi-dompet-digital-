<?php
session_start();
include '../includes/db.php';
include'header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $nomorHP = trim($_POST['nomorHP']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($nomorHP) || empty($password) || empty($confirm_password)) {
        $error = "Semua field harus diisi!";
    } elseif (!preg_match('/^[0-9]{10,13}$/', $nomorHP)) {
        $error = "Nomor HP harus terdiri dari 10-13 digit angka!";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan Konfirmasi Password tidak cocok!";
    } else {
        $stmt = $pdo->prepare('SELECT id FROM pengguna WHERE username = ? OR nomorHP = ?');
        $stmt->execute([$username, $nomorHP]);
        $user = $stmt->fetch();

        if ($user) {
            $error = "Username atau Nomor HP sudah terdaftar!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO pengguna (username, nomorHP, password, role_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $nomorHP, $hashed_password, 3]);
            $user_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare('INSERT INTO wallets (user_id, credit) VALUES (?, ?)');
            $stmt->execute([$user_id, 0]);
            header('Location: login.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center; /* Tetap di tengah horizontal */
            align-items: center; /* Di tengah vertikal */
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 100%;
            max-width: 400px;
            position: relative;
            top: 20px; /* Naikkan sedikit agar tidak terlalu ke bawah */
        }
        h1 {
            color: #007BFF;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background: #007BFF;
            color: white;
            border: none;
            padding: 10px;
            font-size: 18px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .login-link {
            margin-top: 10px;
            font-size: 14px;
        }
        .login-link a {
            color: #007BFF;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="text" name="nomorHP" placeholder="Nomor HP" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <p class="login-link">Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
    <div class="content"></div>
    <?php include 'footer.php'; ?>
</body>
</html>
