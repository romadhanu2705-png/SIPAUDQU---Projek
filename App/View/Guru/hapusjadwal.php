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
$date_parts = explode('-', $jadwal['tanggal']);
$display_date_text = (int)$date_parts[2] . ' ' . $months[(int)$date_parts[1]] . ' ' . $date_parts[0];
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
    .modal-card { background: white; border-radius: 20px; padding: 40px; width: 450px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.15); position: relative; font-family: 'Nunito', sans-serif; }
    .btn { padding: 8px 30px; border-radius: 8px; font-weight: 800; font-size: 0.95rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; border: none; font-family: 'Nunito', sans-serif; }
    .btn-cancel { border: 2px solid #cbd5e1; background: white; color: #475569; }
    .btn-delete { background: #e60000; color: white; }
</style>

<div class="page-wrapper">
    <div class="layout-container">
        <?php include '../../../../App/Layout/sidebar.php'; ?>
        <div class="main-content">
            <div class="content-card">
                <h3 style="font-size: 1rem; font-weight: 800; color: #1e293b; margin-bottom: 15px;">🗒️ Jadwal Kegiatan Pembelajaran (Berbasis Tema)</h3>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay">
    <div class="modal-card">
        <div style="margin-bottom: 10px;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
        </div>
        <h2 style="margin-top: 0; color: #0047FF; font-size: 1.5rem; font-weight: 800; margin-bottom: 15px;">
            Hapus Jadwal
        </h2>
        <p style="color: #64748b; margin-bottom: 20px; font-weight: 600; font-size: 0.9rem; line-height: 1.5; padding: 0 20px;">
            Apakah anda yakin ingin menghapus jadwal kegiatan pembelajaran tanggal <?php echo $display_date_text; ?>?
        </p>
        
        <div style="color: #0047FF; font-weight: 800; font-size: 0.95rem; margin-bottom: 5px;">
            Kegiatan : <?php echo htmlspecialchars($jadwal['kegiatan']); ?>
        </div>
        <div style="color: #0047FF; font-weight: 800; font-size: 0.95rem; margin-bottom: 30px;">
            Tema : <?php echo htmlspecialchars($jadwal['tema']); ?>
        </div>
        
        <form method="POST" style="display: flex; justify-content: center; gap: 15px;">
            <a href="../jadwalbelajar.php" class="btn btn-cancel">Batal</a>
            <button type="submit" class="btn btn-delete">Hapus</button>
        </form>
    </div>
</div>

<?php include '../../../../App/Layout/footer.php'; ?>
