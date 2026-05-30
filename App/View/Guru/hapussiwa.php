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
.modal-card { background: white; border-radius: 24px; padding: 40px; width: 450px; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.1); position: relative; font-family: 'Nunito', sans-serif; }
    .btn { padding: 10px 30px; border-radius: 12px; font-weight: 800; font-size: 0.95rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; border: none; font-family: 'Nunito', sans-serif; }
    .btn-cancel { border: 1px solid #c7d2fe; background: white; color: #475569; }
    .btn-delete { background: #e60000; color: white; box-shadow: 0 4px 10px rgba(230,0,0,0.3); }
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
                            <tr style="background: #f8fafc;">
                                <th style="padding: 12px; border-bottom: 1px solid #e2e8f0;">No</th>
                                <th style="padding: 12px; border-bottom: 1px solid #e2e8f0;">Nama</th>
                                <th style="padding: 12px; border-bottom: 1px solid #e2e8f0;">Kelas</th>
                                <th style="padding: 12px; border-bottom: 1px solid #e2e8f0;">NIS</th>
                                <th style="padding: 12px; border-bottom: 1px solid #e2e8f0;">Tanggal Lahir</th>
                                <th style="padding: 12px; border-bottom: 1px solid #e2e8f0;">Jenis Kelamin</th>
                                <th style="padding: 12px; border-bottom: 1px solid #e2e8f0;">Alamat</th>
                                <th style="padding: 12px; border-bottom: 1px solid #e2e8f0;">Orang Tua</th>
                                <th style="padding: 12px; border-bottom: 1px solid #e2e8f0;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i=0; $i<5; $i++): ?>
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">-</td>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">-</td>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">-</td>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">-</td>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">-</td>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">-</td>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">-</td>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">-</td>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">-</td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay">
    <div class="modal-card">
        <div style="font-size: 3rem; margin-bottom: 10px;">🗑️</div>
        <h2 style="margin-top: 0; color: #0047FF; font-size: 1.5rem; font-weight: 800; margin-bottom: 15px;">
            Hapus Siswa?
        </h2>
        <p style="color: #64748b; margin-bottom: 30px; font-weight: 600; font-size: 0.95rem;">
            Data Siswa <?php echo htmlspecialchars($student['nama_siswa']); ?> akan dihapus permanen!
        </p>
        
        <form method="POST" style="display: flex; justify-content: center; gap: 15px;">
            <a href="../datasiswa.php" class="btn btn-cancel">Batal</a>
            <button type="submit" class="btn btn-delete">Hapus</button>
        </form>
    </div>
</div>

<?php include '../../../../App/Layout/footer.php'; ?>
