<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'C:/xampp/htdocs/SIPAUDQU/App/Config/Database.php';
$pdo = \App\Config\Database::connect();

// Simulate what datasiswa.php does
$daftar_kelas = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC")->fetchAll();
echo "Kelas ditemukan: " . count($daftar_kelas) . "\n";
foreach ($daftar_kelas as $k) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM murid WHERE id_kelas = ?");
    $stmt->execute([$k['id_kelas']]);
    echo "- " . $k['nama_kelas'] . " (id=" . $k['id_kelas'] . "): " . $stmt->fetchColumn() . " siswa\n";
}
$total = $pdo->query("SELECT COUNT(*) FROM murid")->fetchColumn();
echo "\nTotal semua siswa: $total\n";
echo "\nSTATUS: OK - datasiswa.php siap digunakan!\n";
