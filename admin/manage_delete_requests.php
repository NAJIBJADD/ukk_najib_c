<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'admin') die;
$dr = new DeleteRequest();
if (isset($_GET['approve'])) {
    $dr->approveRequest($_GET['approve']);
    header("Location: manage_delete_requests.php");
} elseif (isset($_GET['reject'])) {
    $dr->rejectRequest($_GET['reject']);
    header("Location: manage_delete_requests.php");
}
$requests = $dr->getAllRequests();
?>
... tampilkan tabel dengan tombol setujui/tolak ...