<?php
session_start();
require_once __DIR__ . '/../../../App/Config/Database.php';

$pdo = \App\Config\Database::connect();
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = '';
unset($_SESSION['flash_success']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_siswa = intval($_POST['id_siswa'] ?? 0);
    $id_guru = intval($_POST['id_guru'] ?? 0);
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $jenis_kegiatan = trim($_POST['jenis_kegiatan'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $catatan = trim($_POST['catatan_khusus'] ?? '');

    // Handling file upload
    $dokumentasi_path = '';
    if (isset($_FILES['foto_dokumentasi']) && $_FILES['foto_dokumentasi']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto_dokumentasi']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['foto_dokumentasi']['name']);
        $upload_dir = __DIR__ . '/../../../Public/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $dokumentasi_path = '/SIPAUDQU/Public/uploads/' . $file_name;
        }
    }

    if ($id_siswa && $jenis_kegiatan) {
        try {
            $stmt = $pdo->prepare("INSERT INTO aktivitas (id_siswa, id_guru, tanggal, jenis_kegiatan, kategori, catatan, dokumentasi) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_siswa, $id_guru, $tanggal, $jenis_kegiatan, $kategori, $catatan, $dokumentasi_path]);
            $_SESSION['flash_success'] = "Aktivitas harian berhasil disimpan! 🎉";
            header("Location: aktivitas.php");
            exit;
        } catch (Exception $e) {
            $flash_error = "Gagal menyimpan aktivitas: " . $e->getMessage();
        }
    } else {
        $flash_error = "Mohon isi Nama Siswa dan Jenis Kegiatan.";
    }
}

// Fetch students and teachers for select options
$allStudents = $pdo->query("SELECT id_siswa, nama_siswa FROM murid ORDER BY nama_siswa ASC")->fetchAll();
$allTeachers = $pdo->query("SELECT id_guru, nama_guru FROM guru ORDER BY nama_guru ASC")->fetchAll();



<div class="page-wrapper">
    <div class="layout-container">
        <?php include '../../../App/Layout/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-card" style="font-family: 'Nunito', sans-serif;">
                <h3 style="font-size: 1rem; font-weight: 800; color: #1e293b; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                    🎨 Aktivitas Harian
                </h3>

                <!-- Flash Messages -->
                <?php if ($flash_success): ?>
                    <div style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; border-radius: 10px; padding: 10px 16px; margin-bottom: 15px; font-weight: 800; font-size: 0.85rem;">
                        ✅ <?php echo htmlspecialchars($flash_success); ?>
                    </div>
                <?php endif; ?>
                <?php if ($flash_error): ?>
                    <div style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; border-radius: 10px; padding: 10px 16px; margin-bottom: 15px; font-weight: 800; font-size: 0.85rem;">
                        ⚠️ <?php echo htmlspecialchars($flash_error); ?>
                    </div>
                <?php endif; ?>

                <!-- Input Aktivitas Form -->
                <form method="POST" enctype="multipart/form-data">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <!-- Tanggal -->
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 700; color: #1e293b; display: block; margin-bottom: 5px;">Tanggal</label>
                            <input type="date" name="tanggal" required value="<?php echo date('Y-m-d'); ?>" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.85rem; font-family: 'Nunito', sans-serif; outline: none; background: white;">
                        </div>

                        <!-- Nama Siswa -->
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 700; color: #1e293b; display: block; margin-bottom: 5px;">Nama Siswa</label>
                            <select name="id_siswa" required style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.85rem; font-family: 'Nunito', sans-serif; outline: none; background: white;">
                                <option value="">-- Pilih Siswa --</option>
                                <?php foreach($allStudents as $s): ?>
                                    <option value="<?php echo $s['id_siswa']; ?>"><?php echo htmlspecialchars($s['nama_siswa']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Jenis Kegiatan -->
                    <div style="margin-bottom: 15px;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: #1e293b; display: block; margin-bottom: 5px;">Jenis Kegiatan</label>
                        <input type="text" name="jenis_kegiatan" required placeholder="Contoh: Mewarnai, Bermain..." style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.85rem; font-family: 'Nunito', sans-serif; outline: none; background: white;">
                    </div>

                    <!-- Kategori & Guru -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <!-- Kategori -->
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 700; color: #1e293b; display: block; margin-bottom: 5px;">Kategori</label>
                            <select name="kategori" required style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.85rem; font-family: 'Nunito', sans-serif; outline: none; background: white;">
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Seni">Seni</option>
                                <option value="Motorik">Motorik</option>
                                <option value="Kognitif">Kognitif</option>
                                <option value="Sosial">Sosial</option>
                                <option value="Musikal">Musikal</option>
                            </select>
                        </div>

                        <!-- Guru Pengajar -->
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 700; color: #1e293b; display: block; margin-bottom: 5px;">Guru Pengajar</label>
                            <select name="id_guru" required style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.85rem; font-family: 'Nunito', sans-serif; outline: none; background: white;">
                                <option value="">-- Pilih Guru --</option>
                                <?php foreach($allTeachers as $g): ?>
                                    <option value="<?php echo $g['id_guru']; ?>"><?php echo htmlspecialchars($g['nama_guru']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Catatan Khusus -->
                    <div style="margin-bottom: 15px;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: #1e293b; display: block; margin-bottom: 5px;">Catatan Khusus</label>
                        <textarea name="catatan_khusus" rows="4" placeholder="Tulis catatan..." style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.85rem; font-family: 'Nunito', sans-serif; resize: vertical; outline: none; background: white;"></textarea>
                    </div>

                    <!-- Upload Dokumentasi -->
                    <div style="margin-bottom: 15px;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: #1e293b; display: block; margin-bottom: 5px;">Upload Foto Dokumentasi</label>
                        <div style="border: 2px dashed #cbd5e1; border-radius: 8px; padding: 20px; text-align: center; background: #f8fafc; transition: all 0.2s;">
                            <input type="file" name="foto_dokumentasi" id="file-input" style="display: none;" onchange="updateFileName(this)">
                            <label for="file-input" style="cursor: pointer; display: block;">
                                <div style="font-size: 1.5rem; margin-bottom: 8px;">📸</div>
                                <div id="upload-label" style="font-size: 0.85rem; color: #64748b; font-weight: 700;">Klik untuk pilih file</div>
                            </label>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <button type="reset" style="padding: 10px 20px; border: 1px solid #e2e8f0; background: white; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; color: #64748b;">
                            Reset
                        </button>
                        <button type="submit" style="padding: 10px 20px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; box-shadow: 0 4px 12px rgba(59,130,246,0.3);">
                            Simpan Aktivitas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

