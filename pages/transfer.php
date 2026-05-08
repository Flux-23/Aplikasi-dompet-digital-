<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

include '../includes/db.php';
include 'header.php';
$user_id = $_SESSION['user_id'];

// Ambil saldo pengguna
$stmt = $pdo->prepare('SELECT credit FROM wallets WHERE user_id = ?');
$stmt->execute([$user_id]);
$wallet = $stmt->fetch();
$saldo = $wallet ? $wallet['credit'] : 0;

$message = "";
$recipient_name = "";
$nomorHP = "";
$jumlah = "";
$type = "transfer"; // Default type transaksi adalah "transfer"

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['check_user'])) {
        $nomorHP = $_POST['nomorHP'];
        
        // Cek apakah penerima ada di database
        $stmt = $pdo->prepare('SELECT id, username FROM pengguna WHERE nomorHP = ?');
        $stmt->execute([$nomorHP]);
        $recipient = $stmt->fetch();

        if ($recipient) {
            $recipient_name = $recipient['username'];
        } else {
            $message = "Nomor HP penerima tidak ditemukan.";
        }
    } elseif (isset($_POST['transfer'])) {
        $nomorHP = $_POST['nomorHP'];
        $jumlah = $_POST['jumlah'];
        $recipient_name = $_POST['recipient_name'];

        // Cek ulang penerima
        $stmt = $pdo->prepare('SELECT id FROM pengguna WHERE nomorHP = ?');
        $stmt->execute([$nomorHP]);
        $recipient = $stmt->fetch();

        if (!$recipient) {
            $message = "Nomor HP penerima tidak ditemukan.";
        } elseif ($jumlah <= 0) {
            $message = "Jumlah transfer harus lebih dari 0.";
        } elseif ($saldo < $jumlah) {
            $message = "Saldo tidak cukup untuk melakukan transfer.";
        } else {
            $recipient_id = $recipient['id'];

            // Mulai transaksi database
            $pdo->beginTransaction();
            try {
                // Kurangi saldo pengirim
                $stmt = $pdo->prepare('UPDATE wallets SET credit = credit - ? WHERE user_id = ?');
                $stmt->execute([$jumlah, $user_id]);

                // Tambah saldo penerima
                $stmt = $pdo->prepare('UPDATE wallets SET credit = credit + ? WHERE user_id = ?');
                $stmt->execute([$jumlah, $recipient_id]);

                // Simpan transaksi ke tabel transaksi_pengguna
                $stmt = $pdo->prepare('INSERT INTO transaksi_pengguna (sender_id, receiver_id, type, nomorHP, jumlah, status) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$user_id, $recipient_id, $type, $nomorHP, $jumlah, 'sukses']);

                // Commit transaksi jika semua sukses
                $pdo->commit();

                $_SESSION['success_message'] = "Transfer Rp. " . number_format($jumlah, 2) . " ke {$recipient_name} berhasil!";
header('Location: dashboard.php');
exit();

            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transfer</title>
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
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .back-link {
            display: block;
            margin-top: 10px;
            text-decoration: none;
            color: #007BFF;
            font-size: 14px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .recipient-box {
            background: #ffc107;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-weight: bold;
        }
    </style>
    <script>
        function confirmTransfer() {
            return confirm("Apakah Anda yakin ingin mentransfer sejumlah Rp. " + document.getElementById("jumlah").value + " ke " + document.getElementById("recipient_name").value + "?");
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Transfer</h1>
        <div class="saldo-box">
            Saldo Anda: Rp.<?= number_format($saldo, 2) ?>
        </div>
        
        <?php if ($message): ?>
            <p class="error"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if (!$recipient_name): ?>
            <form method="POST">
                <input type="text" name="nomorHP" class="input-box" placeholder="Nomor HP Penerima" required>
                <button type="submit" name="check_user" class="btn">Cek Penerima</button>
            </form>
        <?php else: ?>
            <form method="POST">
                <div class="recipient-box">
                    Transfer ke: <?= htmlspecialchars($recipient_name) ?>
                </div>
                <input type="hidden" name="type" value="transfer">
                <input type="hidden" name="nomorHP" value="<?= htmlspecialchars($nomorHP) ?>">
                <input type="hidden" name="recipient_name" value="<?= htmlspecialchars($recipient_name) ?>">
                <input type="number" name="jumlah" class="input-box" placeholder="Jumlah Transfer" required>
                <button type="submit" name="transfer" class="btn">Konfirmasi Transfer</button>
            </form>
        <?php endif; ?>

        <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
