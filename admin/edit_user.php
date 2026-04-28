<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$userObj = new User();
$id = (int)$_GET['id'];
$user = $userObj->getUserById($id);
if (!$user) {
    $_SESSION['error'] = "User tidak ditemukan.";
    header("Location: manage_users.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $role       = $_POST['role'];
    $password   = trim($_POST['password'] ?? '');
    
    // Data khusus siswa (hanya akan dipakai jika role = siswa)
    $rayon  = trim($_POST['rayon'] ?? '');
    $rombel = trim($_POST['rombel'] ?? '');
    $nis    = trim($_POST['nis'] ?? '');
    
    // Siapkan parameter dan SQL dinamis
    $params = [];
    $sql = "UPDATE users SET username = ?, nama_lengkap = ?, role = ?";
    $params = [$username, $nama_lengkap, $role];
    
    // Jika role = siswa, tambahkan kolom siswa
    if ($role === 'siswa') {
        $sql .= ", rayon = ?, rombel = ?, nis = ?";
        $params[] = $rayon;
        $params[] = $rombel;
        $params[] = $nis;
    } else {
        // Untuk role selain siswa, kosongkan data siswa (optional, sesuai kebijakan)
        // Agar data lama tidak terbawa, set NULL atau string kosong
        $sql .= ", rayon = NULL, rombel = NULL, nis = NULL";
    }
    
    // Jika password diisi, tambahkan update password
    if (!empty($password)) {
        $sql .= ", password = ?";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $id;
    
    $stmt = Database::getInstance()->getConnection()->prepare($sql);
    if ($stmt->execute($params)) {
        $_SESSION['success'] = "User berhasil diperbarui.";
    } else {
        $_SESSION['error'] = "Gagal memperbarui user.";
    }
    header("Location: manage_users.php");
    exit;
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between">
            <h5 class="mb-0 fw-semibold text-primary"><i class="fas fa-edit me-2"></i>Edit User</h5>
            <a href="manage_users.php" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
        </div>
        <div class="card-body p-4">
            <form method="POST">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control rounded-pill" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control rounded-pill" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" id="roleSelect" class="form-select rounded-pill">
                        <option value="siswa" <?= $user['role'] == 'siswa' ? 'selected' : '' ?>>Siswa</option>
                        <option value="petugas" <?= $user['role'] == 'petugas' ? 'selected' : '' ?>>Petugas</option>
                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div id="siswaFields">
                    <div class="mb-3">
                        <label>NIS</label>
                        <input type="text" name="nis" id="nis" class="form-control rounded-pill" value="<?= htmlspecialchars($user['nis'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label>Rayon</label>
                        <input type="text" name="rayon" id="rayon" class="form-control rounded-pill" value="<?= htmlspecialchars($user['rayon'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label>Rombel</label>
                        <input type="text" name="rombel" id="rombel" class="form-control rounded-pill" value="<?= htmlspecialchars($user['rombel'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label>Password Baru (kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" class="form-control rounded-pill" placeholder="Masukkan password baru">
                </div>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
                <a href="manage_users.php" class="btn btn-secondary rounded-pill px-4">Batal</a>
            </form>
        </div>
    </div>
</div>
<script>
    const roleSelect = document.getElementById('roleSelect');
    const siswaFields = document.getElementById('siswaFields');
    const nisInput = document.getElementById('nis');
    const rayonInput = document.getElementById('rayon');
    const rombelInput = document.getElementById('rombel');

    function toggleSiswaFields() {
        const isSiswa = roleSelect.value === 'siswa';
        // Tampilkan atau sembunyikan div
        siswaFields.style.display = isSiswa ? 'block' : 'none';
        // Disable atau enable input agar tidak ikut submit
        nisInput.disabled = !isSiswa;
        rayonInput.disabled = !isSiswa;
        rombelInput.disabled = !isSiswa;
    }

    // Jalankan saat halaman dimuat
    toggleSiswaFields();
    // Jalankan setiap role berubah
    roleSelect.addEventListener('change', toggleSiswaFields);
</script>
<?php include '../includes/footer.php'; ?>