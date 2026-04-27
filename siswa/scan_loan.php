<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

$itemObj = new Item();
$requestObj = new Request();

$error = '';
$success = '';

// Proses dari kamera, upload, atau manual
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['barcode_manual'])) {
    $barcode = $_POST['barcode_manual'];
    $siswaId = $_SESSION['user_id'];
    $item = $itemObj->getItemByBarcode($barcode);
    if ($item) {
        // Cek apakah sudah ada request pending untuk barang ini
        $pending = false;
        $requests = $requestObj->getRequestsByStudent($siswaId);
        foreach ($requests as $req) {
            if ($req['id_item'] == $item['id'] && $req['status'] == 'pending') {
                $pending = true;
                break;
            }
        }
        if ($pending) {
            $error = "Anda sudah memiliki permintaan pending untuk barang ini. Tunggu konfirmasi petugas.";
        } else {
            if ($requestObj->createRequest($siswaId, $item['id'], "Permintaan via scan")) {
                $success = "Permintaan peminjaman {$item['nama_item']} telah dikirim ke petugas.";
            } else {
                $error = "Gagal mengirim permintaan. Coba lagi.";
            }
        }
    } else {
        $error = "Barcode tidak ditemukan atau barang sedang tidak tersedia.";
    }
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4><i class="fas fa-camera"></i> Permintaan Peminjaman Barang</h4>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Scan QR code atau barcode barang (camera/upload gambar) untuk mengirim permintaan ke petugas.
            </div>
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#cameraTab">📷 Scan Kamera</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#uploadTab">📤 Upload Gambar</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#manualTab">⌨️ Input Manual</button></li>
            </ul>
            <div class="tab-content">
                <!-- Tab Kamera -->
                <div class="tab-pane fade show active" id="cameraTab">
                    <div id="reader" style="width:100%; max-width:500px; margin:0 auto;"></div>
                    <div id="cameraResult" class="mt-2"></div>
                    <form id="autoFormCamera" method="POST">
                        <input type="hidden" name="barcode_manual" id="scannedBarcodeCamera">
                    </form>
                </div>
                <!-- Tab Upload Gambar -->
                <div class="tab-pane fade" id="uploadTab">
                    <div class="text-center">
                        <input type="file" id="barcodeUpload" accept="image/*" class="form-control mb-2" style="max-width:300px; margin:0 auto;">
                        <div id="uploadPreview" class="mt-2"></div>
                        <div id="uploadResult" class="mt-2"></div>
                        <form id="autoFormUpload" method="POST">
                            <input type="hidden" name="barcode_manual" id="scannedBarcodeUpload">
                        </form>
                    </div>
                </div>
                <!-- Tab Input Manual -->
                <div class="tab-pane fade" id="manualTab">
                    <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                    <form method="POST">
                        <div class="input-group">
                            <input type="text" name="barcode_manual" class="form-control" placeholder="Ketik barcode barang" required>
                            <button type="submit" class="btn btn-primary">Kirim Permintaan</button>
                        </div>
                    </form>
                </div>
            </div>
            <hr>
            <h5>Riwayat Permintaan Saya</h5>
            <?php
            $requests = $requestObj->getRequestsByStudent($_SESSION['user_id']);
            if (empty($requests)): ?>
                <p class="text-muted">Belum ada permintaan.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Barang</th><th>Tgl Request</th><th>Status</th><th>Catatan</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $req): ?>
                            <tr>
                                <td><?= htmlspecialchars($req['nama_item']) ?></td>
                                <td><?= $req['tgl_request'] ?></td>
                                <td>
                                    <?php if ($req['status'] == 'pending'): ?>
                                        <span class="badge bg-warning">Menunggu</span>
                                    <?php elseif ($req['status'] == 'disetujui'): ?>
                                        <span class="badge bg-success">Disetujui</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($req['catatan']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Library html5-qrcode untuk scan kamera dan gambar -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    // ========== KAMERA ==========
    const html5QrCode = new Html5Qrcode("reader");
    let isScanning = false;
    function startScanner() {
        if (isScanning) return;
        html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 },
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
    
    // ========== UPLOAD GAMBAR ==========
    const uploadInput = document.getElementById('barcodeUpload');
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
                // Scan file gambar menggunakan html5-qrcode
                const qrScanner = new Html5Qrcode("uploadPreview");
                qrScanner.scanFile(file, true)
                    .then(decodedText => {
                        uploadResult.innerHTML = '<div class="alert alert-success">Barcode terdeteksi: '+decodedText+'</div>';
                        document.getElementById("scannedBarcodeUpload").value = decodedText;
                        document.getElementById("autoFormUpload").submit();
                    })
                    .catch(err => {
                        uploadResult.innerHTML = '<div class="alert alert-danger">Gagal membaca QR/Barcode dari gambar. Pastikan gambar jelas.</div>';
                        console.error(err);
                    });
            };
            img.src = ev.target.result;
        };
        reader.readAsDataURL(file);
    });
    
    // ========== TAB SWITCHING ==========
    document.querySelector('button[data-bs-target="#cameraTab"]').addEventListener('shown.bs.tab', startScanner);
    document.querySelector('button[data-bs-target="#uploadTab"]').addEventListener('shown.bs.tab', stopScanner);
    document.querySelector('button[data-bs-target="#manualTab"]').addEventListener('shown.bs.tab', stopScanner);
    startScanner();
    window.addEventListener('beforeunload', stopScanner);
</script>
<?php include '../includes/footer.php'; ?>