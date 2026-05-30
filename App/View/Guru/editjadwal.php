<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . '/../../../../App/Config/Database.php';

$error = '';
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
    $tema = trim($_POST['tema'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? '');
    $hari = trim($_POST['hari'] ?? '');
    $halaman = trim($_POST['halaman'] ?? '');
    $kegiatan = trim($_POST['kegiatan'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (empty($tema) || empty($tanggal) || empty($kegiatan)) {
        $error = "Tema, Tanggal, dan Kegiatan wajib diisi.";
    } else {
        try {
            if (empty($hari)) {
                $day_index = date('N', strtotime($tanggal));
                $hari_list = [
                    1 => 'Senin',
                    2 => 'Selasa',
                    3 => 'Rabu',
                    4 => 'Kamis',
                    5 => 'Jumat',
                    6 => 'Sabtu',
                    7 => 'Minggu'
                ];
                $hari = $hari_list[$day_index] ?? '';
            }

            $query = "UPDATE jadwal_belajar SET 
                      tema = :tema,
                      tanggal = :tanggal,
                      hari = :hari,
                      halaman = :halaman, 
                      kegiatan = :kegiatan, 
                      deskripsi = :deskripsi
                      WHERE id_jadwal = :id_jadwal";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'tema' => $tema,
                'tanggal' => $tanggal,
                'hari' => $hari,
                'halaman' => $halaman,
                'kegiatan' => $kegiatan,
                'deskripsi' => $deskripsi,
                'id_jadwal' => $id_jadwal
            ]);
            
            $_SESSION['flash_success'] = "Data jadwal berhasil diperbarui.";
            header("Location: ../jadwalbelajar.php");
            exit;
        } catch (\Exception $e) {
            $error = "Gagal memperbarui data: " . $e->getMessage();
        }
    }
}

include '../../../../App/Layout/header.php';
?>
<style>
    body { overflow: hidden; }
    .modal-overlay { 
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        display: flex; align-items: center; justify-content: center; 
        z-index: 9999; 
        background: rgba(255, 255, 255, 0.5); 
        backdrop-filter: blur(4px); 
        -webkit-backdrop-filter: blur(4px);
    }
    .modal-card { background: white; border-radius: 20px; padding: 40px; width: 600px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); position: relative; font-family: 'Nunito', sans-serif;}
    .form-label { display: block; font-weight: 800; font-size: 0.85rem; margin-bottom: 6px; color: #000; }
    .form-input { width: 100%; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; box-sizing: border-box; outline: none; font-family: 'Nunito', sans-serif; font-weight: 600; color: #94a3b8; background: #fff;}
    .form-input:focus { border-color: #3b82f6; color: #334155; }
</style>
