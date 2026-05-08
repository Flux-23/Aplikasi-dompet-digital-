<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

include '../includes/db.php';
include 'header.php';

$user_id = $_SESSION['user_id'];
$transaction_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$transaction_id) {
    die("ID transaksi tidak valid.");
}

// Ambil detail transaksi dari kedua tabel
$stmt = $pdo->prepare("
    SELECT t.id, t.type, t.nomorHP, t.jumlah, t.created_at, t.status, 
           u.username AS pengguna,
           sender.username AS pengirim, receiver.username AS penerima,
           sender.nomorHP AS pengirim_nomorHP, receiver.nomorHP AS penerima_nomorHP,
           t.sender_id, t.receiver_id 
    FROM (
        -- Transaksi Top-Up dan Withdraw
        SELECT id, type, nomorHP, jumlah, created_at, status, user_id, NULL AS sender_id, NULL AS receiver_id 
        FROM transaksi 
        WHERE user_id = ? AND id = ?

        UNION ALL 

        -- Transaksi Transfer
        SELECT id, 'transfer' AS type, nomorHP, jumlah, created_at, status, NULL AS user_id, sender_id, receiver_id 
        FROM transaksi_pengguna 
        WHERE (sender_id = ? OR receiver_id = ?) AND id = ?
    ) t
    LEFT JOIN pengguna u ON t.user_id = u.id
    LEFT JOIN pengguna sender ON t.sender_id = sender.id
    LEFT JOIN pengguna receiver ON t.receiver_id = receiver.id
");
$stmt->execute([$user_id, $transaction_id, $user_id, $user_id, $transaction_id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    die("Transaksi tidak ditemukan atau tidak memiliki akses.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Transaksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 20px;
            width: 400px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            text-align: center;
        }
        h2 {
            color: #007BFF;
        }
        .detail {
            text-align: left;
            margin-top: 20px;
        }
        .detail p {
            margin: 5px 0;
        }
        .status {
            font-weight: bold;
            padding: 5px;
            border-radius: 5px;
        }
        .status.sukses { background: #28a745; color: white; }
        .status.gagal { background: #dc3545; color: white; }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px;
            background: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .download-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 15px;
            background: red;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .download-btn:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="detail">
            <h2>Detail Transaksi</h2>

            <?php if ($transaction['type'] == 'topup' || $transaction['type'] == 'withdraw'): ?>
                <p><strong>ID Transaksi:</strong> <?= htmlspecialchars($transaction['id']) ?></p>
                <p><strong>Username:</strong> <?= htmlspecialchars($transaction['pengguna']) ?></p>
                <p><strong>Nomor HP:</strong> <?= htmlspecialchars($transaction['nomorHP']) ?></p>
                <p><strong>Jenis:</strong> <?= ucfirst(htmlspecialchars($transaction['type'])) ?></p>
                <p><strong>Status:</strong> 
                    <span class="status <?= strtolower($transaction['status']) ?>">
                        <?= ucfirst(htmlspecialchars($transaction['status'])) ?>
                    </span>
                     <p><strong>Jumlah:</strong> Rp <?= number_format($transaction['jumlah'], 2) ?></p>
            
                </p><p><strong>Tanggal:</strong> <?= htmlspecialchars($transaction['created_at']) ?></p>

            <?php endif; ?>

            <?php if ($transaction['type'] == 'transfer'): ?>
                 <p><strong>ID Transaksi:</strong> <?= htmlspecialchars($transaction['id']) ?></p>
                 <p><strong>Jenis:</strong> <?= ucfirst(htmlspecialchars($transaction['type'])) ?></p>
                <p><strong>Pengirim:</strong> <?= htmlspecialchars($transaction['pengirim']) ?></p>
                <p><strong>No HP Pengirim:</strong> <?= htmlspecialchars($transaction['pengirim_nomorHP']) ?></p>
                <p><strong>Penerima:</strong> <?= htmlspecialchars($transaction['penerima']) ?></p>
                <p><strong>No HP Penerima:</strong> <?= htmlspecialchars($transaction['penerima_nomorHP']) ?></p>
                <p><strong>Status:</strong> 
                    <span class="status <?= strtolower($transaction['status']) ?>">
                        <?= ucfirst(htmlspecialchars($transaction['status'])) ?>
                    </span>
                    <p><strong>Jumlah:</strong> Rp <?= number_format($transaction['jumlah'], 2) ?></p>
                </p><p><strong>Tanggal:</strong> <?= htmlspecialchars($transaction['created_at']) ?></p>
            <?php endif; ?>
        </div>

        <a href="report.php" class="btn">Kembali</a>
        <a href="download_transaksi.php?id=<?= $transaction['id'] ?>" class="download-btn">Download PDF</a>
    </div>
</body>
</html>
