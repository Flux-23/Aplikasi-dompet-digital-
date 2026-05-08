<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>PT.Chiness</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/logo/1.png">

    <!-- Import Google Fonts & Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        /* Mengubah latar belakang menjadi putih */
        body {
            background-color: #ffffff; /* Latar belakang putih */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            text-align: center;
            color: #007bff; /* Warna teks biru */
        }

        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .content {
            background: rgba(255, 255, 255, 0.9); /* Latar belakang konten sedikit transparan */
            padding: 30px;
            width: 90%;
            max-width: 450px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2); /* Mengurangi bayangan untuk latar belakang putih */
        }

        .logo {
            width: 120px;
            margin-bottom: 15px;
        }

        h1 {
            font-weight: 700;
            margin-bottom: 20px;
            color: #007bff; /* Warna judul biru */
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            text-decoration: none;
            color: white;
            font-size: 18px;
            font-weight: 600;
            border-radius: 5px;
            transition: 0.3s;
            border: 2px solid white;
        }

        .btn-login {
            background: #ff4b2b;
        }

        .btn-login:hover {
            background: #ff1e00;
            border-color: #ff1e00;
        }

        .btn-register {
            background: #ff9800;
        }

        .btn-register:hover {
            background: #e68900;
            border-color: #e68900;
        }

        /* Footer selalu di bawah */
        .footer {
            width: 100%;
            text-align: center;
            padding: 10px;
            color: white;
            font-size: 14px;
            background: rgba(0, 0, 0, 0.2);
        }

        /* Responsive */
        @media (max-width: 600px) {
            .content {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <img src="assets/logo/1.png" alt="Digital Wallet Logo" class="logo">
            <h1>Selamat Datang di Danaku</h1>
            <a href="pages/login.php" class="btn btn-login"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="pages/register.php" class="btn btn-register"><i class="fas fa-user-plus"></i> Register</a>
        </div>
    </div>

    <!-- Font Awesome untuk ikon -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

    <!-- Footer selalu di bawah -->
    <div class="footer">
        <?php include 'pages/footer.php'; ?>
    </div>
</body>
</html>