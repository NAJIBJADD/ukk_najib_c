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
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6; /* Abu-abu sangat muda untuk background luar */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .login-container {
            width: 100%;
            max-width: 1200px;
        }

        .card-login {
            background: #ffffff; /* PUTIH POLOS */
            border-radius: 2rem;
            border: none;
            box-shadow: 0 20px 35px -8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Kiri dan kanan sama-sama putih */
        .login-left, .login-right {
            background: #ffffff;
            padding: 2.5rem;
        }

        .login-left {
            border-right: 1px solid #eef2ff;
        }

        .login-left h2 {
            font-weight: 700;
            font-size: 1.8rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .login-left h2 i {
            color: #4f46e5;
        }

        .login-left p {
            color: #475569;
            margin-bottom: 1.5rem;
        }

        .feature-list {
            list-style: none;
            padding: 0;
        }

        .feature-list li {
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: #334155;
        }

        .feature-list i {
            color: #4f46e5;
            font-size: 1.1rem;
            background: #eef2ff;
            width: 2rem;
            height: 2rem;
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
            font-size: 3rem;
            color: #4f46e5;
            margin-bottom: 0.5rem;
        }

        .login-header h4 {
            font-weight: 700;
            color: #0f172a;
        }

        .login-header p {
            color: #64748b;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: #334155;
            margin-bottom: 0.5rem;
            display: block;
        }

        .input-group-custom {
            position: relative;
        }

        .input-group-custom i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .form-control-custom {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 2rem;
            transition: all 0.2s;
            background: #ffffff;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .btn-login {
            background: #4f46e5;
            border: none;
            padding: 0.8rem;
            font-weight: 600;
            border-radius: 2rem;
            width: 100%;
            color: white;
            transition: all 0.2s;
        }

        .btn-login:hover {
            background: #6366f1;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -4px rgba(79, 70, 229, 0.3);
        }

        .demo-accounts {
            background: #f8fafc;
            border-radius: 1.5rem;
            padding: 1rem;
            margin-top: 1.8rem;
            text-align: center;
        }

        .demo-badge {
            display: inline-block;
            background: white;
            padding: 0.3rem 0.8rem;
            border-radius: 2rem;
            margin: 0.2rem;
            font-size: 0.75rem;
            color: #4f46e5;
            border: 1px solid #e2e8f0;
        }

        .alert-custom {
            border-radius: 1.5rem;
            background: #fee2e2;
            border: none;
            color: #991b1b;
            padding: 0.8rem 1rem;
        }

        @media (max-width: 768px) {
            .login-left {
                display: none;
            }
            .login-right {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="row g-0 card-login">
        <div class="col-md-6 login-left">
            <div>
                <h2><i class="fas fa-book-open me-2"></i> Perpustakaan Digital</h2>
                <p>Kelola peminjaman buku dan alat dengan mudah, cepat, dan modern.<br>Akses di mana saja, kapan saja.</p>
                <ul class="feature-list">
                    <li><i class="fas fa-check-circle"></i> <span>Manajemen barang & kategori</span></li>
                    <li><i class="fas fa-qrcode"></i> <span>Scan barcode / QR Code</span></li>
                    <li><i class="fas fa-hand-holding-heart"></i> <span>Peminjaman dengan persetujuan</span></li>
                    <li><i class="fas fa-chart-line"></i> <span>Laporan & denda otomatis</span></li>
                    <li><i class="fas fa-bell"></i> <span>Notifikasi & alarm keterlambatan</span></li>
                </ul>
            </div>
        </div>
        <div class="col-md-6 login-right">
            <div class="login-header">
                <div class="logo-icon">
                    <i class="fas fa-landmark"></i>
                </div>
                <h4>Selamat Datang Kembali</h4>
                <p>Silakan masuk ke akun Anda</p>
            </div>
            <?php if($error): ?>
                <div class="alert alert-custom mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?= $error ?>
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