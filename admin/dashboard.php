<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }

$itemObj = new Item();
$loanObj = new Loan();
$userObj = new User();

$total_items = count($itemObj->getAllItems());
$total_loans = count($loanObj->getAllLoans());
$total_users = count($userObj->getAllUsers());

// Hitung peminjaman terlambat
$loans = $loanObj->getAllLoans();
$late_loans = 0;
foreach ($loans as $loan) {
    if ($loan['status'] == 'dipinjam' && strtotime($loan['batas_waktu']) < time()) {
        $late_loans++;
    }
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <h2 class="mb-4">Dashboard Admin </h2>
    <div class="row">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Barang</h5>
                    <h2><?= $total_items ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Peminjaman</h5>
                    <h2><?= $total_loans ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Pengguna</h5>
                    <h2><?= $total_users ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Terlambat</h5>
                    <h2><?= $late_loans ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>