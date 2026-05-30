<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . '/../../../../App/Config/Database.php';

$error = '';
$success = '';

// Fetch class list for dropdown
try {
    $pdo = \App\Config\Database::connect();
    $stmt_kelas = $pdo->query("SELECT id_kelas, nama_kelas FROM kelas ORDER BY nama_kelas ASC");
    $kelas_list = $stmt_kelas->fetchAll();
} catch (\Exception $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama_siswa'] ?? '';
    $nis = $_POST['nis'] ?? '';
    $id_kelas = $_POST['id_kelas'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $nama_wali = $_POST['nama_wali'] ?? '';
    
    $id_user = $_SESSION['user_id'] ?? NULL;

    if (empty($nama) || empty($jenis_kelamin)) {
        $error = "Nama dan Jenis Kelamin wajib diisi.";
    } else {
        try {
            $pdo->beginTransaction();
            
            $id_wali = NULL;
            if (!empty($nama_wali)) {
                $stmt_wali = $pdo->prepare("SELECT id_wali FROM wali_murid WHERE nama_wali = :nama LIMIT 1");
                $stmt_wali->execute(['nama' => $nama_wali]);
                $wali = $stmt_wali->fetch();
                if ($wali) {
                    $id_wali = $wali['id_wali'];
                } else {
                    $stmt_ins_wali = $pdo->prepare("INSERT INTO wali_murid (nama_wali) VALUES (:nama)");
                    $stmt_ins_wali->execute(['nama' => $nama_wali]);
                    $id_wali = $pdo->lastInsertId();
                }
            }

            $query = "INSERT INTO murid (id_user, id_kelas, id_wali, nama_siswa, nis, jenis_kelamin, tanggal_lahir, alamat) 
                      VALUES (:id_user, :id_kelas, :id_wali, :nama_siswa, :nis, :jenis_kelamin, :tanggal_lahir, :alamat)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'id_user' => $id_user,
                'id_kelas' => empty($id_kelas) ? NULL : $id_kelas,
                'id_wali' => $id_wali,
                'nama_siswa' => $nama,
                'nis' => empty($nis) ? NULL : $nis,
                'jenis_kelamin' => $jenis_kelamin,
                'tanggal_lahir' => empty($tanggal_lahir) ? NULL : $tanggal_lahir,
                'alamat' => empty($alamat) ? NULL : $alamat
            ]);
            
            $pdo->commit();
            $_SESSION['flash_success'] = "Data siswa berhasil ditambahkan.";
            header("Location: ../datasiswa.php");
            exit;
        } catch (\Exception $e) {
            $pdo->rollBack();
            $error = "Gagal menyimpan data: " . $e->getMessage();
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
        background: rgba(255, 255, 255, 0.4); 
        backdrop-filter: blur(8px); 
        -webkit-backdrop-filter: blur(8px);
    }
    .modal-card { background: white; border-radius: 24px; padding: 40px; width: 650px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); position: relative; }
    .form-label { display: block; font-weight: 800; font-size: 0.9rem; margin-bottom: 8px; color: #000; font-family: 'Nunito', sans-serif; }
    .form-input { width: 100%; padding: 12px; border: 1px solid #c7d2fe; border-radius: 12px; font-size: 0.9rem; box-sizing: border-box; outline: none; font-family: inherit; }
    .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .form-input::placeholder { color: #94a3b8; font-weight: 600; }
    .form-select { width: 100%; padding: 12px; border: 1px solid #c7d2fe; border-radius: 12px; font-size: 0.9rem; box-sizing: border-box; outline: none; appearance: none; background: url('data:image/svg+xml;utf8,<svg fill="%2394a3b8" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 10px center; background-color: white; color: #64748b; font-family: inherit; font-weight: 600; }
    .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
</style>

<div class="page-wrapper">
    <div class="layout-container">
        <?php include '../../../../App/Layout/sidebar.php'; ?>
        <div class="main-content">
            <div class="content-card">
                <h3 style="font-size: 1rem; font-weight: 800; color: #1e293b; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                    😊 Data Siswa
                </h3>
                <div style="margin-bottom: 15px;">
                    <form style="display: flex; gap: 8px;">
                        <input type="text" placeholder="Cari Data Siswa..." style="flex: 1; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.85rem; font-family: 'Nunito', sans-serif;">
                    </form>
                </div>
                <div style="margin-bottom: 15px; display: flex; justify-content: flex-end;">
                    <div style="background-color: #3b82f6; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; font-family: 'Nunito', sans-serif;">+ Tambah Siswa</div>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                        <thead>
