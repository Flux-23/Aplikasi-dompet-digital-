<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

include '../includes/db.php';
require('../phpqrcode/qrlib.php'); // Pastikan phpqrcode sudah diinstal

$user_id = $_SESSION['user_id'];
$transaction_id = filter_input(INPUT_GET, 'transaction_id', FILTER_VALIDATE_INT);

if (!$transaction_id) {
    die("ID transaksi tidak valid.");
}

// Ambil transaksi top-up berdasarkan ID dan pastikan hanya milik user yang login
$stmt = $pdo->prepare('SELECT * FROM transaksi WHERE id = ? AND user_id = ? AND type = "topup"');
$stmt->execute([$transaction_id, $user_id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    die("Transaksi top-up tidak ditemukan atau tidak memiliki akses.");
}

// Cek apakah QR Code sudah dipindai
if ($transaction['status_scan'] == 1) {
    die("Pembayaran telah diproses. Tidak dapat melakukan transaksi ulang.");
}

// **Data untuk QR Code**
$bank_tujuan = "BNI";
$rekening_tujuan = "3211223";
$nama_penerima = "PT.chiness";
$jumlah = number_format($transaction['jumlah'], 2, '.', '');
$qr_data = "Bank: $bank_tujuan\nRek: $rekening_tujuan\nNama: $nama_penerima\nJumlah: Rp $jumlah";

// **Buat QR Code dalam format base64 tanpa menyimpan file**
ob_start();
QRcode::png($qr_data, null, QR_ECLEVEL_L, 5);
$qr_image = ob_get_contents();
ob_end_clean();

$qr_base64 = base64_encode($qr_image);

// Update status_scan setelah QR dipindai
if (isset($_POST['confirm_scan'])) {
    $updateStmt = $pdo->prepare("UPDATE transaksi SET status_scan = 1 WHERE id = ?");
    $updateStmt->execute([$transaction_id]);
    header("Location: pembayaran_sukses.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Top-Up</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            border-radius: 10px;
            text-align: center;
        }
        .qr-code {
            margin: 20px 0;
        }
        .btn-secondary, .btn-primary {
            text-decoration: none;
            display: inline-block;
            padding: 10px;
            width: 100%;
            color: white;
            text-align: center;
            border-radius: 5px;
            margin-top: 10px;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .btn-primary {
            background-color: #28a745;
        }
        .btn-primary:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pembayaran Top-Up</h1>

        <p><strong>ID Transaksi:</strong> <?= htmlspecialchars($transaction['id']) ?></p>
        <p><strong>Jumlah:</strong> Rp <?= number_format($transaction['jumlah'], 2) ?></p>
        <p><strong>Bank Tujuan:</strong> <?= $bank_tujuan ?></p>
        <p><strong>No. Rekening:</strong> <?= $rekening_tujuan ?></p>
        <p><strong>Nama:</strong> <?= $nama_penerima ?></p>

        <p>Silakan scan QR Code di bawah untuk melakukan pembayaran top-up.</p>

        <div class="qr-code">
            <?php if ($qr_base64): ?>
                <img src="data:image/png;base64,<?= $qr_base64 ?>" alt="QR Code Pembayaran">
            <?php else: ?>
                <p style="color: red;">Gagal menampilkan QR Code</p>
            <?php endif; ?>
        </div>

        <form method="post">
            <button type="submit" name="confirm_scan" class="btn-primary">Konfirmasi Scan</button>
        </form>

        <a href="dashboard.php" class="btn-secondary">Kembali ke Dashboard</a>
    </div>
</body>
</html>
