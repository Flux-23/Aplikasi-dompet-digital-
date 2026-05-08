<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

include '../includes/db.php';
$user_id = $_SESSION['user_id'];

// Ambil data pengguna
$stmt = $pdo->prepare('SELECT * FROM pengguna WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User tidak ditemukan.");
}

// Ambil saldo pengguna
$stmt = $pdo->prepare('SELECT credit FROM wallets WHERE user_id = ?');
$stmt->execute([$user_id]);
$wallet = $stmt->fetch();
$saldo = $wallet ? $wallet['credit'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jumlah = floatval($_POST['jumlah']);
    $nomorHP = $user['nomorHP'];

    if ($jumlah <= 0) {
        $_SESSION['error_message'] = "Jumlah withdraw harus lebih dari 0.";
    } elseif ($saldo < $jumlah) {
        $_SESSION['error_message'] = "Saldo tidak cukup untuk melakukan withdraw.";
    } else {
        try {
            $pdo->beginTransaction();

            // Kurangi saldo pengguna di wallets
            $stmt = $pdo->prepare('UPDATE wallets SET credit = credit - ? WHERE user_id = ?');
            $stmt->execute([$jumlah, $user_id]);

            // Simpan transaksi ke tabel `transaksi`
            $stmt = $pdo->prepare('
                INSERT INTO transaksi(user_id, nomorHP, type, jumlah, status, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([$user_id, $nomorHP, 'withdraw', $jumlah, 'approved']);

            $pdo->commit();

            // Set notifikasi sukses di session
            $_SESSION['success_message'] = "Withdraw berhasil!";

            // Redirect ke dashboard
            header('Location: dashboard.php');
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
    header('Location: withdraw.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Withdraw</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 20px;
            width: 350px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            text-align: center;
            position: relative;
        }
        h1 {
            margin-bottom: 20px;
            color: #007BFF;
        }
        .saldo-box {
            background: #007BFF;
            color: white;
            padding: 15px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .input-box {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn:hover {
            background: #218838;
        }
        .notif {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            max-width: 400px;
            color: white;
            font-weight: bold;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
        .notif.success {
            background-color: #28a745;
        }
        .notif.error {
            background-color: #dc3545;
        }
        .notif.show {
            opacity: 1;
        }
        .notif button {
            background: transparent;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Withdraw</h1>
        <div class="saldo-box">
            Saldo Anda: Rp <?= number_format($saldo, 2) ?>
        </div>

        <form method="POST">
            <input type="number" name="jumlah" class="input-box" placeholder="Masukkan jumlah" required min="1">
            <button type="submit" class="btn">Withdraw</button>
        </form>

        <br> <a href="dashboard.php" class="btn">Kembali ke Dashboard</a>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>