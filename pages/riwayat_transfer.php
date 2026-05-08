<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

include '../includes/db.php';
include'header.php';

$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Jumlah data per halaman
$offset = ($page - 1) * $limit;

// Query pencarian + pagination
$sql = '
    SELECT t.id, p.username AS penerima_nama, t.receiver_id, t.jumlah, t.created_at 
    FROM transaksi_pengguna t
    JOIN pengguna p ON t.receiver_id = p.id
    WHERE t.sender_id = ? 
';

$params = [$user_id];

if ($search) {
    $sql .= ' AND (p.username LIKE ? OR t.receiver_id LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= ' ORDER BY t.created_at DESC LIMIT ? OFFSET ?';
$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transfers = $stmt->fetchAll();

// Hitung total data
$countQuery = '
    SELECT COUNT(*) 
    FROM transaksi_pengguna t
    JOIN pengguna p ON t.receiver_id = p.id
    WHERE t.sender_id = ? 
';

$countParams = [$user_id];

if ($search) {
    $countQuery .= ' AND (p.username LIKE ? OR t.receiver_id LIKE ?)';
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
}

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($countParams);
$total_records = $countStmt->fetchColumn();
$total_pages = ceil($total_records / $limit);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Transfer</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 20px;
            width: 90%;
            max-width: 700px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            text-align: center;
            margin-top: 20px;
            flex-grow: 1;
        }
        h1 {
            color: #007BFF;
            margin-bottom: 20px;
        }
        .search-container {
            margin-bottom: 15px;
            display: flex;
            justify-content: center;
        }
        .search-input {
            padding: 8px;
            width: 70%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .search-btn {
            padding: 8px 15px;
            margin-left: 5px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-btn:hover {
            background: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }
        th {
            background: #007BFF;
            color: white;
        }
        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
        .pagination a {
            padding: 8px 12px;
            margin: 2px;
            text-decoration: none;
            background: #007BFF;
            color: white;
            border-radius: 5px;
        }
        .pagination a.active {
            background: #0056b3;
            font-weight: bold;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-decoration: none;
            padding: 8px 15px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .back-link:hover {
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
        <h1>Riwayat Transfer</h1>

        <!-- Form Pencarian -->
        <form method="GET" class="search-container">
            <input type="text" name="search" class="search-input" placeholder="Cari nama atau ID penerima..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="search-btn">Cari</button>
        </form>

        <?php if (empty($transfers)): ?>
            <p>Belum ada transaksi.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Tanggal</th>
                    <th>ID Penerima</th>
                    <th>Nama Penerima</th>
                    <th>Jumlah</th>
                    <th>Aksi</th>
                </tr>
                <?php foreach ($transfers as $transfer): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('d M Y H:i', strtotime($transfer['created_at']))) ?></td>
                        <td><?= htmlspecialchars($transfer['receiver_id']) ?></td>
                        <td><?= htmlspecialchars($transfer['penerima_nama']) ?></td>
                        <td>Rp.<?= number_format($transfer['jumlah'], 2) ?></td>
                        <td>
                            <a href="view_transfer.php?id=<?= $transfer['id'] ?>" class="download-btn">
                            view
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?search=<?= htmlspecialchars($search) ?>&page=<?= $page - 1 ?>">Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?search=<?= htmlspecialchars($search) ?>&page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?search=<?= htmlspecialchars($search) ?>&page=<?= $page + 1 ?>">Next</a>
            <?php endif; ?>
        </div>

        <a href="dashboard.php" class="back-link">Kembali ke Dashboard</a>
    </div>

    <div class="content"></div>
    <?php include 'footer.php'; ?>
</body>
</html>