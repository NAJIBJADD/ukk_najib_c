<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

$loanObj = new Loan();
$siswaId = $_SESSION['user_id'];
$has_late = $loanObj->checkLateLoan($siswaId);
$loans = $loanObj->getLoansByStudent($siswaId);
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
    <!-- Kartu QR Code Identitas Siswa (hanya ID) -->
    <div class="card shadow mb-4">
        <div class="card-header bg-info text-white">
            <h5><i class="fas fa-id-card"></i> Kartu Identitas Siswa (QR Code)</h5>
        </div>
        <div class="card-body text-center">
            <p>Scan QR code di bawah ini oleh petugas untuk identifikasi Anda.</p>
            <div id="qrcode" style="display: inline-block; margin: 0 auto;"></div>
            <p class="mt-2">
                <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong><br>
                NIS: <?= htmlspecialchars($_SESSION['nis'] ?? '-') ?><br>
                (<?= htmlspecialchars($_SESSION['username']) ?> | ID: <?= $_SESSION['user_id'] ?>)
            </p>
            <button class="btn btn-sm btn-secondary" onclick="printQRCode()">
                <i class="fas fa-print"></i> Cetak QR Code
            </button>
        </div>
    </div>

    <!-- Kartu Riwayat Peminjaman -->
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4><i class="fas fa-hand-holding"></i> Dashboard Siswa</h4>
        </div>
        <div class="card-body">
            <a href="scan_loan.php" class="btn btn-primary mb-3">
                <i class="fas fa-qrcode"></i> Pinjam Barang (Scan Barcode)
            </a>
            <h5>Riwayat Peminjaman Saya</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr><th>Barang</th><th>Barcode Barang</th><th>Tgl Pinjam</th><th>Batas Waktu</th><th>Status</th><th>Denda</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loans as $loan): 
                            $is_late = ($loan['status'] == 'dipinjam' && strtotime($loan['batas_waktu']) < time());
                            $badge = $is_late ? 'danger' : ($loan['status'] == 'dipinjam' ? 'warning' : 'success');
                        ?>
                        <tr class="<?= $is_late ? 'table-danger' : '' ?>">
                            <td><?= htmlspecialchars($loan['nama_item']) ?></td>
                            <td><?= htmlspecialchars($loan['barcode']) ?></td>
                            <td><?= $loan['tgl_pinjam'] ?></td>
                            <td><?= $loan['batas_waktu'] ?></td>
                            <td><span class="badge bg-<?= $badge ?>"><?= strtoupper($loan['status']) ?></span></td>
                            <td>Rp <?= number_format($loan['denda'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($loans)): ?>
                            <tr><td colspan="6" class="text-center">Belum ada riwayat peminjaman</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Library QR Code Generator -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    // Hanya ID siswa yang di-encode (angka)
    let studentId = "<?= $_SESSION['user_id'] ?>";
    let qrData = studentId;   // Tidak pakai nama
    
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