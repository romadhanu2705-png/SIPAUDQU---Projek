<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . '/../../../../App/Config/Database.php';

$id_guru = $_GET['id'] ?? null;

if (!$id_guru) {
    $_SESSION['flash_error'] = "ID Guru tidak valid.";
    header("Location: ../dataguru.php");
    exit;
}

try {
    $pdo = \App\Config\Database::connect();

    // Ambil data guru
    $stmt = $pdo->prepare("SELECT g.id_guru, g.id_user, g.nama_guru FROM guru g WHERE g.id_guru = :id");
    $stmt->execute(['id' => $id_guru]);
    $teacher = $stmt->fetch();

    if (!$teacher) {
        $_SESSION['flash_error'] = "Data guru tidak ditemukan.";
        header("Location: ../dataguru.php");
        exit;
    }
} catch (\Exception $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Hapus data guru terlebih dahulu
        $stmt_g = $pdo->prepare("DELETE FROM guru WHERE id_guru = :id_guru");
        $stmt_g->execute(['id_guru' => $id_guru]);

        // Hapus data pengguna terkait
        if ($teacher['id_user']) {
            $stmt_p = $pdo->prepare("DELETE FROM pengguna WHERE id_user = :id_user");
            $stmt_p->execute(['id_user' => $teacher['id_user']]);
        }

        $_SESSION['flash_success'] = "Data guru " . htmlspecialchars($teacher['nama_guru']) . " berhasil dihapus.";
    } catch (\Exception $e) {
        $_SESSION['flash_error'] = "Gagal menghapus data: " . $e->getMessage();
    }
    header("Location: ../dataguru.php");
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
                <h3 style="font-size: 1rem; font-weight: 800; color: #1e293b; margin-bottom: 15px;">👩‍🏫 Data Guru</h3>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay">
    <div class="modal-card">
        <div style="font-size: 3rem; margin-bottom: 10px;">🗑️</div>
        <h2 style="margin-top: 0; color: #0047FF; font-size: 1.5rem; font-weight: 800; margin-bottom: 15px;">
            Hapus Guru?
        </h2>
        <p style="color: #64748b; margin-bottom: 30px; font-weight: 600; font-size: 0.95rem;">
            Data Guru <strong><?php echo htmlspecialchars($teacher['nama_guru']); ?></strong> akan dihapus permanen!<br>
            <span style="color: #ef4444; font-size: 0.85rem;">Termasuk akun login guru ini.</span>
        </p>

        <form method="POST" style="display: flex; justify-content: center; gap: 15px;">
            <a href="../dataguru.php" class="btn btn-cancel">Batal</a>
            <button type="submit" class="btn btn-delete">Hapus</button>
        </form>
    </div>
</div>

<?php include '../../../../App/Layout/footer.php'; ?>
