<?php
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Wali murid/login.php');
    exit;
}

require_once __DIR__ . '/../../Config/Database.php';
$pdo = \App\Config\Database::connect();

// 1. Create info_kegiatan table if it doesn't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS info_kegiatan (
    id_kegiatan INT AUTO_INCREMENT PRIMARY KEY,
    isi_kegiatan TEXT NOT NULL,
    tanggal_kegiatan TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Seed default info_kegiatan if empty
$stmt_check = $pdo->query("SELECT COUNT(*) FROM info_kegiatan");
if ($stmt_check->fetchColumn() == 0) {
    $default_kegiatan = [
        'Pengumuman Liburan Nasional 17 Agustus 2026',
        'Rapat wali murid siswa Sabtu, 30 Agustus 2026',
        'Pelatihan perkembangan child bulan April nanti pekan ini'
    ];
    $stmt_insert = $pdo->prepare("INSERT INTO info_kegiatan (isi_kegiatan) VALUES (?)");
    foreach ($default_kegiatan as $keg) {
        $stmt_insert->execute([$keg]);
    }
}

// Handle Add Info Kegiatan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah_kegiatan') {
    $isi = trim($_POST['isi_kegiatan'] ?? '');
    if ($isi !== '') {
        $stmt = $pdo->prepare("INSERT INTO info_kegiatan (isi_kegiatan) VALUES (?)");
        $stmt->execute([$isi]);
        $_SESSION['flash_success'] = "Info kegiatan berhasil ditambahkan! 🎉";
    }
    header("Location: dashboardguru.php");
    exit;
}

// Handle Delete Info Kegiatan
if (isset($_GET['hapus_kegiatan'])) {
    $id_del = intval($_GET['hapus_kegiatan']);
    $stmt = $pdo->prepare("DELETE FROM info_kegiatan WHERE id_kegiatan = ?");
    $stmt->execute([$id_del]);
    $_SESSION['flash_success'] = "Info kegiatan berhasil dihapus! ✓";
    header("Location: dashboardguru.php");
    exit;
}

// Fetch flash messages
$flash_success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

// 2. Fetch dynamic stats
$jumlah_siswa = $pdo->query("SELECT COUNT(*) FROM murid")->fetchColumn() ?: 0;
$jumlah_guru = $pdo->query("SELECT COUNT(*) FROM guru")->fetchColumn() ?: 0;

// Dynamic Attendance Today
$absensi_hari_ini = $pdo->query("SELECT COUNT(*) FROM absensi WHERE tanggal = CURDATE()")->fetchColumn() ?: 0;
if ($absensi_hari_ini == 0) {
    $last_date = $pdo->query("SELECT MAX(tanggal) FROM absensi")->fetchColumn();
    if ($last_date) {
        $stmt_last = $pdo->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = ?");
        $stmt_last->execute([$last_date]);
        $absensi_hari_ini = $stmt_last->fetchColumn();
    }
}

// Fetch Aktivitas Hari Ini
$aktivitas_hari_ini = $pdo->query("SELECT COUNT(*) FROM aktivitas WHERE tanggal = CURDATE()")->fetchColumn() ?: 0;

// 3. Fetch Info Kegiatan
$stmt_kegiatan = $pdo->query("SELECT * FROM info_kegiatan ORDER BY id_kegiatan DESC");
$info_kegiatan_list = $stmt_kegiatan->fetchAll();
2
// 4. Fetch Aktivitas Terbaru
$stmt_aktivitas = $pdo->query("
    SELECT a.*, m.nama_siswa 
    FROM aktivitas a 
    LEFT JOIN murid m ON a.id_siswa = m.id_siswa 
    ORDER BY a.id_aktivitas DESC 
    LIMIT 5
");
$aktivitas_terbaru_list = $stmt_aktivitas->fetchAll();

// Get user data
$user_name = $_SESSION['username'] ?? 'Halimatus';
$user_role = $_SESSION['user_role'] ?? 'Admin';

// Include layout
include '../../../App/Layout/header.php';
?>

<style>
    /* Modal Styles */
    .dok-modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none; align-items: center; justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    }
    .dok-modal-content {
        background: white;
        padding: 20px;
        border-radius: 16px;
        position: relative;
        max-width: 90%;
        max-height: 90%;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    .dok-modal-content img {
        max-width: 100%;
        max-height: 70vh;
        border-radius: 8px;
        display: block;
    }
    .dok-close-btn {
        position: absolute;
        top: -15px;
        right: -15px;
        width: 35px;
        height: 35px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
        transition: transform 0.2s;
    }

    