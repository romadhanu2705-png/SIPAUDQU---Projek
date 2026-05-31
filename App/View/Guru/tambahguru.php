<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . '/../../../../App/Config/Database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_guru = trim($_POST['nama_guru'] ?? '');
    $no_hp     = trim($_POST['no_hp'] ?? '');
    $status    = $_POST['status'] ?? 'Aktif';
    $kata_sandi = trim($_POST['kata_sandi'] ?? '');
    $peran     = $_POST['peran'] ?? 'Guru';

    if (empty($nama_guru)) {
        $error = "Nama Guru wajib diisi.";
    } elseif (empty($kata_sandi)) {
        $error = "Kata Sandi wajib diisi.";
    } else {
        try {
            $pdo = \App\Config\Database::connect();
            $pdo->beginTransaction();

            // Insert ke tabel pengguna
            $stmt_p = $pdo->prepare("INSERT INTO pengguna (nama, kata_sandi, peran) VALUES (:nama, :kata_sandi, :peran)");
            $stmt_p->execute([
                'nama'       => $nama_guru,
                'kata_sandi' => $kata_sandi,
                'peran'      => $peran,
            ]);
            $id_user = $pdo->lastInsertId();

            // Insert ke tabel guru
            $stmt_g = $pdo->prepare("INSERT INTO guru (id_user, nama_guru, no_hp, status) VALUES (:id_user, :nama_guru, :no_hp, :status)");
            $stmt_g->execute([
                'id_user'   => $id_user,
                'nama_guru' => $nama_guru,
                'no_hp'     => empty($no_hp) ? null : $no_hp,
                'status'    => $status,
            ]);

            $pdo->commit();
            $_SESSION['flash_success'] = "Data guru berhasil ditambahkan.";
            header("Location: ../dataguru.php");
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
    .modal-card { background: white; border-radius: 24px; padding: 40px; width: 600px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); position: relative; }
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
                <h3 style="font-size: 1rem; font-weight: 800; color: #1e293b; margin-bottom: 15px;">👩‍🏫 Data Guru</h3>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay">
    <div class="modal-card">
        <h2 style="margin-top: 0; color: #0047FF; font-size: 1.4rem; display: flex; align-items: center; gap: 8px; margin-bottom: 25px; font-weight: 800; font-family: 'Nunito', sans-serif;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Tambah Data Guru
        </h2>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 700; font-size: 0.9rem; font-family: 'Nunito', sans-serif;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" style="font-family: 'Nunito', sans-serif;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div style="grid-column: 1 / -1;">
                    <label class="form-label">Nama Lengkap Guru</label>
                    <input type="text" name="nama_guru" required placeholder="Masukkan Nama Lengkap" class="form-input" value="<?php echo htmlspecialchars($_POST['nama_guru'] ?? ''); ?>">
                </div>
                <div>
                    <label class="form-label">No. HP</label>
                    <input type="text" name="no_hp" placeholder="Masukkan No. HP" class="form-input" value="<?php echo htmlspecialchars($_POST['no_hp'] ?? ''); ?>">
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="Aktif" <?php echo (($_POST['status'] ?? 'Aktif') === 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="Tidak Aktif" <?php echo (($_POST['status'] ?? '') === 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Kata Sandi (Login)</label>
                    <input type="text" name="kata_sandi" required placeholder="Masukkan Kata Sandi" class="form-input" value="<?php echo htmlspecialchars($_POST['kata_sandi'] ?? ''); ?>">
                </div>
                <div>
                    <label class="form-label">Peran</label>
                    <select name="peran" class="form-select">
                        <option value="Guru" <?php echo (($_POST['peran'] ?? 'Guru') === 'Guru') ? 'selected' : ''; ?>>Guru</option>
                        <option value="Admin" <?php echo (($_POST['peran'] ?? '') === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 15px;">
                <a href="../dataguru.php" style="padding: 10px 30px; border: 1px solid #c7d2fe; border-radius: 12px; background: white; color: #475569; font-weight: 800; text-decoration: none; font-size: 0.95rem; display: flex; align-items: center; justify-content: center;">Batal</a>
                <button type="submit" style="padding: 10px 30px; border: none; border-radius: 12px; background: #007bff; color: white; font-weight: 800; cursor: pointer; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(0,123,255,0.3);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../../../App/Layout/footer.php'; ?>
