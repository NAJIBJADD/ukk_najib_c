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

// Proses dari manual, kamera, atau upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['barcode_manual'])) {
    $barcode = trim($_POST['barcode_manual']);
    $siswaId = $_SESSION['user_id'];
    $item = $itemObj->getItemByBarcode($barcode);
    
    if (!$item) {
        $allItems = $itemObj->getAllItems();
        $sampleBarcodes = array_slice(array_column($allItems, 'barcode'), 0, 5);
        $error = "❌ Barcode/QR '$barcode' tidak ditemukan atau stok habis.<br>💡 Contoh tersedia: " . implode(', ', $sampleBarcodes);
    } else {
        // Cek request pending
        $pending = false;
        $requests = $requestObj->getRequestsByStudent($siswaId);
        foreach ($requests as $req) {
            if ($req['id_item'] == $item['id'] && $req['status'] == 'pending') {
                $pending = true;
                break;
            }
        }
        if ($pending) {
            $error = "⚠️ Anda sudah memiliki permintaan pending untuk '{$item['nama_item']}'. Tunggu konfirmasi petugas.";
        } else {
            if ($requestObj->createRequest($siswaId, $item['id'], "Permintaan via scan")) {
                $success = "✅ Permintaan peminjaman '{$item['nama_item']}' berhasil dikirim ke petugas.";
            } else {
                $error = "❌ Gagal mengirim permintaan. Coba lagi.";
            }
        }
    }
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-qrcode"></i> Scan QR Code Barang (Kotak)</h4>
            <a href="dashboard.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Arahkan kamera ke QR Code barang (kotak). Bisa juga upload gambar QR atau input manual kode.
            </div>
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#cameraTab">📷 Scan Kamera</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#uploadTab">📤 Upload Gambar QR</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#manualTab">⌨️ Input Manual</button></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="cameraTab">
                    <div id="qr-reader" style="width:100%; max-width:500px; margin:0 auto;"></div>
                    <div id="cameraResult" class="mt-2 text-center"></div>
                    <form id="autoFormCamera" method="POST">
                        <input type="hidden" name="barcode_manual" id="scannedBarcodeCamera">
                    </form>
                </div>
                <div class="tab-pane fade" id="uploadTab">
                    <div class="text-center">
                        <input type="file" id="qrUpload" accept="image/*" class="form-control mb-2" style="max-width:300px; margin:0 auto;">
                        <div id="uploadPreview" class="mt-2"></div>
                        <div id="uploadResult" class="mt-2"></div>
                        <form id="autoFormUpload" method="POST">
                            <input type="hidden" name="barcode_manual" id="scannedBarcodeUpload">
                        </form>
                    </div>
                </div>
                <div class="tab-pane fade" id="manualTab">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="input-group">
                            <input type="text" name="barcode_manual" class="form-control" placeholder="Masukkan kode QR / barcode" required>
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
                    <table class="table table-bordered table-striped">
                        <thead>
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
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    // Inisialisasi scanner kamera (khusus QR code)
    const html5QrCode = new Html5Qrcode("qr-reader");
    let isScanning = false;

    function startScanner() {
        if (isScanning) return;
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            (decodedText) => {
                // Berhenti scan setelah berhasil
                html5QrCode.stop();
                isScanning = false;
                document.getElementById("scannedBarcodeCamera").value = decodedText;
                document.getElementById("autoFormCamera").submit();
            },
            (errorMessage) => {
                // Abaikan error scan sementara, teruskan
            }
        ).then(() => {
            isScanning = true;
        }).catch(err => {
            console.error("Gagal memulai kamera: ", err);
            alert("Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.");
        });
    }

    function stopScanner() {
        if (isScanning) {
            html5QrCode.stop();
            isScanning = false;
        }
    }

    // Upload gambar QR
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
                // Gunakan html5-qrcode untuk scan dari file gambar
                const qrScanner = new Html5Qrcode("uploadPreview");
                qrScanner.scanFile(file, true)
                    .then(decodedText => {
                        uploadResult.innerHTML = '<div class="alert alert-success">QR terdeteksi: '+decodedText+'</div>';
                        document.getElementById("scannedBarcodeUpload").value = decodedText;
                        document.getElementById("autoFormUpload").submit();
                    })
                    .catch(err => {
                        uploadResult.innerHTML = '<div class="alert alert-danger">Gagal membaca QR dari gambar. Pastikan gambar jelas dan berisi QR code.</div>';
                        console.error(err);
                    });
            };
            img.src = ev.target.result;
        };
        reader.readAsDataURL(file);
    });

    // Tab switching
    document.querySelector('button[data-bs-target="#cameraTab"]').addEventListener('shown.bs.tab', startScanner);
    document.querySelector('button[data-bs-target="#uploadTab"]').addEventListener('shown.bs.tab', stopScanner);
    document.querySelector('button[data-bs-target="#manualTab"]').addEventListener('shown.bs.tab', stopScanner);
    // Mulai scan saat halaman dimuat
    startScanner();
    // Hentikan scan saat halaman ditutup
    window.addEventListener('beforeunload', stopScanner);
</script>
<?php include '../includes/footer.php'; ?>