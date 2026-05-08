<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

include '../includes/db.php';
include'header.php';
$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Buat token CSRF jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Cek role user dengan JOIN ke tabel role
$stmt = $pdo->prepare('
    SELECT r.name FROM pengguna p
    JOIN role r ON p.role_id = r.id
    WHERE p.id = ?
');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || !in_array($user['name'], ['admin', 'bankmini'])) {
    die("Akses ditolak! Anda tidak memiliki izin untuk mengisi saldo.");
}

// Proses saat form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $target_nomorHP = filter_input(INPUT_POST, 'target_nomorHP', FILTER_SANITIZE_STRING);
    $jumlah = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT);

    if (!$target_nomorHP || !$jumlah || $jumlah < 10000) {
        $error = "Nomor HP tidak valid atau jumlah minimal Rp 10.000.";
    } else {
        // Cek apakah user tujuan ada berdasarkan nomor HP
        $stmt = $pdo->prepare('SELECT id FROM pengguna WHERE nomorHP = ?');
        $stmt->execute([$target_nomorHP]);
        $target_user = $stmt->fetch();

        if (!$target_user) {
            $error = "User dengan nomor HP tersebut tidak ditemukan.";
        } else {
            $target_user_id = $target_user['id'];

            // Cek apakah user memiliki wallets
            $stmt = $pdo->prepare('SELECT id FROM wallets WHERE user_id = ?');
            $stmt->execute([$target_user_id]);
            $wallet = $stmt->fetch();

            if (!$wallet) {
                $error = "User tidak memiliki wallet.";
            } else {
                // Gunakan transaksi untuk memastikan data tetap konsisten
                $pdo->beginTransaction();
                try {
                    // Tambahkan saldo ke wallets user
                    $stmt = $pdo->prepare('UPDATE wallets SET credit = credit + ? WHERE user_id = ?');
                    $stmt->execute([$jumlah, $target_user_id]);

                    // Catat transaksi
                    $stmt = $pdo->prepare('INSERT INTO transaksi (user_id, nomorHP, type, jumlah, status) 
                                        VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$target_user_id, $target_nomorHP, 'topup', $jumlah, 'approved']);

                    $pdo->commit();
                    $success = "Saldo sebesar Rp " . number_format($jumlah, 0, ',', '.') . " berhasil ditambahkan ke Nomor HP: " . htmlspecialchars($target_nomorHP);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = "Terjadi kesalahan: " . $e->getMessage();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Isi Saldo User | E-Wallet</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            border-radius: 10px;
            text-align: center;
        }
        .error {
            color: red;
            font-size: 14px;
        }
        .success {
            color: green;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Isi Saldo User</h1>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="text" name="target_nomorHP" placeholder="Masukkan Nomor HP" required>
            <input type="number" name="jumlah" placeholder="Masukkan jumlah (min: Rp 10.000)" required>
            <button type="submit" class="btn">Tambah Saldo</button>
        </form>
        
        <br><a href="dashboard.php" class="btn">Kembali</a>
    </div>
    <div class="content">
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
