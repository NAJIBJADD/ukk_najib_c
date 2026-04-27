<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$userObj = new User();
$logObj = new Log();
$id = (int)$_GET['id'];
$user = $userObj->getUserById($id);
if (!$user) {
    $_SESSION['error'] = "User tidak ditemukan.";
    header("Location: manage_users.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $role = $_POST['role'];
    $rayon = trim($_POST['rayon'] ?? '');
    $rombel = trim($_POST['rombel'] ?? '');
    $nis = trim($_POST['nis'] ?? '');
    
    // Update data
    $stmt = Database::getInstance()->getConnection()->prepare("UPDATE users SET nama_lengkap = ?, role = ?, rayon = ?, rombel = ?, nis = ? WHERE id = ?");
    $result = $stmt->execute([$nama_lengkap, $role, $rayon, $rombel, $nis, $id]);
    if ($result) {
        $logObj->add($_SESSION['user_id'], 'Edit User', "User ID $id diubah");
        $_SESSION['success'] = "Data user berhasil diupdate.";
        header("Location: manage_users.php");
        exit;
    } else {
        $error = "Gagal update data.";
    }
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning">
            <h4>Edit User: <?= htmlspecialchars($user['username']) ?></h4>
        </div>
        <div class="card-body">
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="POST">
                <div class="mb-2"><label>Username</label><input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled></div>
                <div class="mb-2"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required></div>
                <div class="mb-2">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="siswa" <?= $user['role']=='siswa'?'selected':'' ?>>Siswa</option>
                        <option value="petugas" <?= $user['role']=='petugas'?'selected':'' ?>>Petugas</option>
                        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                    </select>
                </div>
                <div class="mb-2"><label>NIS (khusus siswa)</label><input type="text" name="nis" class="form-control" value="<?= htmlspecialchars($user['nis'] ?? '') ?>" placeholder="NIS"></div>
                <div class="mb-2"><label>Rayon (khusus siswa)</label><input type="text" name="rayon" class="form-control" value="<?= htmlspecialchars($user['rayon'] ?? '') ?>" placeholder="Rayon"></div>
                <div class="mb-2"><label>Rombel (khusus siswa)</label><input type="text" name="rombel" class="form-control" value="<?= htmlspecialchars($user['rombel'] ?? '') ?>" placeholder="Rombel"></div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="manage_users.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>