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

    .dok-close-btn:hover {
        transform: scale(1.1);
        background: #dc2626;
    }
</style>

<div class="page-wrapper" style="font-family: 'Nunito', sans-serif;">
    <div class="layout-container">
        <?php include '../../../App/Layout/sidebar.php'; ?>
        
        <div class="main-content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h3>Selamat datang, <?php echo htmlspecialchars($user_name); ?>!</h3>
            </div>

            <!-- Toast notification if success -->
            <?php if ($flash_success): ?>
                <div style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; border-radius: 10px; padding: 10px 16px; margin-bottom: 15px; font-weight: 800; font-size: 0.85rem;">
                    ✅ <?php echo htmlspecialchars($flash_success); ?>
                </div>
            <?php endif; ?>

            <!-- Dashboard Stats -->
            <div class="content-card">
                <div class="dashboard-grid">
                    <!-- Jumlah Siswa -->
                    <div style="background: linear-gradient(135deg, #fef3c7, #fcd34d); border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(252,211,77,0.3);">
                        <div style="font-size: 2rem; font-weight: 900; color: #854d0e;">😊</div>
                        <div style="font-size: 0.9rem; font-weight: 700; color: #92400e; margin-top: 8px;">Jumlah Siswa</div>
                        <div style="font-size: 2rem; font-weight: 900; color: #854d0e; margin-top: 5px;"><?php echo $jumlah_siswa; ?></div>
                    </div>

                    <!-- Jumlah Guru -->
                    <div style="background: linear-gradient(135deg, #dbeafe, #93c5fd); border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(147,197,253,0.3);">
                        <div style="font-size: 2rem; font-weight: 900; color: #1e40af;">👩‍🏫</div>
                        <div style="font-size: 0.9rem; font-weight: 700; color: #1e40af; margin-top: 8px;">Jumlah Guru</div>
                        <div style="font-size: 2rem; font-weight: 900; color: #1e40af; margin-top: 5px;"><?php echo $jumlah_guru; ?></div>
                    </div>

                    <!-- Absensi Hari Ini -->
                    <div style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(220,252,231,0.5);">
                        <div style="font-size: 2rem; font-weight: 900; color: #16a34a;">✓</div>
                        <div style="font-size: 0.9rem; font-weight: 700; color: #16a34a; margin-top: 8px;">Absensi Hari Ini</div>
                        <div style="font-size: 2rem; font-weight: 900; color: #16a34a; margin-top: 5px;"><?php echo $absensi_hari_ini; ?></div>
                    </div>

                    <!-- Aktivitas Hari Ini -->
                    <div style="background: linear-gradient(135deg, #fdf4ff, #f3e8ff); border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(243,232,255,0.5);">
                        <div style="font-size: 2rem; font-weight: 900; color: #9333ea;">🎨</div>
                        <div style="font-size: 0.9rem; font-weight: 700; color: #9333ea; margin-top: 8px;">Aktivitas Hari Ini</div>
                        <div style="font-size: 2rem; font-weight: 900; color: #9333ea; margin-top: 5px;"><?php echo $aktivitas_hari_ini; ?></div>
                    </div>
                </div>
            </div>

            <!-- Info Kegiatan dan Akses Cepat -->
            <div class="content-card">
                <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                    <!-- Info Kegiatan -->
                    <div style="flex: 1 1 300px;">
                        <h4 style="font-size: 0.9rem; font-weight: 800; color: #1e293b; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                            ℹ️ Info Kegiatan
                        </h4>
                        <div style="background: #fef3c7; border: 1px solid #fde047; border-radius: 8px; padding: 12px;">
                            <ul style="list-style: none; padding-left: 0; margin: 0; font-size: 0.8rem; color: #626262; line-height: 1.6;">
                                <?php if(empty($info_kegiatan_list)): ?>
                                    <li style="color: #94a3b8; font-style: italic; text-align: center; padding: 10px 0;">Belum ada pengumuman kegiatan.</li>
                                <?php else: ?>
                                    <?php foreach($info_kegiatan_list as $info): ?>
                                        <li style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px; border-bottom: 1px dashed #fcd34d; padding-bottom: 6px;">
                                            <span style="flex: 1; padding-right: 10px;">📢 <?php echo htmlspecialchars($info['isi_kegiatan']); ?></span>
                                            <a href="?hapus_kegiatan=<?php echo $info['id_kegiatan']; ?>" onclick="return confirm('Hapus kegiatan ini?')" style="color: #ef4444; text-decoration: none; font-size: 0.7rem; font-weight: 800; flex-shrink: 0; background: #fee2e2; padding: 2px 6px; border-radius: 4px; transition: background 0.2s;" onmouseover="this.style.background='#fca5a5'" onmouseout="this.style.background='#fee2e2'">Hapus</a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                            
                            <!-- Input Form -->
                            <form method="POST" style="margin-top: 12px; display: flex; gap: 6px; border-top: 1px solid #fde047; padding-top: 10px;">
                                <input type="hidden" name="action" value="tambah_kegiatan">
                                <input type="text" name="isi_kegiatan" placeholder="Tambah kegiatan baru..." required style="flex: 1; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.75rem; font-family: 'Nunito', sans-serif; outline: none; background: white;">
                                <button type="submit" style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; border-radius: 6px; padding: 6px 12px; font-weight: 700; font-size: 0.75rem; cursor: pointer; box-shadow: 0 2px 5px rgba(59,130,246,0.2);">Tambah</button>
                            </form>
                        </div>
                    </div>

                    <!-- Akses Cepat -->
                    <div style="flex: 1 1 300px;">
                        <h4 style="font-size: 0.9rem; font-weight: 800; color: #1e293b; margin-bottom: 10px;">Akses Cepat</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 8px;">
                            <a href="Laporanabsensi.php" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 12px; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 700; font-size: 0.8rem; box-shadow: 0 4px 12px rgba(239,68,68,0.3); transition: all 0.2s;">
                                📊 Laporan Absensi
                            </a>
                            <a href="jadwalbelajar.php" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 12px; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 700; font-size: 0.8rem; box-shadow: 0 4px 12px rgba(245,158,11,0.3); transition: all 0.2s;">
                                📅 Jadwal Belajar
                            </a>
                            <a href="aktivitas.php" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; padding: 12px; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 700; font-size: 0.8rem; box-shadow: 0 4px 12px rgba(59,130,246,0.3); transition: all 0.2s;">
                                🎨 Aktivitas Harian
                            </a>
                            <a href="absensi.php" style="background: linear-gradient(135deg, #22c55e, #16a34a); color: white; padding: 12px; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 700; font-size: 0.8rem; box-shadow: 0 4px 12px rgba(34,197,94,0.3); transition: all 0.2s;">
                                ✓ Absensi Siswa
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aktivitas Terbaru -->
            <div class="content-card">
                <h4 style="font-size: 0.9rem; font-weight: 800; color: #1e293b; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                    🎨 Aktivitas Terbaru
                </h4>
                <?php if(empty($aktivitas_terbaru_list)): ?>
                    <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 15px; text-align: center; color: #64748b; font-size: 0.8rem;">
                        <div style="font-size: 2rem; margin-bottom: 5px;">📋</div>
                        <div>Belum ada aktivitas terbaru</div>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php 
                        $kategoriColors = [
                            'Seni'     => ['bg' => '#fef3c7', 'color' => '#d97706'],
                            'Kognitif' => ['bg' => '#dbeafe', 'color' => '#2563eb'],
                            'Sosial'   => ['bg' => '#dcfce7', 'color' => '#16a34a'],
                            'Motorik'  => ['bg' => '#fee2e2', 'color' => '#dc2626'],
                            'Musikal'  => ['bg' => '#f3e8ff', 'color' => '#9333ea'],
                        ];
                        foreach($aktivitas_terbaru_list as $akt): 
                            $kCat = $akt['kategori'] ?? '';
                            $kStyle = $kategoriColors[$kCat] ?? ['bg' => '#f1f5f9', 'color' => '#475569'];
                            
                            $t_formatted = '';
                            if (!empty($akt['tanggal']) && $akt['tanggal'] !== '0000-00-00') {
                                $t_formatted = date('j M Y', strtotime($akt['tanggal']));
                            }
                        ?>
                            <div style="display: flex; gap: 15px; background: #ffffff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px; align-items: start; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                        <span style="font-size: 0.85rem; font-weight: 800; color: #1e293b;"><?php echo htmlspecialchars($akt['nama_siswa']); ?></span>
                                        <span style="display: inline-block; padding: 2px 8px; border-radius: 12px; font-weight: 800; font-size: 0.65rem; background:<?php echo $kStyle['bg']; ?>; color:<?php echo $kStyle['color']; ?>;">
                                            <?php echo htmlspecialchars($kCat ?: 'Lainnya'); ?>
                                        </span>
                                        <span style="font-size: 0.7rem; color: #94a3b8; margin-left: auto; font-weight: 700;"><?php echo $t_formatted; ?></span>
                                    </div>
                                    <div style="font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 3px;">
                                        👉 <?php echo htmlspecialchars($akt['jenis_kegiatan']); ?>
                                    </div>
                                    <?php if (!empty($akt['catatan'])): ?>
                                        <div style="font-size: 0.75rem; color: #64748b; font-style: italic; background: #f8fafc; padding: 6px 10px; border-radius: 6px; border-left: 3px solid #cbd5e1; margin-top: 5px;">
                                            "<?php echo htmlspecialchars($akt['catatan']); ?>"
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($akt['dokumentasi'])): ?>
                                    <div style="flex-shrink: 0; width: 60px; height: 60px; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; position: relative;">
                                        <a href="javascript:void(0)" onclick="showDokumentasi('<?php echo htmlspecialchars(addslashes($akt['dokumentasi'])); ?>')">
                                            <img src="<?php echo htmlspecialchars($akt['dokumentasi']); ?>" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Dokumentasi -->
<div class="dok-modal-overlay" id="dokModal" onclick="closeDokumentasi(event)">
    <div class="dok-modal-content">
        <button class="dok-close-btn" onclick="closeDokumentasi(event, true)">&times;</button>
        <img id="dokImage" src="" alt="Dokumentasi">
    </div>
</div>

<script>
    function showDokumentasi(imgUrl) {
        document.getElementById('dokImage').src = imgUrl;
        document.getElementById('dokModal').style.display = 'flex';
    }

    function closeDokumentasi(e, forceClose = false) {
        if (forceClose || e.target.id === 'dokModal') {
            document.getElementById('dokModal').style.display = 'none';
            document.getElementById('dokImage').src = '';
        }
    }
</script>

<?php include '../../../App/Layout/footer.php'; ?>
