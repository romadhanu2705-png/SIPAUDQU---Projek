<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . '/../../../../App/Config/Database.php';

$error = '';
$success = '';

try {
    $pdo = \App\Config\Database::connect();
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
        $error = "Tema, Tanggal, dan Jenis Kegiatan wajib diisi.";
    } else {
        try {
            // Dapatkan id_guru dari user_id session jika ada
            $id_user = $_SESSION['user_id'] ?? null;
            $id_guru = 1; // Default
            if ($id_user) {
                $stmt_guru = $pdo->prepare("SELECT id_guru FROM guru WHERE id_user = :uid LIMIT 1");
                $stmt_guru->execute(['uid' => $id_user]);
                $guru_row = $stmt_guru->fetch();
                if ($guru_row) {
                    $id_guru = $guru_row['id_guru'];
                }
            }

            // Jika hari kosong, coba deteksi nama hari dari tanggal
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
 $query = "INSERT INTO jadwal_belajar (id_guru, tema, tanggal, hari, halaman, kegiatan, deskripsi) 
                      VALUES (:id_guru, :tema, :tanggal, :hari, :halaman, :kegiatan, :deskripsi)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'id_guru' => $id_guru,
                'tema' => $tema,
                'tanggal' => $tanggal,
                'hari' => $hari,
                'halaman' => $halaman,
                'kegiatan' => $kegiatan,
                'deskripsi' => $deskripsi
            ]);

            $_SESSION['flash_success'] = "Data jadwal belajar berhasil ditambahkan.";
            header("Location: ../jadwalbelajar.php");
            exit;
        } catch (\Exception $e) {
            $error = "Gagal menambahkan data jadwal: " . $e->getMessage();
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
    .modal-card { background: white; border-radius: 24px; padding: 40px; width: 650px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); position: relative; font-family: 'Nunito', sans-serif; }
    .form-label { display: block; font-weight: 800; font-size: 0.9rem; margin-bottom: 8px; color: #000; }
    .form-input { width: 100%; padding: 12px; border: 1px solid #c7d2fe; border-radius: 12px; font-size: 0.9rem; box-sizing: border-box; outline: none; font-family: inherit; font-weight: 600; color: #334155; }
    .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .form-input::placeholder { color: #94a3b8; font-weight: 600; }
    .form-select { width: 100%; padding: 12px; border: 1px solid #c7d2fe; border-radius: 12px; font-size: 0.9rem; box-sizing: border-box; outline: none; appearance: none; background: url('data:image/svg+xml;utf8,<svg fill="%2394a3b8" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 10px center; background-color: white; color: #334155; font-family: inherit; font-weight: 600; }
    .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
</style>

<div class="page-wrapper">
    <div class="layout-container">
        <?php include '../../../../App/Layout/sidebar.php'; ?>
        <div class="main-content">
            <div class="content-card">
                <h3 style="font-size: 1rem; font-weight: 800; color: #1e293b; margin-bottom: 15px;">📅 Jadwal Kegiatan Pembelajaran</h3>
            </div>
        </div>
    </div>
</div>
<div class="modal-overlay">
    <div class="modal-card">
        <h2 style="margin-top: 0; color: #0047FF; font-size: 1.4rem; display: flex; align-items: center; gap: 8px; margin-bottom: 25px; font-weight: 800;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Tambah Jadwal Kegiatan Belajar
        </h2>
        
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 700; font-size: 0.9rem;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label class="form-label">Tema</label>
                    <input type="text" name="tema" required placeholder="Contoh: Binatang" class="form-input">
                </div>
                <div>
                    <label class="form-label">Halaman Buku</label>
                    <input type="text" name="halaman" placeholder="Contoh: Halaman 1 atau Outdoor" class="form-input">
                </div>
                
                <div>
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Jenis Kegiatan</label>
                    <select name="kegiatan" required class="form-select">
                        <option value="">~ Pilih Kegiatan ~</option>
                        <option value="Mengenal">Mengenal</option>
                        <option value="Mewarnai">Mewarnai</option>
                        <option value="Menghitung">Menghitung</option>
                        <option value="Motorik">Motorik</option>
                        <option value="Bermain">Bermain</option>
                        <option value="Outdoor">Outdoor</option>
                    </select>
                </div>
                
                <div>
                    <label class="form-label">Hari</label>
                    <select name="hari" class="form-select">
                        <option value="">~ Otomatis Berdasarkan Tanggal ~</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                    </select>
                </div>
            </div>
            
            <div style="margin-bottom: 30px;">
                <label class="form-label">Deskripsi Kegiatan</label>
                <textarea name="deskripsi" rows="3" placeholder="Contoh: Mengenal berbagai jenis hewan dan habitatnya" class="form-input" style="resize: none;"></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 15px;">
                <a href="../jadwalbelajar.php" style="padding: 10px 30px; border: 1px solid #c7d2fe; border-radius: 12px; background: white; color: #475569; font-weight: 800; text-decoration: none; font-size: 0.95rem; display: flex; align-items: center; justify-content: center;">Batal</a>
                <button type="submit" style="padding: 10px 30px; border: none; border-radius: 12px; background: #007bff; color: white; font-weight: 800; cursor: pointer; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(0,123,255,0.3);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../../../App/Layout/footer.php'; ?>
