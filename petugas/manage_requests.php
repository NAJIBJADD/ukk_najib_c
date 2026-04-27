<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'petugas') {
    header("Location: ../login.php");
    exit;
}

$requestObj = new Request();

// Proses setujui/tolak
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    $status = ($action == 'approve') ? 'disetujui' : 'ditolak';
    if ($requestObj->updateRequestStatus($id, $status, $_SESSION['user_id'])) {
        $_SESSION['success'] = "Permintaan berhasil " . ($status == 'disetujui' ? 'disetujui' : 'ditolak');
    } else {
        $_SESSION['error'] = "Gagal memproses permintaan.";
    }
    header("Location: manage_requests.php");
    exit;
}

$requests = $requestObj->getAllRequests();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4><i class="fas fa-tasks"></i> Permintaan Peminjaman (Petugas)</h4>
        </div>
        <div class="card-body">
            <?php if (empty($requests)): ?>
                <div class="alert alert-info">Belum ada permintaan pending.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>ID</th><th>Siswa</th><th>Barang</th><th>Tgl Request</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $req): ?>
                            <tr>
                                <td><?= $req['id'] ?></td>
                                <td><?= htmlspecialchars($req['siswa']) ?></td>
                                <td><?= htmlspecialchars($req['nama_item']) ?></td>
                                <td><?= $req['tgl_request'] ?></td>
                                <td>
                                    <?php if ($req['status'] == 'pending'): ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php elseif ($req['status'] == 'disetujui'): ?>
                                        <span class="badge bg-success">Disetujui</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Ditolak</span>
                                    <?php endif; ?>
                                </span>
                                </td>
                                <td>
                                    <?php if ($req['status'] == 'pending'): ?>
                                        <a href="?action=approve&id=<?= $req['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Setujui permintaan ini?')">Setujui</a>
                                        <a href="?action=reject&id=<?= $req['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tolak permintaan ini?')">Tolak</a>
                                    <?php else: ?>
                                        <span class="text-muted">Selesai</span>
                                    <?php endif; ?>
                                </td>
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