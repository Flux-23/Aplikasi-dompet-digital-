<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Ambil session notifikasi jika ada
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
unset($_SESSION['success_message']); // Hapus session setelah ditampilkan

$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
unset($_SESSION['error_message']); // Hapus session setelah ditampilkan

include '../includes/db.php';
include 'header.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Ambil informasi user
$stmt = $pdo->prepare('SELECT * FROM pengguna WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Jika user biasa, ambil saldo mereka sendiri
if ($role == 3) {
    $stmt = $pdo->prepare('SELECT credit FROM wallets WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $wallet = $stmt->fetch();
    $saldo = $wallet ? $wallet['credit'] : 0;
}

// Jika admin, ambil saldo semua user dengan pencarian dan paginasi
if ($role == 1) {
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $start = ($page > 1) ? ($page * $limit) - $limit : 0;

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $whereClause = $search ? "WHERE p.username LIKE :search1 OR p.nomorHP LIKE :search2" : "";

    $query = "SELECT p.username, p.nomorHP, w.credit FROM wallets w JOIN pengguna p ON w.user_id = p.id $whereClause LIMIT :start, :limit";
    $stmt = $pdo->prepare($query);
    if ($search) {
        $stmt->bindValue(':search1', "%$search%", PDO::PARAM_STR);
        $stmt->bindValue(':search2', "%$search%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $users_saldo = $stmt->fetchAll();

    $totalQuery = "SELECT COUNT(*) as total FROM wallets w JOIN pengguna p ON w.user_id = p.id $whereClause";
    $stmt = $pdo->prepare($totalQuery);
    if ($search) {
        $stmt->bindValue(':search1', "%$search%", PDO::PARAM_STR);
        $stmt->bindValue(':search2', "%$search%", PDO::PARAM_STR);
    }
    $stmt->execute();
    $totalRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $totalRow['total'];
    $pages = ceil($total / $limit);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            max-width: 1000px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
            text-align: center;
            font-size: 18px;
        }
        .saldo-box {
            background: #007BFF;
            color: white;
            padding: 20px;
            margin: 20px 0;
            font-size: 22px;
            font-weight: bold;
            border-radius: 10px;
        }
        nav ul {
            list-style: none;
            padding: 0;
        }
        nav ul li {
            display: inline;
            margin: 0 15px;
        }
        nav ul li a {
            text-decoration: none;
            color: #007BFF;
            font-weight: bold;
            font-size: 18px;
        }
        .action-buttons {
            margin: 20px 0;
        }
        .action-buttons a {
            display: inline-block;
            margin: 10px;
            padding: 15px 20px;
            background: #007BFF;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            transition: background 0.3s;
        }
        .action-buttons a:hover {
            background: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            font-size: 18px;
        }
        th, td {
            padding: 15px;
            text-align: center;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            margin: 0 5px;
            padding: 10px 15px;
            text-decoration: none;
            background: #007BFF;
            color: white;
            border-radius: 5px;
            font-size: 18px;
        }
        .pagination a.active {
            background: #0056b3;
        }
        .search-container {
            margin-bottom: 20px;
        }
        .search-container input[type="text"] {
            padding: 12px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .search-container button {
            padding: 12px 18px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .search-container button:hover {
            background: #0056b3;
        }
         .notif {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #28a745;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            font-size: 16px;
            font-weight: bold;
            z-index: 9999;
            display: none;
        }
        .notif.error {
            background: #dc3545;
        }
    </style>
</head>
<body>
    <?php if ($success_message): ?>
    <div class="notif" id="notif"><?= htmlspecialchars($success_message) ?></div>
    <script>
        document.getElementById("notif").style.display = "block";
        setTimeout(() => {
            document.getElementById("notif").style.display = "none";
        }, 3000); // Notifikasi hilang setelah 3 detik
    </script>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="notif error" id="notif"><?= htmlspecialchars($error_message) ?></div>
    <script>
        document.getElementById("notif").style.display = "block";
        setTimeout(() => {
            document.getElementById("notif").style.display = "none";
        }, 3000);
    </script>
<?php endif; ?>
    <div class="container">
        <h1>Selamat Datang <?= htmlspecialchars($user['username']) ?></h1>
  <?php if ($role == 3): ?>
            <div class="saldo-box">
                Saldo Anda: Rp.<?= number_format($saldo, 0, ',', '.'); ?>
            </div>
        <?php endif; ?>

        <nav>
  <ul>
    <?php if ($role == 2): ?>
      <li><a href="approve_topup.php"><i class="fa fa-check-circle"></i> Approve Topup</a></li>
    <?php endif; ?>
    <?php if ($role == 1): ?>
      <li><a href="view_transactions.php"><i class="fa fa-history"></i> Lihat Transaksi User</a></li>
    <?php endif; ?>
    <?php if ($role == 1): ?>
      <li><a href="kelola_akun.php"><i class="fa fa-cog"></i> Kelola Akun</a></li>
    <?php endif; ?>
    <?php if ($role == 2): ?>
      <li><a href="saldo_user.php"><i class="fa fa-credit-card"></i> Isi Saldo User</a></li>
    <?php endif; ?>
    <?php if ($role == 3): ?>
      <li><a href="riwayat_transfer.php"><i class="fa fa-history"></i> Riwayat Transfer</a></li>
      <li><a href="topup.php"><i class="fa fa-plus-circle"></i> Topup</a></li>
      <li><a href="withdraw.php"><i class="fa fa-minus-circle"></i> Withdraw</a></li>
      <li><a href="transfer.php"><i class="fa fa-exchange"></i> Transfer</a></li>
      <li><a href="report.php"><i class="fa fa-file-text"></i> Report</a></li>
    <?php endif; ?>
    <li><a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
  </ul>
</nav>

        <?php if ($role == 1): ?>
            <h2>Saldo Semua User</h2>
            <div class="search-container">
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Cari username atau nomor HP" value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">Cari</button>
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Nomor HP</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users_saldo as $user_saldo): ?>
                        <tr>
                            <td><?= htmlspecialchars($user_saldo['username']) ?></td>
                            <td><?= htmlspecialchars($user_saldo['nomorHP']) ?></td>
                            <td>Rp.<?= number_format($user_saldo['credit'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="pagination">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
