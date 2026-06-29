<?php
// Redirect to main index
session_start();
require_once '../koneksi.php';
requireAdmin();
redirect('/sispak_bidan/index.php');
?>
