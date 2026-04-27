<?php
require_once 'includes/autoload.php';
session_destroy();
header("Location: login.php");
exit;