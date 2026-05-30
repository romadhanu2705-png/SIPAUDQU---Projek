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
        <h2 style="margin-top: 0; color: #0047FF; font-size: 1.3rem; display: flex; align-items: center; gap: 8px; margin-bottom: 30px; font-weight: 800;">
            ✏️ Edit Jadwal Kegiatan Belajar
        </h2>
        
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 700; font-size: 0.9rem;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px;">
                <div>
                    <label class="form-label">Tema</label>
                    <input type="text" name="tema" value="<?php echo htmlspecialchars($jadwal['tema']); ?>" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Halaman Buku</label>
                    <input type="text" name="halaman" value="<?php echo htmlspecialchars($jadwal['halaman'] ?? ''); ?>" class="form-input">
                </div>
                
                <div>
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" value="<?php echo htmlspecialchars($jadwal['tanggal']); ?>" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Jenis Kegiatan</label>
                    <select name="kegiatan" required class="form-select" style="width: 100%; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; outline: none; font-family: inherit; font-weight: 600; color: #94a3b8; background: #fff;">
                        <option value="Mengenal" <?php echo $jadwal['kegiatan'] === 'Mengenal' ? 'selected' : ''; ?>>Mengenal</option>
                        <option value="Mewarnai" <?php echo $jadwal['kegiatan'] === 'Mewarnai' ? 'selected' : ''; ?>>Mewarnai</option>
                        <option value="Menghitung" <?php echo $jadwal['kegiatan'] === 'Menghitung' ? 'selected' : ''; ?>>Menghitung</option>
                        <option value="Motorik" <?php echo $jadwal['kegiatan'] === 'Motorik' ? 'selected' : ''; ?>>Motorik</option>
                        <option value="Bermain" <?php echo $jadwal['kegiatan'] === 'Bermain' ? 'selected' : ''; ?>>Bermain</option>
                        <option value="Outdoor" <?php echo $jadwal['kegiatan'] === 'Outdoor' ? 'selected' : ''; ?>>Outdoor</option>
                    </select>
                </div>
                
                <div>
                    <label class="form-label">Hari</label>
                    <select name="hari" class="form-select" style="width: 100%; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; outline: none; font-family: inherit; font-weight: 600; color: #94a3b8; background: #fff;">
                        <option value="">~ Otomatis ~</option>
                        <option value="Senin" <?php echo $jadwal['hari'] === 'Senin' ? 'selected' : ''; ?>>Senin</option>
                        <option value="Selasa" <?php echo $jadwal['hari'] === 'Selasa' ? 'selected' : ''; ?>>Selasa</option>
                        <option value="Rabu" <?php echo $jadwal['hari'] === 'Rabu' ? 'selected' : ''; ?>>Rabu</option>
                        <option value="Kamis" <?php echo $jadwal['hari'] === 'Kamis' ? 'selected' : ''; ?>>Kamis</option>
                        <option value="Jumat" <?php echo $jadwal['hari'] === 'Jumat' ? 'selected' : ''; ?>>Jumat</option>
                        <option value="Sabtu" <?php echo $jadwal['hari'] === 'Sabtu' ? 'selected' : ''; ?>>Sabtu</option>
                        <option value="Minggu" <?php echo $jadwal['hari'] === 'Minggu' ? 'selected' : ''; ?>>Minggu</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="form-input" style="resize: none;"><?php echo htmlspecialchars($jadwal['deskripsi'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div style="display: flex; justify-content: center; gap: 15px;">
                <a href="../jadwalbelajar.php" style="padding: 8px 30px; border: 2px solid #cbd5e1; border-radius: 20px; background: white; color: #64748b; font-weight: 800; text-decoration: none; font-size: 0.9rem;">Batal</a>
                <button type="submit" style="padding: 8px 30px; border: none; border-radius: 20px; background: #007bff; color: white; font-weight: 800; cursor: pointer; font-size: 0.9rem; display: flex; align-items: center; gap: 6px;">
                    💾 Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../../../App/Layout/footer.php'; ?>
