<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . '/../../../../App/Config/Database.php';

$id_siswa = $_GET['id'] ?? null;

if (!$id_siswa) {
    $_SESSION['flash_error'] = "ID Siswa tidak valid.";
    header("Location: ../datasiswa.php");
    exit;
}

try {
    $pdo = \App\Config\Database::connect();
    
    // Fetch student data
    $stmt = $pdo->prepare("SELECT nama_siswa FROM murid WHERE id_siswa = :id");
    $stmt->execute(['id' => $id_siswa]);
    $student = $stmt->fetch();
    
    if (!$student) {
        $_SESSION['flash_error'] = "Data siswa tidak ditemukan.";
        header("Location: ../datasiswa.php");
        exit;
    }
} catch (\Exception $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("DELETE FROM murid WHERE id_siswa = :id");
        $stmt->execute(['id' => $id_siswa]);
        
        $_SESSION['flash_success'] = "Data siswa berhasil dihapus.";
    } catch (\Exception $e) {
        $_SESSION['flash_error'] = "Gagal menghapus data: " . $e->getMessage();
    }
    header("Location: ../datasiswa.php");
    exit;
}

include '../../../../App/Layout/header.php';
?>
<style>
    body { overflow: hidden; }
    .modal-overlay { 
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        display: flex; align-items: center; justify-content: center; 
        z-index: 9999; 
        background: rgba(255, 255, 255, 0.4); 
        backdrop-filter: blur(8px); 
        -webkit-backdrop-filter: blur(8px);
    }
