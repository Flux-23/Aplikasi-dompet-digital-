<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) { // Pastikan hanya admin yang bisa akses
    header('Location: ../index.php');
    exit();
}

include '../includes/db.php';
include'header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $nomorHP = trim($_POST['nomorHP']);
    $password = $_POST['password'];
    
    // Validasi input
    if (empty($username) || empty($nomorHP) || empty($password)) {
        $error = "Semua field harus diisi!";
    } elseif (!preg_match('/^[0-9]{10,13}$/', $nomorHP)) {
        $error = "Nomor HP harus berupa angka dengan 10-13 digit!";
    } else {
        // Cek apakah username atau nomorHP sudah terdaftar
        $stmt = $pdo->prepare('SELECT id FROM pengguna WHERE username = ? OR nomorHP = ?');
        $stmt->execute([$username, $nomorHP]);
        $user = $stmt->fetch();

        if ($user) {
            $error = "Username atau Nomor HP sudah terdaftar!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Simpan pengguna baru ke database (role_id = 3 untuk User)
            $stmt = $pdo->prepare('INSERT INTO pengguna (username, nomorHP, password, role_id) VALUES (?, ?, ?, 3)');
            $stmt->execute([$username, $nomorHP, $hashed_password]);

            // Redirect ke halaman yang sama dengan pesan sukses
            header('Location: add_user.php?success=1');
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
    <title>Tambah Akun Pengguna</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 90%;
            max-width: 400px;
        }
        h1 {
            color: #007BFF;
            font-size: 22px;
            margin-bottom: 20px;
        }
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-back {
            margin-top: 10px;
            display: inline-block;
            text-decoration: none;
            color: #007BFF;
            font-size: 14px;
        }
        .btn-back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tambah Akun Pengguna</h1>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">Akun berhasil ditambahkan!</div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="text" name="nomorHP" placeholder="Nomor HP" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Tambah Akun</button>
        </form>

        <a href="kelola_akun.php" class="btn-back">Kembali ke kelola akun</a>
    </div>
    <div class="content">
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>