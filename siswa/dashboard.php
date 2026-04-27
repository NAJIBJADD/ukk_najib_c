<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

$loanObj = new Loan();
$requestObj = new Request();
$itemObj = new Item();
$siswaId = $_SESSION['user_id'];
$has_late = $loanObj->checkLateLoan($siswaId);
$loans = $loanObj->getLoansByStudent($siswaId);
$requests = $requestObj->getRequestsByStudent($siswaId);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<?php if ($has_late): ?>
<div class="container mt-2">
    <div class="alert alert-danger text-center blink-red">
        <i class="fas fa-bell"></i> PERINGATAN! Ada peminjaman yang melewati batas waktu! Segera kembalikan!
    </div>
</div>
<audio id="alarmSound" src="https://www.soundjay.com/misc/sounds/bell-ringing-05.mp3" loop autoplay></audio>
<?php endif; ?>

<div class="container mt-4">
    <!-- Kartu QR Code Identitas Siswa -->
    <div class="card shadow mb-4">
        <div class="card-header bg-info text-white">
            <h5><i class="fas fa-id-card"></i> Kartu Identitas Siswa</h5>
        </div>
        <div class="card-body text-center">
            <p>Scan QR code di bawah ini oleh petugas untuk identifikasi Anda.</p>
            <div id="qrcode" style="display: inline-block; margin: 0 auto;"></div>
            <p class="mt-2">
                <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong><br>
                NIS: <?= htmlspecialchars($_SESSION['nis'] ?? '-') ?><br>
                (<?= htmlspecialchars($_SESSION['username']) ?> | ID: <?= $_SESSION['user_id'] ?>)
            </p>
            <div class="mt-3">
                <button class="btn btn-sm btn-primary me-2" data-bs-toggle="modal" data-bs-target="#qrModal">
                    <i class="fas fa-expand"></i> Perbesar QR Code
                </button>
                <button class="btn btn-sm btn-success" id="downloadQRBtn">
                    <i class="fas fa-download"></i> Download QR
                </button>
                <button class="btn btn-sm btn-secondary" onclick="printQRCode()">
                    <i class="fas fa-print"></i> Cetak QR Code
                </button>
            </div>
        </div>
    </div>

    <!-- Riwayat Peminjaman Aktif & Selesai -->
    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h4><i class="fas fa-hand-holding"></i> Riwayat Peminjaman</h4>
            <a href="request_item.php" class="btn btn-light btn-sm rounded-pill">
                <i class="fas fa-hand-holding"></i> Ajukan Peminjaman Baru
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Barang</th>
                            <th>Kategori</th>
                            <th>Tgl Pinjam</th>
                            <th>Batas Waktu</th>
                            <th>Status</th>
                            <th>Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loans as $loan): 
                            $item = $itemObj->getItemById($loan['id_item']);
                            $kategori = $item['kategori'] ?? '-';
                            $is_late = ($loan['status'] == 'dipinjam' && strtotime($loan['batas_waktu']) < time());
                            $badge = $is_late ? 'danger' : ($loan['status'] == 'dipinjam' ? 'warning' : 'success');
                        ?>
                        <tr class="<?= $is_late ? 'table-danger' : '' ?>">
                            <td><?= htmlspecialchars($loan['nama_item']) ?></td>
                            <td><?= htmlspecialchars($kategori) ?></td>
                            <td><?= $loan['tgl_pinjam'] ?></td>
                            <td><?= $loan['batas_waktu'] ?></td>
                            <td><span class="badge bg-<?= $badge ?>"><?= strtoupper($loan['status']) ?></span></td>
                            <td>Rp <?= number_format($loan['denda'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($loans)): ?>
                            <tr><td colspan="6" class="text-center">Belum ada riwayat peminjaman</span></td>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Riwayat Permintaan (Termasuk Ditolak) -->
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h4><i class="fas fa-envelope"></i> Riwayat Permintaan Peminjaman</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Barang</th>
                            <th>Kategori</th>
                            <th>Tgl Request</th>
                            <th>Status</th>
                            <th>Catatan / Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): 
                            $item = $itemObj->getItemById($req['id_item']);
                            $kategori = $item['kategori'] ?? '-';
                            $status_badge = '';
                            if ($req['status'] == 'pending') $status_badge = 'warning';
                            elseif ($req['status'] == 'disetujui') $status_badge = 'success';
                            else $status_badge = 'danger';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($req['nama_item']) ?></td>
                            <td><?= htmlspecialchars($kategori) ?></td>
                            <td><?= $req['tgl_request'] ?></td>
                            <td><span class="badge bg-<?= $status_badge ?>"><?= strtoupper($req['status']) ?></span></td>
                            <td><?= htmlspecialchars($req['catatan'] ?: '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($requests)): ?>
                            <tr><td colspan="5" class="text-center">Belum ada permintaan</span></td>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <small class="text-muted fst-italic">* Permintaan yang ditolak tidak dikenakan denda. Denda hanya berlaku pada peminjaman yang sudah disetujui dan terlambat/rusak/hilang.</small>
        </div>
    </div>
</div>

<!-- Modal QR Code Besar -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-header">
                <h5 class="modal-title">QR Code Identitas Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="qrBig" style="display: inline-block;"></div>
                <p class="mt-3">
                    <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong><br>
                    ID: <?= $_SESSION['user_id'] ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    let studentId = "<?= $_SESSION['user_id'] ?>";
    let qrData = studentId;
    let qrcode = new QRCode(document.getElementById("qrcode"), {
        text: qrData,
        width: 150,
        height: 150,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

    function printQRCode() {
        let qrCanvas = document.querySelector("#qrcode img");
        if (!qrCanvas) return;
        let win = window.open();
        win.document.write("<html><head><title>Cetak QR Code Siswa</title></head><body>");
        win.document.write("<div style='text-align:center; margin-top:50px;'>");
        win.document.write("<img src='" + qrCanvas.src + "'/><br><br>");
        win.document.write("<h3><?= htmlspecialchars($_SESSION['nama']) ?></h3>");
        win.document.write("<p>NIS: <?= htmlspecialchars($_SESSION['nis'] ?? '-') ?></p>");
        win.document.write("<p><?= htmlspecialchars($_SESSION['username']) ?> (ID: <?= $_SESSION['user_id'] ?>)</p>");
        win.document.write("</div></body></html>");
        win.print();
        win.close();
    }

    document.getElementById('downloadQRBtn').addEventListener('click', function() {
        let qrImg = document.querySelector('#qrcode img');
        if (qrImg) {
            let link = document.createElement('a');
            link.download = 'qr_siswa.png';
            link.href = qrImg.src;
            link.click();
        }
    });

    $('#qrModal').on('show.bs.modal', function () {
        document.getElementById('qrBig').innerHTML = '';
        new QRCode(document.getElementById('qrBig'), {
            text: studentId,
            width: 250,
            height: 250,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    });
</script>

<style>
.blink-red {
    animation: blink 0.8s infinite;
}
@keyframes blink {
    0% { background-color: #f8d7da; color: black; }
    50% { background-color: #dc3545; color: white; }
    100% { background-color: #f8d7da; color: black; }
}
</style>

<?php include '../includes/footer.php'; ?>