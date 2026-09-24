<?php
require_once __DIR__ . '/../includes/data.php';

$_SESSION['user'] = null;
$_SESSION['flash_msg'] = 'Sesi petugas telah berakhir. Anda berhasil keluar.';
header('Location: login.php');
exit;
