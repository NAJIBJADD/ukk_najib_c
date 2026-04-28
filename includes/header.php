<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>電子図書館｜Perpustakaan Digital</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Font: Noto Sans JP (gaya Jepang) -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- JS Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        /* sentuhan Jepang: background gradien lembut pink/sakura */
        body {
            background: linear-gradient(145deg, #fef3f0 0%, #fff5f7 100%);
            font-family: 'Noto Sans JP', 'Inter', sans-serif;
        }
        /* hiasan kecil seperti bunga sakura di pojok (opsional) */
        body::before {
            content: "🌸";
            position: fixed;
            bottom: 20px;
            left: 20px;
            font-size: 2rem;
            opacity: 0.2;
            pointer-events: none;
            z-index: 0;
        }
        body::after {
            content: "🍁";
            position: fixed;
            top: 20px;
            right: 20px;
            font-size: 2rem;
            opacity: 0.2;
            pointer-events: none;
            z-index: 0;
        }
        /* card dengan border bawah merah seperti amplop Jepang */
        .card {
            border-bottom: 3px solid #e07a5f;
        }
        .btn-primary {
            background: #c44536;
            border: none;
        }
        .btn-primary:hover {
            background: #ab2f1f;
        }
        .navbar-brand {
            background: transparent;
            -webkit-background-clip: unset;
            background-clip: unset;
            color: #8b3c2c !important;
            text-shadow: none;
        }
        .navbar-brand i {
            color: #e07a5f;
        }
    </style>
</head>
<body>