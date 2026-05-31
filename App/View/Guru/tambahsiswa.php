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
        <h2 style="margin-top: 0; color: #0047FF; font-size: 1.4rem; display: flex; align-items: center; gap: 8px; margin-bottom: 25px; font-weight: 800; font-family: 'Nunito', sans-serif;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Tambah Data Siswa
        </h2>
        
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 700; font-size: 0.9rem; font-family: 'Nunito', sans-serif;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" style="font-family: 'Nunito', sans-serif;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_siswa" required placeholder="Masukkan Nama Lengkap" class="form-input">
                </div>
                <div>
                    <label class="form-label">NIS</label>
                    <input type="text" name="nis" placeholder="Masukkan NIS" class="form-input">
                </div>
                
                <div>
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-input" style="color: #64748b; font-weight: 600;">
                </div>
                <div>
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" required class="form-select">
                        <option value="">~ Pilih Jenis Kelamin ~</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                
                <div>
                    <label class="form-label">Nama Orang Tua/Wali</label>
                    <input type="text" name="nama_wali" placeholder="Masukkan Nama Orang Tua/Wali" class="form-input">
                </div>
                <div>
                    <label class="form-label">Kelompok</label>
                    <select name="id_kelas" class="form-select">
                        <option value="">~ Pilih Kelompok ~</option>
                        <?php foreach ($kelas_list as $kelas): ?>
                            <option value="<?php echo $kelas['id_kelas']; ?>"><?php echo htmlspecialchars($kelas['nama_kelas']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div style="margin-bottom: 30px;">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" rows="3" placeholder="Masukkan Alamat Lengkap" class="form-input" style="resize: none;"></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 15px;">
                <a href="../datasiswa.php" style="padding: 10px 30px; border: 1px solid #c7d2fe; border-radius: 12px; background: white; color: #475569; font-weight: 800; text-decoration: none; font-size: 0.95rem; display: flex; align-items: center; justify-content: center;">Batal</a>
                <button type="submit" style="padding: 10px 30px; border: none; border-radius: 12px; background: #007bff; color: white; font-weight: 800; cursor: pointer; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(0,123,255,0.3);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../../../App/Layout/footer.php'; ?>
