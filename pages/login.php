<?php
session_start();
include '../includes/db.php';
include'header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM pengguna WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role_id'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | E-Wallet</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 100%;
            max-width: 300px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        h1 {
            color: #007BFF;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background: #007BFF;
            color: white;
            border: none;
            padding: 10px;
            font-size: 18px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .register-link {
            margin-top: 10px;
            font-size: 14px;
        }
        .register-link a {
            color: #007BFF;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .button-container{
            background: #007BFF;
            color: white;
            border: none;
            padding: 10px;
            font-size: 18px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s; 
        }
        .button-container:hover{
           background: #0056b3; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <h1><a href="../index.php" class="button-container">kembali</a></h1>
        <p class="register-link">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </div>
    <div class="content"></div>
    <?php include 'footer.php'; ?>
</body>
</html>
