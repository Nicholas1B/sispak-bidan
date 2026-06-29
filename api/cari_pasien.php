<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

header('Content-Type: application/json; charset=utf-8');

$q = sanitize($conn, $_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$res = $conn->query("
    SELECT id, nama_pasien, usia, usia_kehamilan, no_hp, alamat
    FROM pasien
    WHERE nama_pasien LIKE '%$q%'
    ORDER BY nama_pasien
    LIMIT 8
");

$out = [];
while ($r = $res->fetch_assoc()) {
    $out[] = $r;
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
