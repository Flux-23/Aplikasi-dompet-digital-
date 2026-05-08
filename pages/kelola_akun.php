<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: ../index.php');
    exit();
}

include '../includes/db.php';
include'header.php';

// Jumlah data per halaman
$limit = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Filter pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Query untuk mengambil data akun
$query = 'SELECT id, username, nomorHP, role_id FROM pengguna WHERE role_id != 1';
$params = [];

if (!empty($search)) {
    $query .= ' AND (username LIKE ? OR nomorHP LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " LIMIT $start, $limit";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Menghitung total data untuk pagination
$countQuery = 'SELECT COUNT(*) FROM pengguna WHERE role_id != 1';
if (!empty($search)) {
    $countQuery .= ' AND (username LIKE ? OR nomorHP LIKE ?)';
}
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalUsers = $countStmt->fetchColumn();
$totalPages = ceil($totalUsers / $limit);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $stmt = $pdo->prepare('DELETE FROM pengguna WHERE id = ?');
    $stmt->execute([$user_id]);
    header('Location: kelola_akun.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun Pengguna</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 90%;
            max-width: 700px;
        }
        h1 {
            color: #007BFF;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .btn {
            padding: 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-add {
            background: #28a745;
            color: white;
        }
        .btn-add:hover {
            background: #218838;
        }
        .btn-back {
            background: #007BFF;
            color: white;
        }
        .btn-back:hover {
            background: #0056b3;
        }
        .search-box {
            margin-bottom: 15px;
        }
        input[type="text"] {
            padding: 8px;
            width: 80%;
            max-width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #007BFF;
            color: white;
        }
        .btn-delete {
            background: #d9534f;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-delete:hover {
            background: #c9302c;
        }
        .pagination {
            margin-top: 15px;
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
        .btn-edit {
            background: #ffc107;
            color: white;
        }
        .btn-edit:hover {
            background: #e0a800;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Kelola Akun Pengguna</h1>

        <div class="button-container">
            <a href="add_user.php" class="btn btn-add">Tambah Akun</a>
            <a href="dashboard.php" class="btn btn-back">Kembali ke Dashboard</a>
        </div>

        <!-- Form Pencarian -->
        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="Cari username atau nomor HP..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-back">Cari</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Nomor HP</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['nomorHP']) ?></td>
                            <td><?= ($user['role_id'] == 2) ? 'Bank Mini' : 'User' ?></td>
                            <td> <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-edit">Edit</a>
                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?');" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                    <button type="submit" name="delete_user" class="btn-delete">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Tidak ada data ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
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

    </div>
    <div class="content">
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
