<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . '/../../../../App/Config/Database.php';

$id_jadwal = $_GET['id'] ?? null;

if (!$id_jadwal) {
    $_SESSION['flash_error'] = "ID Jadwal tidak valid.";
    header("Location: ../jadwalbelajar.php");
    exit;
}

try {
    $pdo = \App\Config\Database::connect();
    
    $stmt = $pdo->prepare("SELECT * FROM jadwal_belajar WHERE id_jadwal = :id");
    $stmt->execute(['id' => $id_jadwal]);
    $jadwal = $stmt->fetch();
    
    if (!$jadwal) {
        $_SESSION['flash_error'] = "Data jadwal tidak ditemukan.";
        header("Location: ../jadwalbelajar.php");
        exit;
    }
} catch (\Exception $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("DELETE FROM jadwal_belajar WHERE id_jadwal = :id");
        $stmt->execute(['id' => $id_jadwal]);
        
        $_SESSION['flash_success'] = "Data jadwal berhasil dihapus.";
    } catch (\Exception $e) {
        $_SESSION['flash_error'] = "Gagal menghapus data: " . $e->getMessage();
    }
    header("Location: ../jadwalbelajar.php");
    exit;
}

include '../../../../App/Layout/header.php';

// Format Tanggal
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
