<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$userObj = new User();
$logObj = new Log();

// Tambah user
if (isset($_POST['add'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $role = $_POST['role'];
    
    // Data dasar
    $data = [
        'username' => $username,
        'password' => $password,
        'nama_lengkap' => $nama_lengkap,
        'role' => $role,
        'rayon' => '',
        'rombel' => '',
        'nis' => ''
    ];
    
    // Jika role siswa, ambil data tambahan
    if ($role == 'siswa') {
        $data['rayon'] = trim($_POST['rayon'] ?? '');
        $data['rombel'] = trim($_POST['rombel'] ?? '');
        $data['nis'] = trim($_POST['nis'] ?? '');
    }
    
    if ($userObj->createUser($data)) {
        $logObj->add($_SESSION['user_id'], 'Tambah User', "User {$data['username']} ditambahkan");
        $_SESSION['success'] = "User berhasil ditambahkan.";
    } else {
        $_SESSION['error'] = "Gagal menambahkan user. Username mungkin sudah ada.";
    }
    header("Location: manage_users.php");
    exit;
}

// Hapus user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id === $_SESSION['user_id']) {
        $_SESSION['error'] = "Tidak bisa menghapus akun sendiri.";
    } elseif ($userObj->deleteUser($id, $_SESSION['user_id'])) {
        $logObj->add($_SESSION['user_id'], 'Hapus User', "User ID $id dihapus");
        $_SESSION['success'] = "User berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus user.";
    }
    header("Location: manage_users.php");
    exit;
}

$users = $userObj->getAllUsers();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <h4><i class="fas fa-users"></i> Manajemen Pengguna</h4>
            <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Tambah User
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Username</th><th>Nama Lengkap</th><th>Role</th><th>NIS</th><th>Rayon</th><th>Rombel</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                            <td><?= $user['role'] ?></td>
                            <td><?= htmlspecialchars($user['nis'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($user['rayon'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($user['rombel'] ?? '-') ?></td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus user <?= htmlspecialchars($user['username'])?>?')">Hapus</a>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Akun sendiri</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah User dengan field dinamis -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label>Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Role <span class="text-danger">*</span></label>
                        <select name="role" id="roleSelect" class="form-control" required>
                            <option value="siswa">Siswa</option>
                            <option value="petugas">Petugas</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <!-- Field khusus siswa (ditampilkan jika role siswa) -->
                    <div id="siswaFields" style="display: none;">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label>NIS</label>
                                <input type="text" name="nis" class="form-control" placeholder="Nomor Induk Siswa">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label>Rayon</label>
                                <input type="text" name="rayon" class="form-control" placeholder="Contoh: Cicurug">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label>Rombel</label>
                                <input type="text" name="rombel" class="form-control" placeholder="Contoh: RPL 1">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const roleSelect = document.getElementById('roleSelect');
    const siswaFields = document.getElementById('siswaFields');
    function toggleSiswaFields() {
        if (roleSelect.value === 'siswa') {
            siswaFields.style.display = 'block';
        } else {
            siswaFields.style.display = 'none';
        }
    }
    roleSelect.addEventListener('change', toggleSiswaFields);
    toggleSiswaFields();
</script>
<?php include '../includes/footer.php'; ?>