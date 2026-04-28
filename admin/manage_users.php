<?php
require_once '../includes/autoload.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$userObj = new User();
$logObj = new Log();

// Fungsi validasi username (tanpa spasi)
function validateUsername($username) {
    // Tidak boleh kosong, tidak boleh ada spasi, hanya huruf/angka/underscore/titik
    return preg_match('/^[a-zA-Z0-9_.]+$/', $username);
}

// --- PROSES TAMBAH ---
if (isset($_POST['add'])) {
    $username = trim($_POST['username']);
    
    if (!validateUsername($username)) {
        $_SESSION['error'] = "Username tidak boleh mengandung spasi! Gunakan huruf, angka, underscore(_), atau titik(.) saja.";
        header("Location: manage_users.php");
        exit;
    }
    
    $data = [
        'username' => $username,
        'password' => $_POST['password'],
        'nama_lengkap' => trim($_POST['nama_lengkap']),
        'role' => $_POST['role'],
        'rayon' => $_POST['role'] == 'siswa' ? trim($_POST['rayon'] ?? '') : '',
        'rombel' => $_POST['role'] == 'siswa' ? trim($_POST['rombel'] ?? '') : '',
        'nis' => $_POST['role'] == 'siswa' ? trim($_POST['nis'] ?? '') : ''
    ];
    
    if ($userObj->createUser($data)) {
        $logObj->add($_SESSION['user_id'], 'Tambah User', "User {$data['username']} ditambahkan");
        $_SESSION['success'] = "User berhasil ditambahkan.";
    } else {
        $_SESSION['error'] = "Gagal menambahkan user. Mungkin username sudah terdaftar.";
    }
    header("Location: manage_users.php");
    exit;
}

// --- PROSES EDIT ---
if (isset($_POST['edit'])) {
    $username = trim($_POST['username']);
    
    if (!validateUsername($username)) {
        $_SESSION['error'] = "Username tidak boleh mengandung spasi! Gunakan huruf, angka, underscore(_), atau titik(.) saja.";
        header("Location: manage_users.php");
        exit;
    }
    
    $data = [
        'id' => (int)$_POST['id'],
        'username' => $username,
        'nama_lengkap' => trim($_POST['nama_lengkap']),
        'role' => $_POST['role'],
        'password' => $_POST['password'],
        'rayon' => $_POST['role'] == 'siswa' ? trim($_POST['rayon'] ?? '') : '',
        'rombel' => $_POST['role'] == 'siswa' ? trim($_POST['rombel'] ?? '') : '',
        'nis' => $_POST['role'] == 'siswa' ? trim($_POST['nis'] ?? '') : ''
    ];

    if ($userObj->updateUser($data)) {
        $logObj->add($_SESSION['user_id'], 'Edit User', "User ID {$data['id']} diperbarui");
        $_SESSION['success'] = "User berhasil diperbarui.";
    } else {
        $_SESSION['error'] = "Gagal memperbarui user.";
    }
    header("Location: manage_users.php");
    exit;
}

// --- PROSES HAPUS ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($userObj->deleteUser($id, $_SESSION['user_id'])) {
        $logObj->add($_SESSION['user_id'], 'Hapus User', "ID $id dihapus");
        $_SESSION['success'] = "User berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus user (Anda tidak bisa menghapus akun sendiri).";
    }
    header("Location: manage_users.php");
    exit;
}

$users = $userObj->getAllUsers();
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4">
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <h4>Manajemen Pengguna</h4>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">Tambah User</button>
        </div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Username</th><th>Nama</th><th>Role</th><th>Detail</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                        <td><?= $user['role'] ?></td>
                        <td><small><?= htmlspecialchars($user['nis'] ?? '-') ?> | <?= htmlspecialchars($user['rayon'] ?? '-') ?> | <?= htmlspecialchars($user['rombel'] ?? '-') ?></small></td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-btn" 
                                data-bs-toggle="modal" data-bs-target="#editModal"
                                data-id="<?= $user['id'] ?>"
                                data-username="<?= $user['username'] ?>"
                                data-nama="<?= $user['nama_lengkap'] ?>"
                                data-role="<?= $user['role'] ?>"
                                data-nis="<?= $user['nis'] ?>"
                                data-rayon="<?= $user['rayon'] ?>"
                                data-rombel="<?= $user['rombel'] ?>">Edit</button>
                            
                            <?php if($user['id'] != $_SESSION['user_id']): ?>
                                <a href="?delete=<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus?')">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH USER -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content" onsubmit="return validateUsernameForm('add')">
            <div class="modal-header">
                <h5 class="modal-title">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label>Username <span class="text-danger">*</span> <small class="text-muted">(tanpa spasi)</small></label>
                    <input type="text" name="username" id="add_username" class="form-control" 
                           pattern="[a-zA-Z0-9_.]+" title="Hanya huruf, angka, underscore(_), dan titik(.) - tanpa spasi" required>
                    <div class="invalid-feedback">Username tidak boleh mengandung spasi.</div>
                </div>
                <div class="mb-2">
                    <label>Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Role</label>
                    <select name="role" id="add_role" class="form-control" required>
                        <option value="admin">Admin</option>
                        <option value="petugas">Petugas</option>
                        <option value="siswa">Siswa</option>
                    </select>
                </div>
                <div id="add_siswa_only" style="display: none;">
                    <input type="text" name="nis" class="form-control mb-2" placeholder="NIS">
                    <input type="text" name="rayon" class="form-control mb-2" placeholder="Rayon">
                    <input type="text" name="rombel" class="form-control mb-2" placeholder="Rombel">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="add" class="btn btn-primary">Tambah User</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT USER -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content" onsubmit="return validateUsernameForm('edit')">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-2">
                    <label>Username <span class="text-danger">*</span> <small class="text-muted">(tanpa spasi)</small></label>
                    <input type="text" name="username" id="edit_username" class="form-control" 
                           pattern="[a-zA-Z0-9_.]+" title="Hanya huruf, angka, underscore(_), dan titik(.) - tanpa spasi" required>
                </div>
                <div class="mb-2">
                    <label>Password (Kosongkan jika tidak diganti)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="edit_nama" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Role</label>
                    <select name="role" id="edit_role" class="form-control">
                        <option value="admin">Admin</option>
                        <option value="petugas">Petugas</option>
                        <option value="siswa">Siswa</option>
                    </select>
                </div>
                <div id="edit_siswa_only" style="display: none;">
                    <input type="text" name="nis" id="edit_nis" class="form-control mb-2" placeholder="NIS">
                    <input type="text" name="rayon" id="edit_rayon" class="form-control mb-2" placeholder="Rayon">
                    <input type="text" name="rombel" id="edit_rombel" class="form-control mb-2" placeholder="Rombel">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="edit" class="btn btn-warning">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
// Fungsi validasi client-side untuk username (tanpa spasi)
function validateUsernameForm(mode) {
    let usernameField;
    if (mode === 'add') {
        usernameField = document.getElementById('add_username');
    } else {
        usernameField = document.getElementById('edit_username');
    }
    const username = usernameField.value.trim();
    // Cek apakah mengandung spasi
    if (/\s/.test(username)) {
        alert('Username tidak boleh mengandung spasi!');
        usernameField.focus();
        return false;
    }
    // Cek pattern (opsional, sesuai HTML pattern)
    if (!/^[a-zA-Z0-9_.]+$/.test(username)) {
        alert('Username hanya boleh berisi huruf, angka, underscore(_), atau titik(.)');
        usernameField.focus();
        return false;
    }
    return true;
}

// Fungsi toggle field siswa pada modal tambah
function toggleAddSiswaFields() {
    const role = document.getElementById('add_role').value;
    const siswaDiv = document.getElementById('add_siswa_only');
    siswaDiv.style.display = role === 'siswa' ? 'block' : 'none';
}

function toggleEditSiswaFields() {
    const role = document.getElementById('edit_role').value;
    const siswaDiv = document.getElementById('edit_siswa_only');
    siswaDiv.style.display = role === 'siswa' ? 'block' : 'none';
}

// Event listeners
document.getElementById('add_role').addEventListener('change', toggleAddSiswaFields);
document.getElementById('addModal').addEventListener('show.bs.modal', function() {
    toggleAddSiswaFields();
    // Reset form jika diperlukan
    document.getElementById('add_username').value = '';
});

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit_id').value = this.dataset.id;
        document.getElementById('edit_username').value = this.dataset.username;
        document.getElementById('edit_nama').value = this.dataset.nama;
        const roleSelect = document.getElementById('edit_role');
        roleSelect.value = this.dataset.role;
        document.getElementById('edit_nis').value = this.dataset.nis || '';
        document.getElementById('edit_rayon').value = this.dataset.rayon || '';
        document.getElementById('edit_rombel').value = this.dataset.rombel || '';
        toggleEditSiswaFields();
    });
});

document.getElementById('edit_role').addEventListener('change', toggleEditSiswaFields);
</script>

<?php include '../includes/footer.php'; ?>