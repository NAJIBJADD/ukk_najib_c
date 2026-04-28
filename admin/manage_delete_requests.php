<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = Database::getInstance()->getConnection();

// Proses setujui
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $stmt = $pdo->prepare("UPDATE delete_requests SET status = 'approved' WHERE id = ?");
    $stmt->execute([$id]);
    // Ambil loan_id
    $stmt2 = $pdo->prepare("SELECT loan_id FROM delete_requests WHERE id = ?");
    $stmt2->execute([$id]);
    $req = $stmt2->fetch(PDO::FETCH_ASSOC);
    if ($req) {
        $loanObj = new Loan();
        $loanObj->deleteLoan($req['loan_id']);
    }
    $_SESSION['success'] = "Permintaan hapus disetujui dan peminjaman dihapus.";
    header("Location: manage_delete_requests.php");
    exit;
}

// Proses tolak
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $stmt = $pdo->prepare("UPDATE delete_requests SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = "Permintaan hapus ditolak.";
    header("Location: manage_delete_requests.php");
    exit;
}

// Ambil semua request pending - sesuaikan nama kolom petugas (id_petugas atau petugas_id)
$sql = "SELECT dr.*, l.id_siswa, u.nama_lengkap AS siswa, i.nama_item, p.nama_lengkap AS petugas
        FROM delete_requests dr
        JOIN loans l ON dr.loan_id = l.id
        JOIN users u ON l.id_siswa = u.id
        JOIN items i ON l.id_item = i.id
        JOIN users p ON dr.id_petugas = p.id   -- ubah petugas_id menjadi id_petugas
        WHERE dr.status = 'pending'
        ORDER BY dr.tgl_request ASC";
$stmt = $pdo->query($sql);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white">
            <h4>Persetujuan Hapus Peminjaman</h4>
        </div>
        <div class="card-body">
            <?php if (empty($requests)): ?>
                <div class="alert alert-info">Tidak ada permintaan hapus pending.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>ID</th><th>Siswa</th><th>Barang</th><th>Petugas</th><th>Alasan</th><th>Tgl Request</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $req): ?>
                            <tr>
                                <td><?= $req['id_siswa'] ?></td>
                                <td><?= htmlspecialchars($req['siswa']) ?></span>
                                <td><?= htmlspecialchars($req['nama_item']) ?> </span>
                                <td><?= htmlspecialchars($req['petugas']) ?> </span>
                                <td><?= nl2br(htmlspecialchars($req['alasan'])) ?> </span>
                                <td><?= time_elapsed_string($req['tgl_request']) ?> </span>
                                <td>
                                    <a href="?approve=<?= $req['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Setujui penghapusan?')">Setujui</a>
                                    <a href="?reject=<?= $req['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tolak penghapusan?')">Tolak</a>
                                 </span>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>