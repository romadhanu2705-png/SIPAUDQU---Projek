<?php
require 'App/Config/Database.php';
$pdo = \App\Config\Database::connect();
echo "DESCRIBE jadwal_belajar:\n";
try {
    foreach($pdo->query("DESCRIBE jadwal_belajar")->fetchAll() as $c) echo $c['Field'].' | '.$c['Type']."\n";
    echo "\nSample:\n";
    foreach($pdo->query("SELECT * FROM jadwal_belajar LIMIT 1")->fetchAll() as $r) print_r($r);
} catch (Exception $e) { echo $e->getMessage(); }


