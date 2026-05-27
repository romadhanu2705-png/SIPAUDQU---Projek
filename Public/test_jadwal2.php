<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'C:/xampp/htdocs/SIPAUDQU/App/Config/Database.php';
$pdo = \App\Config\Database::connect();

// Check table structure
echo "=== KOLOM jadwal_belajar ===\n";
$stmt = $pdo->query('DESCRIBE jadwal_belajar');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) {
    echo $c['Field'] . ' | ' . $c['Type'] . ' | Null:' . $c['Null'] . "\n";
}

// Try the actual query
echo "\n=== TEST QUERY ===\n";
$bulan = date('Y-m');
$tema = 'Tema';
$query = "
    SELECT id_jadwal, tanggal, hari, halaman, kegiatan, deskripsi, tema
    FROM jadwal_belajar
    WHERE DATE_FORMAT(tanggal, '%Y-%m') = :bulan
      AND tema = :tema
    ORDER BY tanggal ASC
";
$stmt2 = $pdo->prepare($query);
$stmt2->execute(['bulan' => $bulan, 'tema' => $tema]);
$rows = $stmt2->fetchAll();
echo "Rows ditemukan: " . count($rows) . "\n";
if(count($rows) > 0) {
    print_r($rows[0]);
}
