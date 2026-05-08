<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

include '../includes/db.php';
include 'header.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<p>Transaksi tidak ditemukan.</p>";
    exit();
}

$transaction_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Query untuk mengambil detail transaksi termasuk nomorHP
$sql = "
    SELECT t.id, p.username AS penerima_nama, t.receiver_id, t.nomorHP, t.jumlah, t.created_at
    FROM transaksi_pengguna t
    JOIN pengguna p ON t.receiver_id = p.id
    WHERE t.id = ? AND t.sender_id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$transaction_id, $user_id]);
$transfer = $stmt->fetch();

if (!$transfer) {
    echo "<p>Transaksi tidak ditemukan.</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Transaksi</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            text-align: center;
        }
        .container {
            background: white;
            padding: 20px;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            margin: 20px auto;
        }
        h1 {
            color: #007BFF;
        }
        p {
            font-size: 16px;
            margin: 10px 0;
        }
        .back-btn {
            display: block;
            margin-top: 20px;
            text-decoration: none;
            padding: 8px 15px;
            background: #007BFF;
            color: white;
            border-radius: 5px;
        }
        .back-btn:hover {
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
        <h1>Detail Transaksi</h1>
        <p><strong>ID Penerima:</strong> <?= htmlspecialchars($transfer['receiver_id']) ?></p>
        <p><strong>Nama Penerima:</strong> <?= htmlspecialchars($transfer['penerima_nama']) ?></p>
        <p><strong>No Penerima:</strong> <?= htmlspecialchars($transfer['nomorHP']) ?></p>
        <p><strong>Jumlah:</strong> Rp.<?= number_format($transfer['jumlah'], 2) ?></p>
        <p><strong>Tanggal:</strong> <?= htmlspecialchars(date('d M Y H:i', strtotime($transfer['created_at']))) ?></p>
        <a href="download_transfer.php?id=<?= $transfer['id'] ?>" class="download-btn">Download PDF</a>
        <a href="riwayat_transfer.php" class="back-btn">Kembali</a>
    </div>
</body>
</html>
