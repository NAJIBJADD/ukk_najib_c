<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'petugas') {
    header("Location: ../login.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loan_id'])) {
    $loanId = (int)$_POST['loan_id'];
    $alasan = trim($_POST['alasan']);
    $deleteReq = new DeleteRequest();
    if ($deleteReq->createRequest($loanId, $_SESSION['user_id'], $alasan)) {
        $_SESSION['success'] = "Permintaan penghapusan data peminjaman telah dikirim ke admin.";
    } else {
        $_SESSION['error'] = "Gagal mengirim permintaan.";
    }
    header("Location: all_loans.php");
    exit;
}
header("Location: all_loans.php");
exit;
?>