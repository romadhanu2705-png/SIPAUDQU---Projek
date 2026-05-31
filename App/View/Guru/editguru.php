<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . '/../../../../App/Config/Database.php';

$error = '';

$id_guru = $_GET['id'] ?? null;
if (!$id_guru) {
    $_SESSION['flash_error'] = "ID Guru tidak valid.";
    header("Location: ../dataguru.php");
    exit;
}

try {
    $pdo = \App\Config\Database::connect();

    // Ambil data guru beserta data pengguna
    $stmt = $pdo->prepare("
        SELECT g.id_guru, g.id_user, g.nama_guru, g.no_hp, g.status, p.peran, p.kata_sandi
        FROM guru g
        LEFT JOIN pengguna p ON g.id_user = p.id_user
        WHERE g.id_guru = :id
    ");
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
    $nama_guru  = trim($_POST['nama_guru'] ?? '');
    $no_hp      = trim($_POST['no_hp'] ?? '');
    $status     = $_POST['status'] ?? 'Aktif';
    $peran      = $_POST['peran'] ?? 'Guru';
    $kata_sandi = trim($_POST['kata_sandi'] ?? '');

    if (empty($nama_guru)) {
        $error = "Nama Guru wajib diisi.";
    } else {
        try {
            $pdo->beginTransaction();

            // Update tabel pengguna
            if (!empty($kata_sandi)) {
                $stmt_p = $pdo->prepare("UPDATE pengguna SET nama = :nama, kata_sandi = :kata_sandi, peran = :peran WHERE id_user = :id_user");
                $stmt_p->execute([
                    'nama'       => $nama_guru,
                    'kata_sandi' => $kata_sandi,
                    'peran'      => $peran,
                    'id_user'    => $teacher['id_user'],
                ]);
            } else {
                $stmt_p = $pdo->prepare("UPDATE pengguna SET nama = :nama, peran = :peran WHERE id_user = :id_user");
                $stmt_p->execute([
                    'nama'    => $nama_guru,
                    'peran'   => $peran,
                    'id_user' => $teacher['id_user'],
                ]);
            }

            // Update tabel guru
            $stmt_g = $pdo->prepare("UPDATE guru SET nama_guru = :nama_guru, no_hp = :no_hp, status = :status WHERE id_guru = :id_guru");
            $stmt_g->execute([
                'nama_guru' => $nama_guru,
                'no_hp'     => empty($no_hp) ? null : $no_hp,
                'status'    => $status,
                'id_guru'   => $id_guru,
            ]);

            $pdo->commit();
            $_SESSION['flash_success'] = "Data guru berhasil diperbarui.";
            header("Location: ../dataguru.php");
            exit;
        } catch (\Exception $e) {
            $pdo->rollBack();
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
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
 .modal-card { background: white; border-radius: 24px; border: 3px solid #007bff; padding: 40px; width: 600px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); position: relative; }
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
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0047FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
            Edit Data Guru
        </h2>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 700; font-size: 0.9rem; font-family: 'Nunito', sans-serif;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" style="font-family: 'Nunito', sans-serif;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div style="grid-column: 1 / -1;">
                    <label class="form-label">Nama Lengkap Guru</label>
                    <input type="text" name="nama_guru" required placeholder="Masukkan Nama Lengkap" class="form-input"
                           value="<?php echo htmlspecialchars($_POST['nama_guru'] ?? $teacher['nama_guru']); ?>">
                </div>
                <div>
                    <label class="form-label">No. HP</label>
                    <input type="text" name="no_hp" placeholder="Masukkan No. HP" class="form-input"
                           value="<?php echo htmlspecialchars($_POST['no_hp'] ?? $teacher['no_hp'] ?? ''); ?>">
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="Aktif" <?php echo (($_POST['status'] ?? $teacher['status']) === 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="Tidak Aktif" <?php echo (($_POST['status'] ?? $teacher['status']) === 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Kata Sandi Baru <span style="font-weight:400; color:#94a3b8;">(kosongkan jika tidak diubah)</span></label>
                    <input type="text" name="kata_sandi" placeholder="Masukkan Kata Sandi Baru" class="form-input">
                </div>
                <div>
                    <label class="form-label">Peran</label>
                    <select name="peran" class="form-select">
                        <option value="Guru" <?php echo (($_POST['peran'] ?? $teacher['peran']) === 'Guru') ? 'selected' : ''; ?>>Guru</option>
                        <option value="Admin" <?php echo (($_POST['peran'] ?? $teacher['peran']) === 'Admin') ? 'selected' : ''; ?>>Admin</option>
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
