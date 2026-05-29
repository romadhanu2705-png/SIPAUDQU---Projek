<?php
// laporan.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle   = 'Laporan Anak';
$currentPage = 'laporan';

// Tab aktif: absensi | aktivitas | perkembangan
$activeTab = $_GET['tab'] ?? 'absensi';
$allowed_tabs = ['absensi', 'aktivitas', 'perkembangan'];
if (!in_array($activeTab, $allowed_tabs)) {
    $activeTab = 'absensi';
}

// Bulan filter
$selectedBulan = $_GET['bulan'] ?? 'April 2026';

// =============================================
// Mengambil data dari database
// =============================================
require_once __DIR__ . '/../../Config/Database.php';
$pdo = \App\Config\Database::connect();

// ── Ambil siswa berdasarkan id_wali dari session ────────────────────────────
$all_students = [];
$student_row  = null;
$id_wali      = $_SESSION['id_wali'] ?? null;

if ($id_wali) {
    $stmtMurid = $pdo->prepare("
        SELECT m.*, k.nama_kelas
        FROM murid m
        LEFT JOIN kelas k ON m.id_kelas = k.id_kelas
        WHERE m.id_wali = :id_wali
        ORDER BY m.nama_siswa ASC
    ");
    $stmtMurid->execute(['id_wali' => $id_wali]);
    $all_students = $stmtMurid->fetchAll();
} else {
    // Fallback: cari berdasarkan id_user di wali_murid
    $stmtWali = $pdo->prepare("SELECT * FROM wali_murid WHERE id_user = :uid LIMIT 1");
    $stmtWali->execute(['uid' => $_SESSION['user_id']]);
    $wali = $stmtWali->fetch();
    if ($wali) {
        $_SESSION['id_wali']   = $wali['id_wali'];
        $_SESSION['nama_wali'] = $wali['nama_wali'];
        $stmtMurid = $pdo->prepare("
            SELECT m.*, k.nama_kelas
            FROM murid m
            LEFT JOIN kelas k ON m.id_kelas = k.id_kelas
            WHERE m.id_wali = :id_wali
            ORDER BY m.nama_siswa ASC
        ");
        $stmtMurid->execute(['id_wali' => $wali['id_wali']]);
        $all_students = $stmtMurid->fetchAll();
    }
}
$selected_id = $_GET['id_siswa'] ?? null;
if (!empty($all_students)) {
    if ($selected_id) {
        foreach ($all_students as $s) {
            if ($s['id_siswa'] == $selected_id) {
                $student_row = $s;
                break;
            }
        }
    }
    if (!$student_row) {
        $student_row = $all_students[0];
        $selected_id = $student_row['id_siswa'];
    }
}

$student = [
    'id_siswa' => $student_row['id_siswa'] ?? 0,
    'nama'     => $student_row['nama_siswa'] ?? 'Tidak Ada Data Siswa',
    'kelas'    => str_replace('Kelas ', '', $student_row['nama_kelas'] ?? '-'),
];

$studentName = $student['nama'];

// Parse Bulan Filter (e.g. "April 2026" -> "2026-04")
$indonesian_months = [
    'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
    'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
    'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'
];

$parts = explode(' ', $selectedBulan);
if (count($parts) === 2) {
    $bulan_name = $parts[0];
    $tahun = $parts[1];
    $bulan_num = $indonesian_months[$bulan_name] ?? date('m');
    $db_bulan = $tahun . '-' . $bulan_num;
} else {
    $db_bulan = date('Y-m');
}

$bulan_string = $selectedBulan;

// --- Data Absensi ---
// Menarik tanggal sekolah dari jadwal_belajar lalu mencocokkan dengan absensi
$query_absensi = "
    SELECT 
        j.tanggal, 
        j.hari,
        COALESCE(a.status, 'Hadir') as status
    FROM (
        SELECT DISTINCT tanggal, hari 
        FROM jadwal_belajar 
        WHERE DATE_FORMAT(tanggal, '%Y-%m') = :bulan
    ) j
    LEFT JOIN absensi a ON j.tanggal = a.tanggal AND a.id_siswa = :id_siswa
    ORDER BY j.tanggal ASC
";
$stmt_abs = $pdo->prepare($query_absensi);
$stmt_abs->execute(['bulan' => $db_bulan, 'id_siswa' => $student['id_siswa']]);
$absensi_records = $stmt_abs->fetchAll();

$rekap_absensi = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0];
$detail_absensi = [];
$no = 1;
$days_id = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => "Jum'at", 6 => 'Sabtu'];

foreach ($absensi_records as $rec) {
    $status = $rec['status'];
    if ($status === 'Alpa') $status = 'Alpha';
    
    $key = strtolower($status);
    if (isset($rekap_absensi[$key])) {
        $rekap_absensi[$key]++;
    }

    $day_num = date('w', strtotime($rec['tanggal']));
    $hari = $days_id[$day_num] ?? $rec['hari'];
    $tgl_formatted = date('j/n/Y', strtotime($rec['tanggal']));

    $detail_absensi[] = [
        'no'         => $no++,
        'tanggal'    => $tgl_formatted,
        'hari'       => $hari,
        'status'     => $status,
        'keterangan' => '-'
    ];
}

// --- Data Aktivitas Harian ---
$query_aktivitas = "
    SELECT 
        a.tanggal,
        a.jenis_kegiatan as kategori,
        a.kategori as kategori_utama,
        a.dokumentasi,
        g.nama_guru
    FROM aktivitas a
    LEFT JOIN guru g ON a.id_guru = g.id_guru
    WHERE a.id_siswa = :id_siswa AND DATE_FORMAT(a.tanggal, '%Y-%m') = :bulan
    ORDER BY a.tanggal ASC
";
$stmt_akt = $pdo->prepare($query_aktivitas);
$stmt_akt->execute(['id_siswa' => $student['id_siswa'], 'bulan' => $db_bulan]);
$aktivitas_records = $stmt_akt->fetchAll();

$aktivitas_harian = [];
$no_akt = 1;
$category_map = [
    'Seni'     => 'badge-mewarnai',
    'Motorik'  => 'badge-motorik',
    'Kognitif' => 'badge-menghitung',
    'Sosial'   => 'badge-bermain',
    'Musikal'  => 'badge-mengenal',
];

foreach ($aktivitas_records as $akt) {
    $kCat = $akt['kategori_utama'] ?? '';
    $badge_class = $category_map[$kCat] ?? 'badge-mengenal';
    
    $day_num = date('w', strtotime($akt['tanggal']));
    $hari = $days_id[$day_num] ?? '';
    $tgl_formatted = date('j/n/Y', strtotime($akt['tanggal']));

    $guru_nama = $akt['nama_guru'] ? 'Bu ' . strtok($akt['nama_guru'], ' ') : '-';

    $aktivitas_harian[] = [
        'no'             => $no_akt++,
        'tanggal'        => $tgl_formatted,
        'hari'           => $hari,
        'kategori'       => $akt['kategori'] ?: $kCat,
        'kategori_class' => $badge_class,
        'guru'           => $guru_nama,
        'dokumentasi'    => $akt['dokumentasi'],
    ];
}


// --- Data Perkembangan Anak ---
$query_perkembangan = "
    SELECT 
        p.aspek,
        p.penilaian,
        p.catatan,
        g.nama_guru
    FROM perkembangan_anak p
    LEFT JOIN guru g ON p.id_guru = g.id_guru
    WHERE p.id_siswa = :id_siswa AND p.bulan = :bulan
    ORDER BY p.id_perkembangan ASC
";
$stmt_perk = $pdo->prepare($query_perkembangan);
$stmt_perk->execute(['id_siswa' => $student['id_siswa'], 'bulan' => $bulan_string]);
$perk_records = $stmt_perk->fetchAll();

$perkembangan_defaults = [
    'Bahasa'           => ['aspek' => 'Aspek Bahasa',           'icon' => '💬', 'card_class' => ''],
    'Motorik'          => ['aspek' => 'Aspek Motorik',          'icon' => '✏️',  'card_class' => 'motorik'],
    'Sosial Emosional' => ['aspek' => 'Aspek Sosial Emosional', 'icon' => '🤝', 'card_class' => 'sosial'],
    'Kognitif'         => ['aspek' => 'Aspek Kognitif',         'icon' => '🧠', 'card_class' => 'kognitif'],
    'Seni'             => ['aspek' => 'Aspek Seni',             'icon' => '🎨', 'card_class' => 'motorik'],
];

$perkembangan = [];
foreach ($perk_records as $p) {
    $asp = trim($p['aspek']);
    $def = $perkembangan_defaults[$asp] ?? [
        'aspek' => 'Aspek ' . $asp,
        'icon' => '⭐',
        'card_class' => ''
    ];
    
    $penilaian_text = $p['penilaian'] ? "Penilaian: <strong>" . htmlspecialchars($p['penilaian']) . "</strong>.<br>" : "";
    $deskripsi = $penilaian_text . htmlspecialchars($p['catatan'] ?: '-');

    $perkembangan[] = [
        'aspek'       => $def['aspek'],
        'kelas'       => $def['card_class'],
        'deskripsi'   => $deskripsi,
        'icon'        => $def['icon'],
        'card_class'  => $def['card_class'],
    ];
}

// Status badge helper
function statusBadge(string $status): string {
    $map = [
        'Hadir' => 'status-hadir',
        'Sakit' => 'status-sakit',
        'Izin'  => 'status-izin',
        'Alpha' => 'status-alpha',
    ];
    $class = $map[$status] ?? 'status-alpha';
    return "<span class=\"status-badge {$class}\">{$status}</span>";
}

include '../../../App/Layout/headers.php';
?>

<div class="layout-container">
  <?php include '../../../App/Layout/sidebars.php'; ?>

    <main class="main-content">
      <div class="content-card">

        <!-- Welcome Banner -->
        <div class="welcome-banner">
          <h3>🎉 Selamat datang, Bapak/Ibu Wali Murid!</h3>
          <p>Informasi laporan absensi, aktivitas dan perkembangan anak Anda</p>
        </div>

        <!-- Student Info Box -->
        <div class="student-info-box">
          <?= htmlspecialchars($student['nama']) ?>
          <div class="student-class">Kelompok <?= htmlspecialchars($student['kelas']) ?></div>
        </div>

        <!-- Section Title -->
        <div class="section-heading">
          <i class="fas fa-chart-bar" style="color:#3b82f6;"></i>
          Laporan Anak
        </div>
        <p style="font-size:0.8rem; color:#64748b; margin-bottom:15px;">
          Informasi laporan absensi, aktivitas dan perkembangan anak Anda
        </p>

        <!-- Tab Navigation -->
        <div class="tab-nav">
          <a href="?tab=absensi&bulan=<?= urlencode($selectedBulan) ?>&id_siswa=<?= $student['id_siswa'] ?>"
             class="tab-btn <?= $activeTab === 'absensi' ? 'active' : '' ?>">
            Laporan Absensi
          </a>
          <a href="?tab=aktivitas&bulan=<?= urlencode($selectedBulan) ?>&id_siswa=<?= $student['id_siswa'] ?>"
             class="tab-btn <?= $activeTab === 'aktivitas' ? 'active' : '' ?>">
            Laporan Aktivitas Harian
          </a>
          <a href="?tab=perkembangan&bulan=<?= urlencode($selectedBulan) ?>&id_siswa=<?= $student['id_siswa'] ?>"
             class="tab-btn <?= $activeTab === 'perkembangan' ? 'active' : '' ?>">
            Laporan Perkembangan Anak
          </a>
        </div>

        <!-- Filters (Anak & Bulan) -->
        <div class="month-filter" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
          <?php if (!empty($all_students) && count($all_students) > 1): ?>
          <div>
            <label style="font-weight: 800; margin-right: 8px;">Pilih Anak:</label>
            <select onchange="window.location.href='?tab=<?= urlencode($activeTab) ?>&bulan=<?= urlencode($selectedBulan) ?>&id_siswa='+this.value">
              <?php foreach ($all_students as $s): ?>
                <option value="<?= $s['id_siswa'] ?>" <?= $s['id_siswa'] == $student['id_siswa'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($s['nama_siswa']) ?> - Kelas <?= htmlspecialchars(str_replace('Kelompok ', '', $s['nama_kelas'] ?? '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>

          <div>
            <label style="font-weight: 800; margin-right: 8px;">Bulan:</label>
            <select onchange="window.location.href='?tab=<?= urlencode($activeTab) ?>&id_siswa=<?= $student['id_siswa'] ?>&bulan='+this.value">
              <?php
              $bulan_options = [
                  'Januari 2026', 'Februari 2026', 'Maret 2026', 'April 2026',
                  'Mei 2026', 'Juni 2026', 'Juli 2026', 'Agustus 2026',
                  'September 2026', 'Oktober 2026', 'November 2026', 'Desember 2026'
              ];
              foreach ($bulan_options as $opt):
              ?>
                <option value="<?= htmlspecialchars($opt) ?>" <?= $opt === $selectedBulan ? 'selected' : '' ?>>
                  <?= htmlspecialchars($opt) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- ============================
             TAB: LAPORAN ABSENSI
             ============================ -->
        <?php if ($activeTab === 'absensi'): ?>

          <div class="section-heading">Rekap Absensi</div>
          <div class="rekap-grid">
            <div class="rekap-item">
              <div class="rekap-label">Hadir</div>
              <div class="rekap-value" style="color:#16a34a;"><?= $rekap_absensi['hadir'] ?></div>
            </div>
            <div class="rekap-item">
              <div class="rekap-label">Izin</div>
              <div class="rekap-value" style="color:#d97706;"><?= $rekap_absensi['izin'] ?></div>
            </div>
            <div class="rekap-item">
              <div class="rekap-label">Sakit</div>
              <div class="rekap-value" style="color:#dc2626;"><?= $rekap_absensi['sakit'] ?></div>
            </div>
            <div class="rekap-item">
              <div class="rekap-label">Alpha</div>
              <div class="rekap-value" style="color:#4338ca;"><?= $rekap_absensi['alpha'] ?></div>
            </div>
          </div>

          <div class="section-heading">Detail Absensi</div>
          <div style="overflow-x:auto;">
            <table class="data-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Tanggal</th>
                  <th>Hari</th>
                  <th>Status</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($detail_absensi)): ?>
                  <tr><td colspan="5" style="text-align:center; color:#94a3b8; padding: 20px;">Belum ada jadwal sekolah untuk bulan ini.</td></tr>
                <?php else: ?>
                  <?php foreach ($detail_absensi as $row): ?>
                  <tr>
                    <td><?= $row['no'] ?></td>
                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                    <td><?= htmlspecialchars($row['hari']) ?></td>
                    <td><?= statusBadge($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
           <?php endif; ?>

        <!-- ============================
             TAB: AKTIVITAS HARIAN
             ============================ -->
        <?php if ($activeTab === 'aktivitas'): ?>

          <div class="section-heading">Laporan Aktivitas Harian</div>
          <div style="overflow-x:auto;">
            <table class="data-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Tanggal</th>
                  <th>Hari</th>
                  <th>Kategori</th>
                  <th>Guru</th>
                  <th>Dokumentasi</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($aktivitas_harian)): ?>
                  <tr><td colspan="6" style="text-align:center; color:#94a3b8; padding: 20px;">Belum ada laporan aktivitas untuk bulan ini.</td></tr>
                <?php else: ?>
                  <?php foreach ($aktivitas_harian as $row): ?>
                  <tr>
                    <td><?= $row['no'] ?></td>
                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                    <td><?= htmlspecialchars($row['hari']) ?></td>
                    <td>
                      <span class="badge <?= htmlspecialchars($row['kategori_class']) ?>">
                        <?= htmlspecialchars($row['kategori']) ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars($row['guru']) ?></td>
                    <td>
                      <?php if (!empty($row['dokumentasi'])): ?>
                        <a href="javascript:void(0);" onclick="openModal('<?= htmlspecialchars($row['dokumentasi']) ?>')" style="color:#3b82f6; font-weight:700;">
                          <i class="fas fa-image"></i> Lihat
                        </a>
                      <?php else: ?>
                        <span style="color:#94a3b8; font-size:0.75rem;">-</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

        <?php endif; ?>

        <!-- ============================
             TAB: PERKEMBANGAN ANAK
             ============================ -->
        <?php if ($activeTab === 'perkembangan'): ?>

          <div class="section-heading">Laporan Perkembangan Anak</div>
          <?php if(empty($perkembangan)): ?>
            <p style="color:#64748b; font-weight:600;">Belum ada catatan perkembangan untuk bulan ini.</p>
          <?php else: ?>
            <?php foreach ($perkembangan as $item): ?>
            <div class="dev-aspect-card <?= htmlspecialchars($item['card_class']) ?>">
              <h4><?= $item['icon'] ?> <?= htmlspecialchars($item['aspek']) ?></h4>
              <p><?= $item['deskripsi'] ?></p>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>

        <?php endif; ?>

      </div><!-- .content-card -->
    </main>
  </div><!-- .layout-container -->

<!-- Modal for Image Viewing -->
<div id="imageModal" class="modal" onclick="closeModal(event)">
  <span class="close" onclick="closeModal(event)">&times;</span>
  <img class="modal-content" id="modalImage">
</div>

<style>
/* Modal Styles */
.modal {
  display: none; 
  position: fixed; 
  z-index: 9999; 
  left: 0;
  top: 0;
  width: 100%; 
  height: 100%; 
  background-color: rgba(0,0,0,0.8); 
  align-items: center;
  justify-content: center;
}
.modal-content {
  max-width: 90%;
  max-height: 85vh;
  object-fit: contain;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}
.modal .close {
  position: absolute;
  top: 15px;
  right: 35px;
  color: #f1f1f1;
  font-size: 40px;
  font-weight: bold;
  transition: 0.3s;
  cursor: pointer;
}
.modal .close:hover,
.modal .close:focus {
  color: #bbb;
  text-decoration: none;
  cursor: pointer;
}
</style>

<script>
function openModal(imageSrc) {
  var modal = document.getElementById("imageModal");
  var modalImg = document.getElementById("modalImage");
  modal.style.display = "flex";
  modalImg.src = imageSrc;
}

function closeModal(event) {
  if (event.target.id === "imageModal" || event.target.className === "close") {
    var modal = document.getElementById("imageModal");
    modal.style.display = "none";
    document.getElementById("modalImage").src = ""; // Clear src
  }
}
</script>

<?php include '../../../App/Layout/footer.php'; ?>
