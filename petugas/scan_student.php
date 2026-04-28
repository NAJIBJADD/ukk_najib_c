<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'petugas') {
    header("Location: ../login.php");
    exit;
}

$requestObj = new Request();
$studentData = null;
$pendingRequests = [];
$error = '';
$searchResult = [];

// Proses pencarian nama siswa (GET)
if (isset($_GET['search_name']) && !empty(trim($_GET['search_name']))) {
    $keyword = '%' . trim($_GET['search_name']) . '%';
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'siswa' AND (nama_lengkap LIKE ? OR username LIKE ?) ORDER BY nama_lengkap ASC");
    $stmt->execute([$keyword, $keyword]);
    $searchResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Proses pilih siswa dari hasil pencarian
if (isset($_GET['select_student'])) {
    $studentId = (int)$_GET['select_student'];
    $userObj = new User();
    $student = $userObj->getUserById($studentId);
    if ($student && $student['role'] == 'siswa') {
        $studentData = $student;
        $allRequests = $requestObj->getRequestsByStudent($studentData['id']);
        foreach ($allRequests as $req) {
            if ($req['status'] == 'pending') {
                $pendingRequests[] = $req;
            }
        }
    } else {
        $error = "Siswa tidak ditemukan.";
    }
}

// Proses scan dari kamera atau manual atau upload (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['barcode'])) {
    $scannedId = trim($_POST['barcode']);
    if (preg_match('/^(\d+)/', $scannedId, $matches)) {
        $scannedId = $matches[1];
    }
    $userObj = new User();
    $allUsers = $userObj->getAllUsers();
    foreach ($allUsers as $user) {
        if ($user['id'] == $scannedId && $user['role'] == 'siswa') {
            $studentData = $user;
            break;
        }
    }
    if (!$studentData) {
        $error = "QR Code tidak dikenali. Pastikan scan QR code siswa yang valid (berisi ID angka).";
    } else {
        $allRequests = $requestObj->getRequestsByStudent($studentData['id']);
        foreach ($allRequests as $req) {
            if ($req['status'] == 'pending') {
                $pendingRequests[] = $req;
            }
        }
    }
}

// Proses setujui/tolak
if (isset($_POST['action']) && isset($_POST['request_id'])) {
    $action = $_POST['action'];
    $requestId = (int)$_POST['request_id'];
    $status = ($action == 'approve') ? 'disetujui' : 'ditolak';
    if ($requestObj->updateRequestStatus($requestId, $status, $_SESSION['user_id'])) {
        $_SESSION['success'] = "Permintaan berhasil " . ($status == 'disetujui' ? 'disetujui' : 'ditolak');
    } else {
        $_SESSION['error'] = "Gagal memproses permintaan.";
    }
    header("Location: scan_student.php");
    exit;
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-id-card"></i> Scan QR Code Siswa & Persetujuan Peminjaman</h4>
            <a href="dashboard.php" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
        <div class="card-body">
            <!-- Pencarian Nama Siswa -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <strong>Cari Siswa berdasarkan Nama</strong>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" name="search_name" class="form-control" placeholder="Masukkan nama depan" value="<?= htmlspecialchars($_GET['search_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </form>
                    <?php if (!empty($searchResult)): ?>
                        <hr>
                        <h6>Hasil pencarian:</h6>
                        <ul class="list-group">
                            <?php foreach ($searchResult as $siswa): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($siswa['nama_lengkap']) ?> (<?= htmlspecialchars($siswa['username']) ?>)
                                    <a href="?select_student=<?= $siswa['id'] ?>" class="btn btn-sm btn-success">Pilih</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php elseif (isset($_GET['search_name']) && empty($searchResult)): ?>
                        <div class="alert alert-warning mt-2">Tidak ditemukan siswa dengan nama tersebut.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Petugas: Scan QR Code siswa (berisi ID angka) atau cari berdasarkan nama untuk melihat permintaan pending.
            </div>
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#cameraTab">📷 Scan Kamera</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#uploadTab">📤 Upload Gambar</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#manualTab">⌨️ Input Manual ID</button></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="cameraTab">
                    <div id="reader" style="width:100%; max-width:500px; margin:0 auto;"></div>
                    <div id="result" class="mt-2 text-center"></div>
                    <form id="autoFormCamera" method="POST">
                        <input type="hidden" name="barcode" id="scannedBarcodeCamera">
                    </form>
                </div>
                <div class="tab-pane fade" id="uploadTab">
                    <div class="text-center">
                        <input type="file" id="qrUpload" accept="image/*" class="form-control mb-2" style="max-width:300px; margin:0 auto;">
                        <div id="uploadPreview" class="mt-2"></div>
                        <div id="uploadResult" class="mt-2"></div>
                        <form id="autoFormUpload" method="POST">
                            <input type="hidden" name="barcode" id="scannedBarcodeUpload">
                        </form>
                    </div>
                </div>
                <div class="tab-pane fade" id="manualTab">
                    <form method="POST">
                        <div class="input-group">
                            <input type="text" name="barcode" class="form-control" placeholder="Masukkan ID Siswa (contoh: 3)" required>
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger mt-3"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($studentData): ?>
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <h5>Data Siswa: <?= htmlspecialchars($studentData['nama_lengkap']) ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr><th style="width: 30%;">ID</th><td><?= $studentData['id'] ?></td></tr>
                                <tr><th>Username</th><td><?= htmlspecialchars($studentData['username']) ?></td></tr>
                                <tr><th>Nama Lengkap</th><td><?= htmlspecialchars($studentData['nama_lengkap']) ?></td></tr>
                                <tr><th>NIS</th><td><?= htmlspecialchars($studentData['nis'] ?? '-') ?></td></tr>
                                <tr><th>Rayon</th><td><?= htmlspecialchars($studentData['rayon'] ?? '-') ?></td></tr>
                                <tr><th>Rombel</th><td><?= htmlspecialchars($studentData['rombel'] ?? '-') ?></td></tr>
                            </table>
                        </div>
                        <h5 class="mt-3">📋 Permintaan Peminjaman (Pending)</h5>
                        <?php if (empty($pendingRequests)): ?>
                            <div class="alert alert-info">Tidak ada permintaan pending dari siswa ini.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Barang</th>
                                            <th>Tanggal Request</th>
                                            <th>Tanggal Kembali Diminta</th>
                                            <th>Catatan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingRequests as $req): ?>
                                            <tr>
                                                <td><?= $req['id'] ?></td>
                                                <td><?= htmlspecialchars($req['nama_item']) ?></td>
                                                <td><?= time_elapsed_string($req['tgl_request']) ?></td>
                                                <td><?= $req['requested_return_date'] ?? '-' ?></td>
                                                <td><?= htmlspecialchars($req['catatan']) ?></td>
                                                <td>
                                                    <form method="POST" style="display:inline-block">
                                                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Setujui peminjaman ini?')">✅ Setujui</button>
                                                    </form>
                                                    <form method="POST" style="display:inline-block">
                                                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tolak permintaan ini?')">❌ Tolak</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const html5QrCode = new Html5Qrcode("reader");
    let isScanning = false;
    function startScanner() {
        if (isScanning) return;
        html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: 300 },
            (decodedText) => {
                html5QrCode.stop();
                isScanning = false;
                document.getElementById("scannedBarcodeCamera").value = decodedText;
                document.getElementById("autoFormCamera").submit();
            },
            (err) => {}
        ).then(() => { isScanning = true; }).catch(err => console.warn(err));
    }
    function stopScanner() {
        if (isScanning) {
            html5QrCode.stop();
            isScanning = false;
        }
    }
    
    const uploadInput = document.getElementById('qrUpload');
    const uploadPreview = document.getElementById('uploadPreview');
    const uploadResult = document.getElementById('uploadResult');
    uploadInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(ev) {
            const img = new Image();
            img.onload = function() {
                uploadPreview.innerHTML = '<img src="'+img.src+'" style="max-width:200px; max-height:200px;">';
                const qrScanner = new Html5Qrcode("uploadPreview");
                qrScanner.scanFile(file, true)
                    .then(decodedText => {
                        uploadResult.innerHTML = '<div class="alert alert-success">Barcode terdeteksi: '+decodedText+'</div>';
                        document.getElementById("scannedBarcodeUpload").value = decodedText;
                        document.getElementById("autoFormUpload").submit();
                    })
                    .catch(err => {
                        uploadResult.innerHTML = '<div class="alert alert-danger">Gagal membaca QR code dari gambar: '+err+'</div>';
                    });
            };
            img.src = ev.target.result;
        };
        reader.readAsDataURL(file);
    });
    
    document.querySelector('button[data-bs-target="#cameraTab"]').addEventListener('shown.bs.tab', startScanner);
    document.querySelector('button[data-bs-target="#uploadTab"]').addEventListener('shown.bs.tab', stopScanner);
    document.querySelector('button[data-bs-target="#manualTab"]').addEventListener('shown.bs.tab', stopScanner);
    startScanner();
    window.addEventListener('beforeunload', stopScanner);
</script>
<?php include '../includes/footer.php'; ?>