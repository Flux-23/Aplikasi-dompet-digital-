<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

include '../includes/db.php';
$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Buat token CSRF untuk keamanan
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $jumlah = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT);

    if ($jumlah === false || $jumlah < 10000) {
        $error = "Minimal top-up adalah Rp 10.000 dan harus berupa angka valid.";
    } else {
        // Ambil nomor HP user
        $stmt = $pdo->prepare('SELECT nomorHP FROM pengguna WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "User tidak ditemukan.";
        } else {
            // Simpan transaksi ke database dengan status 'waiting_approval'
            $stmt = $pdo->prepare('INSERT INTO transaksi (user_id, nomorHP, type, jumlah, status) 
                                   VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$user_id, $user['nomorHP'], 'topup', $jumlah, 'waiting_approval']);

            $transaction_id = $pdo->lastInsertId();
            header("Location: payment.php?transaction_id=$transaction_id");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Top-Up | E-Wallet</title>
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
        <h1>Top-Up Saldo</h1>
        
        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="number" name="jumlah" placeholder="Masukkan jumlah (min: Rp 10.000)" required>
            <button type="submit" class="btn">Lanjutkan Pembayaran</button>
        </form>
        <br><a href="dashboard.php" class="btn">back</a>
    </div>
    <div class="content">
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
