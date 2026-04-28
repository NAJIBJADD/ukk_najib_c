<?php
require_once 'includes/autoload.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . $_SESSION['role'] . "/dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = new User();
    if ($user->login($_POST['username'], $_POST['password'])) {
        $user->setSession();
        header("Location: " . $_SESSION['role'] . "/dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Masuk - Perpustakaan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans JP', 'Inter', sans-serif;
            background: #fef9f5;
            background-image: radial-gradient(circle at 10% 20%, rgba(245, 203, 192, 0.1) 0%, transparent 50%),
                              repeating-linear-gradient(45deg, rgba(230, 100, 80, 0.02) 0px, rgba(230, 100, 80, 0.02) 2px, transparent 2px, transparent 8px);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
            position: relative;
        }
        body::before {
            content: "🌸";
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 2rem;
            opacity: 0.3;
            z-index: 0;
        }
        body::after {
            content: "🍁";
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 2rem;
            opacity: 0.3;
            z-index: 0;
        }

        .login-container {
            width: 100%;
            max-width: 1100px;
            position: relative;
            z-index: 1;
        }

        .card-login {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 2rem;
            border: none;
            box-shadow: 0 20px 35px -8px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(224, 122, 95, 0.2);
            overflow: hidden;
            backdrop-filter: blur(2px);
        }

        .login-left, .login-right {
            background: transparent;
            padding: 2.2rem;
            position: relative;
        }

        .login-left {
            border-right: 1px solid #f0ded8;
            background: linear-gradient(135deg, #fff9f7 0%, #fff 100%);
        }

        .login-left h2 {
            font-weight: 700;
            font-size: 1.7rem;
            color: #5e2e2a;
            margin-bottom: 0.5rem;
        }
        .login-left h2 i {
            color: #e07a5f;
        }
        .login-left p {
            color: #8b5e5a;
            margin-bottom: 1.2rem;
            font-size: 0.9rem;
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            margin-bottom: 0.7rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            color: #4e3a37;
            font-size: 0.9rem;
        }
        .feature-list i {
            color: #e07a5f;
            font-size: 1rem;
            background: #fff1ec;
            width: 1.8rem;
            height: 1.8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header .logo-icon {
            font-size: 2.8rem;
            color: #e07a5f;
            margin-bottom: 0.5rem;
        }
        .login-header h4 {
            font-weight: 700;
            color: #3d2c29;
            font-size: 1.4rem;
        }
        .login-header p {
            color: #a7847e;
            font-size: 0.85rem;
        }

        .form-group {
            margin-bottom: 1.3rem;
        }
        .form-group label {
            font-weight: 500;
            color: #6b4b46;
            margin-bottom: 0.4rem;
            display: block;
            font-size: 0.9rem;
        }
        .input-group-custom {
            position: relative;
        }
        .input-group-custom i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #c9aaa2;
        }
        .form-control-custom {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.4rem;
            border: 1px solid #f0d4cc;
            border-radius: 2rem;
            transition: all 0.2s;
            background: #ffffff;
            font-size: 0.9rem;
        }
        .form-control-custom:focus {
            outline: none;
            border-color: #e07a5f;
            box-shadow: 0 0 0 3px rgba(224, 122, 95, 0.15);
        }

        .btn-login {
            background: #e07a5f;
            border: none;
            padding: 0.7rem;
            font-weight: 600;
            border-radius: 2rem;
            width: 100%;
            color: white;
            transition: all 0.2s;
            font-size: 1rem;
            margin-top: 0.5rem;
        }
        .btn-login:hover {
            background: #c55a3e;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -4px rgba(224, 122, 95, 0.4);
        }

        .alert-custom {
            border-radius: 1.5rem;
            background: #ffe6e2;
            border: none;
            color: #bc5a42;
            padding: 0.7rem 1rem;
            font-size: 0.85rem;
        }

        @media (max-width: 768px) {
            body { padding: 0.8rem; }
            .login-left { display: none; }
            .login-right { padding: 1.5rem; }
            .card-login { border-radius: 1.5rem; }
            .login-header .logo-icon { font-size: 2.2rem; }
            .login-header h4 { font-size: 1.2rem; }
        }

        @media (max-width: 480px) {
            .login-right { padding: 1.2rem; }
            .form-control-custom { padding: 0.6rem 1rem 0.6rem 2.2rem; }
        }

        .login-left::before {
            content: "🌸";
            position: absolute;
            bottom: 15px;
            right: 15px;
            font-size: 1.3rem;
            opacity: 0.25;
            pointer-events: none;
        }
        .login-right::after {
            content: "🍂";
            position: absolute;
            top: 15px;
            left: 15px;
            font-size: 1.3rem;
            opacity: 0.2;
            pointer-events: none;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="row g-0 card-login">
        <div class="col-md-6 login-left">
            <div>
                <h2>
                    <i class="fas fa-book-open me-2"></i>
                    <span>Denshi Toshokan</span>
                    <span style="display: block; font-size: 0.75rem; font-weight: normal;">(Perpustakaan Digital)</span>
                </h2>
                <p>Kelola peminjaman buku dan alat dengan mudah, cepat, dan modern.<br>Akses di mana saja, kapan saja.</p>
                <ul class="feature-list">
                    <li><i class="fas fa-check-circle"></i> <span>Manajemen barang & kategori</span></li>
                    <li><i class="fas fa-qrcode"></i> <span>Scan QR Code Siswa</span></li>
                    <li><i class="fas fa-hand-holding-heart"></i> <span>Peminjaman dengan persetujuan</span></li>
                    <li><i class="fas fa-chart-line"></i> <span>Laporan & denda otomatis</span></li>
                    <li><i class="fas fa-bell"></i> <span>Notifikasi & alarm keterlambatan</span></li>
                </ul>
            </div>
        </div>
        <div class="col-md-6 login-right">
            <div class="login-header">
                <div class="logo-icon">
                    <i class="fas fa-torii-gate"></i>
                </div>
                <h4>Selamat Datang Kembali</h4>
                <p>Silakan masuk ke akun Anda</p>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-custom mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-group-custom">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" class="form-control-custom" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-group-custom">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control-custom" placeholder="Masukkan password" required>
                    </div>
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i> Masuk
                </button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>