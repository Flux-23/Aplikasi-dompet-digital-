<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header('Location: ../index.php');
    exit();
}

include '../includes/db.php';
include'header.php';
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transaction_id = filter_input(INPUT_POST, 'transaction_id', FILTER_VALIDATE_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : null;

    if (!$transaction_id || ($action == 'reject' && empty($reason))) {
        $error = "Permintaan tidak valid. Harap isi alasan penolakan.";
    } else {
        $stmt = $pdo->prepare('SELECT user_id, jumlah, status FROM transaksi WHERE id = ?');
        $stmt->execute([$transaction_id]);
        $transaction = $stmt->fetch();

        if (!$transaction) {
            $error = "Transaksi tidak ditemukan.";
        } elseif ($transaction['status'] != 'waiting_approval') {
            $error = "Transaksi sudah diproses sebelumnya.";
        } else {
            try {
                $pdo->beginTransaction();
                if ($action == 'approve') {
                    $stmt = $pdo->prepare('UPDATE transaksi SET status = "approved", reason = NULL WHERE id = ?');
                    $stmt->execute([$transaction_id]);

                    $stmt = $pdo->prepare('INSERT INTO wallets (user_id, credit) 
                                           VALUES (?, ?) 
                                           ON DUPLICATE KEY UPDATE credit = credit + VALUES(credit)');
                    $stmt->execute([$transaction['user_id'], $transaction['jumlah']]);

                    $success = "Top-up berhasil disetujui.";
                } elseif ($action == 'reject') {
                    $stmt = $pdo->prepare('UPDATE transaksi SET status = "rejected", reason = ? WHERE id = ?');
                    $stmt->execute([$reason, $transaction_id]);
                    $success = "Top-up ditolak dengan alasan: $reason";
                }
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
    }
}

$stmt = $pdo->prepare('SELECT t.id, p.username, t.nomorHP, t.jumlah, t.status, t.reason 
                       FROM transaksi t 
                       JOIN pengguna p ON t.user_id = p.id 
                       WHERE t.type = "topup" 
                       ORDER BY t.id DESC');

$stmt->execute();
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Persetujuan Top-up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #007BFF;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        .status-approved {
            color: green;
            font-weight: bold;
        }
        .status-rejected {
            color: red;
            font-weight: bold;
        }
        .btn {
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            color: white;
            font-size: 14px;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .btn-approve {
            background-color: #28a745;
        }
        .btn-approve:hover {
            background-color: #218838;
        }
        .btn-reject {
            background-color: #dc3545;
        }
        .btn-reject:hover {
            background-color: #c82333;
        }
        .btn-dashboard {
            background-color: #28a745;
            padding: 10px 20px;
            display: block;
            text-align: center;
            margin: 20px auto 0;
            max-width: 200px;
        }
        .btn-dashboard:hover {
            background-color: #218838;
        }
        .no-data {
            text-align: center;
            color: red;
            font-size: 18px;
            padding: 20px;
        }
        .reason-box {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-top: 5px;
        }
    </style>
    <script>
        function showRejectForm(id) {
            document.getElementById("reject-form-" + id).style.display = "block";
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>💰 Persetujuan Top-up</h1>

        <?php if (!empty($success)): ?>
            <p class="success" style="color: green; text-align: center;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <p class="error" style="color: red; text-align: center;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (empty($transactions)): ?>
            <p class="no-data">Tidak ada transaksi.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Nomor HP</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Alasan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction['id']) ?></td>
                            <td><?= htmlspecialchars($transaction['username']) ?></td>
                            <td><?= htmlspecialchars($transaction['nomorHP']) ?></td>
                            <td>Rp.<?= number_format($transaction['jumlah'], 2) ?></td>
                            <td class="<?= $transaction['status'] == 'rejected' ? 'status-rejected' : 'status-approved' ?>">
                                <?= ucfirst($transaction['status']) ?>
                            </td>
                            <td><?= htmlspecialchars($transaction['reason'] ?? '-') ?></td>
                            <td>
                                <?php if ($transaction['status'] == 'waiting_approval'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="transaction_id" value="<?= $transaction['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-approve">✔ Approve</button>
                                    </form>
                                    <button onclick="showRejectForm(<?= $transaction['id'] ?>)" class="btn btn-reject">✖ Reject</button>
                                    <form id="reject-form-<?= $transaction['id'] ?>" method="POST" style="display:none;">
                                        <input type="hidden" name="transaction_id" value="<?= $transaction['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <textarea name="reason" required class="reason-box" placeholder="Alasan..."></textarea>
                                        <button type="submit" class="btn btn-reject">Submit</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="dashboard.php" class="btn btn-dashboard">Kembali ke Dashboard</a>
    </div>
    <div class="content">
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
