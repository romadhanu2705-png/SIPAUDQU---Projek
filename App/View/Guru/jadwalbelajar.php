<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../../../App/Config/Database.php';

$pdo = \App\Config\Database::connect();

$current_date = $_GET['bulan'] ?? date('Y-m'); 
$current_tema = $_GET['tema'] ?? 'Binatang';

$query = "
    SELECT id_jadwal, tanggal, hari, halaman, kegiatan, deskripsi, tema
    FROM jadwal_belajar
    WHERE DATE_FORMAT(tanggal, '%Y-%m') = :bulan
      AND tema = :tema
    ORDER BY tanggal ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute(['bulan' => $current_date, 'tema' => $current_tema]);
$schedules = $stmt->fetchAll();

$stmt_tema = $pdo->query("SELECT DISTINCT tema FROM jadwal_belajar");
$themes = $stmt_tema->fetchAll(PDO::FETCH_COLUMN);
if(empty($themes)) $themes = ['Binatang', 'Diri Sendiri', 'Keluargaku', 'Lingkungan', 'Alam Semesta'];
if(!in_array($current_tema, $themes)) $themes[] = $current_tema;

function getBadgeClass($kegiatan) {
    $kegiatan = strtolower($kegiatan);
    if (strpos($kegiatan, 'mengenal') !== false) return 'mengenal';
    if (strpos($kegiatan, 'mewarnai') !== false) return 'mewarnai';
    if (strpos($kegiatan, 'menghitung') !== false) return 'menghitung';
    if (strpos($kegiatan, 'motorik') !== false) return 'motorik';
    if (strpos($kegiatan, 'bermain') !== false) return 'bermain';
    return 'default-badge';
}

include __DIR__ . '/../../../App/Layout/header.php';
?>

<style>
    .absensi-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        font-family: 'Nunito', sans-serif;
    }
    
    .page-title {
        color: #2563eb;
        font-size: 1.4rem;
        font-weight: 800;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .filter-section {
        display: flex;
        align-items: flex-end;
        gap: 20px;
        margin-bottom: 30px;
        margin-top: 25px;
    }
    
    .filter-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .filter-label {
        font-size: 0.85rem;
        font-weight: 800;
        color: #0f172a;
        margin-left: 15px;
    }
    
    .filter-input-wrapper {
        border: 1px solid #475569;
        border-radius: 20px;
        padding: 8px 18px;
        display: inline-flex;
        align-items: center;
        background: white;
        height: 40px;
        box-sizing: border-box;
    }
    
    .filter-input-wrapper input[type="month"], .filter-input-wrapper select {
        border: none;
        outline: none;
        font-family: 'Nunito', sans-serif;
        font-weight: 700;
        color: #475569;
        background: transparent;
        font-size: 0.9rem;
    }
    
    .jadwal-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #cbd5e1;
    }
    
    .jadwal-table th {
        background: #e0f2fe;
        color: #0f172a;
        font-weight: 800;
        padding: 15px;
        text-align: center;
        border-bottom: 2px solid #cbd5e1;
    }
    
    .jadwal-table td {
        padding: 15px;
        border-bottom: 1px solid #e2e8f0;
        color: #0f172a;
        font-weight: 700;
        font-size: 0.9rem;
        vertical-align: middle;
        border-right: 1px solid #e2e8f0;
    }