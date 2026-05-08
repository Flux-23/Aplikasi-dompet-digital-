<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: ../index.php');
    exit();
}

include '../includes/db.php';
include 'header.php';

// Periksa apakah ID pengguna diberikan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: kelola_akun.php');
    exit();
}

$user_id = $_GET['id'];

// Ambil data pengguna berdasarkan ID
$stmt = $pdo->prepare('SELECT id, username, nomorHP, role_id FROM pengguna WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: kelola_akun.php');
    exit();
}

// Proses update data jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $nomorHP = trim($_POST['nomorHP']);
    $role_id = $_POST['role_id'];
    
    if (!empty($username) && !empty($nomorHP)) {
        $updateStmt = $pdo->prepare('UPDATE pengguna SET username = ?, nomorHP = ?, role_id = ? WHERE id = ?');
        $updateStmt->execute([$username, $nomorHP, $role_id, $user_id]);
        header('Location: kelola_akun.php');
        exit();
    } else {
        $error = 'Semua kolom harus diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
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
            max-width: 500px;
        }
        h1 {
            color: #007BFF;
        }
        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            padding: 12px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            font-weight: bold;
        }
        .btn-save {
            background: #28a745;
            color: white;
        }
        .btn-save:hover {
            background: #218838;
        }
        .btn-back {
            background: #007BFF;
            color: white;
            display: block;
            margin-top: 10px;
        }
        .btn-back:hover {
            background: #0056b3;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #555;
            padding: 10px;
            background: white;
            width: 100%;
            box-shadow: 0px -2px 5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Ubah User</h1>
        <form action="edit_user.php?id=<?php echo $user['id']; ?>" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?php echo $user['username']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="nomorHP">Nomor Telepon</label>
                <input type="number" name="nomorHP" id="nomorHP" value="<?php echo $user['nomorHP']; ?>" required>
            </div>

            <div class="form-group">
                <label for="role_id">Role</label>
                <select name="role_id" id="role_id">
                    <option value="2" <?php echo ($user['role_id'] == 2) ? 'selected' : ''; ?>>Bank Mini</option>
                    <option value="3" <?php echo ($user['role_id'] == 3) ? 'selected' : ''; ?>>Pengguna</option>
                    <option value="1" <?php echo ($user['role_id'] == 1) ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-save">Simpan Perubahan</button>
        </form>
        <a href="kelola_akun.php" class="btn btn-back">Kembali ke Dashboard</a>
    </div>
    
    <div class="content">
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
