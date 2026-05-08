<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 1 )) {
    header('Location: ../index.php');
    exit();
}

include '../includes/db.php';
include'header.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$perPage = 10; // Jumlah transaksi per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $perPage;
$searchParam = "%$search%";

// Hitung total transaksi
$countQuery = "SELECT COUNT(*) FROM transaksi t 
               JOIN pengguna p ON t.user_id = p.id 
               WHERE t.id LIKE ? 
               OR p.username LIKE ? 
               OR t.nomorHP LIKE ? 
               OR t.type LIKE ? 
               OR t.status LIKE ?";

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute([$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
$totalTransactions = $countStmt->fetchColumn();
$totalPages = ceil($totalTransactions / $perPage);

// Query utama dengan LIMIT
$query = "SELECT t.id, p.username, t.nomorHP, t.type, t.jumlah, t.status, t.reason, t.created_at 
          FROM transaksi t 
          JOIN pengguna p ON t.user_id = p.id 
          WHERE t.id LIKE ? 
          OR p.username LIKE ? 
          OR t.nomorHP LIKE ? 
          OR t.type LIKE ? 
          OR t.status LIKE ? 
          ORDER BY t.created_at DESC 
          LIMIT ?, ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, (int)$start, (int)$perPage]);
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lihat Transaksi</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
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
            margin-top: 20px;
            background: white;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: center;
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
        .pagination .current {
            padding: 8px 12px;
            background: #28a745;
            color: white;
            border-radius: 5px;
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
           .download-all-btn {
            display: inline-block;
            margin-bottom: 15px;
            padding: 10px 15px;
            background: darkblue;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .download-all-btn:hover {
            background: navy;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Daftar Transaksi</h1>
        <div class="search-box">
            <form method="GET">
                <input type="text" name="search" placeholder="Cari transaksi..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Cari</button>
        <a href="download_all_transaksi.php" class="download-all-btn">Download Semua Transaksi</a>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Nomor HP</th>
                    <th>Jenis Transaksi</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Alasan</th>
                    <th>Tanggal</th>
                    <th>aksi</th>
                </tr>
            </thead>
           <tbody>
    <?php if (empty($transactions)): ?>
        <tr>
            <td colspan="9">Transaksi tidak ditemukan.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($transactions as $transaction): ?>
            <tr>
                <td><?= htmlspecialchars($transaction['id']) ?></td>
                <td><?= htmlspecialchars($transaction['username']) ?></td>
                <td><?= htmlspecialchars($transaction['nomorHP']) ?></td>
                <td><?= ucfirst(htmlspecialchars($transaction['type'])) ?></td>
                <td>Rp.<?= number_format($transaction['jumlah'], 0, ',', '.') ?></td>
                <td style="color: <?= $transaction['status'] == 'success' ? 'green' : 'red'; ?>;">
                    <?= ucfirst(htmlspecialchars($transaction['status'])) ?>
                </td>
                <td><?= htmlspecialchars($transaction['reason']) ?></td>
                <td><?= date('d-m-Y H:i', strtotime($transaction['created_at'])) ?></td>
                <td>
<a href="detail_transaksi.php?id=<?= $transaction['id'] ?>" class="download-btn">
                   view
    </a>
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
