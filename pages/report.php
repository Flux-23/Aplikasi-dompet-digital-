<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

include '../includes/db.php';
include 'header.php';

$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination Setup
$limit = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;
$searchParam = "%$search%";

// Hitung total transaksi berdasarkan pencarian
$totalStmt = $pdo->prepare("
    SELECT COUNT(*) FROM (
        SELECT id FROM transaksi WHERE user_id = ? 
        AND (type LIKE ? OR jumlah LIKE ? OR id LIKE ?) 
        
        UNION ALL 
        
        SELECT t.id FROM transaksi_pengguna t
        JOIN pengguna sender ON t.sender_id = sender.id
        JOIN pengguna receiver ON t.receiver_id = receiver.id
        WHERE (t.sender_id = ? OR t.receiver_id = ?) 
        AND (t.id LIKE ? OR t.jumlah LIKE ? OR sender.username LIKE ? OR receiver.username LIKE ?)
    ) as total
");
$totalStmt->execute([$user_id, $searchParam, $searchParam, $searchParam, $user_id, $user_id, $searchParam, $searchParam, $searchParam, $searchParam]);
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Ambil transaksi sesuai pencarian dan pagination
$stmt = $pdo->prepare("
    SELECT t.id, t.type, t.jumlah, t.created_at, 
           CASE 
               WHEN t.sender_id = ? THEN receiver.username
               ELSE sender.username
           END AS nama_lawan,
           t.sender_id, t.receiver_id 
    FROM (
        -- Transaksi Withdraw dan Top-Up
        SELECT id, type, jumlah, created_at, NULL AS sender_id, NULL AS receiver_id 
        FROM transaksi 
        WHERE user_id = ? AND (type LIKE ? OR jumlah LIKE ? OR id LIKE ?) 

        UNION ALL 

        -- Transaksi Transfer (Penerima dan Pengirim)
        SELECT id, 'transfer' AS type, jumlah, created_at, sender_id, receiver_id 
        FROM transaksi_pengguna 
        WHERE (sender_id = ? OR receiver_id = ?) 
        AND (id LIKE ? OR jumlah LIKE ?)
    ) t
    LEFT JOIN pengguna sender ON t.sender_id = sender.id
    LEFT JOIN pengguna receiver ON t.receiver_id = receiver.id
    ORDER BY t.created_at DESC
    LIMIT ? OFFSET ?
");

$stmt->execute([
    $user_id,  // Untuk menentukan pengguna
    $user_id, $searchParam, $searchParam, $searchParam, // Untuk transaksi biasa
    $user_id, $user_id, $searchParam, $searchParam, // Untuk transaksi pengguna
    $limit, $offset
]);

$transactions = $stmt->fetchAll();

?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Transaksi</title>
    <style>
         body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 20px;
            width: 100%;
            max-width: 900px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            text-align: center;
        }
        h1 {
            color: #007BFF;
        }
        .search-box {
            margin-bottom: 20px;
        }
        .search-box input {
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .search-box button {
            padding: 8px 12px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-box button:hover {
            background: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: white;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background: #f2f2f2;
        }
        tr:hover {
            background: #d6e4ff;
            transition: 0.3s;
        }
        .pagination {
            margin-top: 20px;
        }
        .pagination a {
            padding: 8px 12px;
            margin: 2px;
            background: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .pagination a:hover {
            background: #0056b3;
        }
        .pagination .disabled {
            background: #ccc;
            pointer-events: none;
        }
        .pagination .current {
            padding: 8px 12px;
            background: #28a745;
            color: white;
            border-radius: 5px;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .back-btn:hover {
            background: #218838;
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
        <h1>Riwayat Transaksi</h1>

        <div class="search-box">
            <form method="GET">
                <input type="text" name="search" placeholder="Cari transaksi..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Cari</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>aksi</th>
                </tr>
            </thead>
           <tbody>
    <?php if (empty($transactions)): ?>
        <tr><td colspan="4">Tidak ada riwayat transaksi.</td></tr>
    <?php else: ?>
        <?php foreach ($transactions as $transaction): ?>
            <tr>
                <td><?= ucfirst(htmlspecialchars($transaction['type'])) ?></td>
                <td>Rp <?= number_format($transaction['jumlah'], 2) ?></td>
                <td><?= htmlspecialchars($transaction['created_at']) ?></td>
                <td>
                    <?php if ($transaction['type'] == 'withdraw'): ?>
                        🔴 Tarik Tunai
                    <?php elseif ($transaction['type'] == 'topup'): ?>
                        🟢 Top-Up Saldo
                    <?php elseif ($transaction['type'] == 'transfer'): ?>
                        <?php if ($transaction['sender_id'] == $user_id): ?>
                            🔄 Kirim ke <strong><?= htmlspecialchars($transaction['nama_lawan']) ?></strong>
                        <?php else: ?>
                            ✅ Terima dari <strong><?= htmlspecialchars($transaction['nama_lawan']) ?></strong>
                        <?php endif; ?>
                    <?php endif; ?>
                     <td><a href="view_report.php?id=<?= $transaction['id'] ?>" class="download-btn">
                   view
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>

        </table>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">&#171; Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>" class="<?= ($i == $page) ? 'current' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next &#187;</a>
            <?php endif; ?>
        </div>

        <a href="dashboard.php" class="back-btn">Kembali ke Dashboard</a>
    </div>
    <div class="content">
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
