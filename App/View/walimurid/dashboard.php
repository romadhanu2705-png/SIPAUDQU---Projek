<?php
// dashboard.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../../Config/Database.php';

$pageTitle   = 'Dashboard';
$currentPage = 'dashboard';

$pdo = \App\Config\Database::connect();

// ── Ambil data siswa berdasarkan id_wali dari session ──────────────────────
$all_students = [];
$nama_wali    = $_SESSION['nama_wali'] ?? '';
$id_wali      = $_SESSION['id_wali']   ?? null;

if ($id_wali) {
    // Wali punya id_wali → ambil semua siswa yang terhubung
    $stmtMurid = $pdo->prepare("
        SELECT m.*, k.nama_kelas
        FROM murid m
        LEFT JOIN kelas k ON m.id_kelas = k.id_kelas
        WHERE m.id_wali = :id_wali
        ORDER BY m.nama_siswa ASC
    ");
    $stmtMurid->execute(['id_wali' => $id_wali]);
    $all_students = $stmtMurid->fetchAll();

    // Jika nama_wali belum ada di session, ambil dari DB
    if (empty($nama_wali)) {
        $stmtW = $pdo->prepare("SELECT nama_wali FROM wali_murid WHERE id_wali = :id_wali LIMIT 1");
        $stmtW->execute(['id_wali' => $id_wali]);
        $w = $stmtW->fetch();
        $nama_wali = $w['nama_wali'] ?? '';
    }
} else {
    // Fallback: cari berdasarkan id_user di wali_murid
    $stmtWali = $pdo->prepare("SELECT * FROM wali_murid WHERE id_user = :uid LIMIT 1");
    $stmtWali->execute(['uid' => $_SESSION['user_id']]);
    $wali = $stmtWali->fetch();
    if ($wali) {
        $nama_wali = $wali['nama_wali'];
        $id_wali   = $wali['id_wali'];
        $_SESSION['id_wali']   = $id_wali;
        $_SESSION['nama_wali'] = $nama_wali;

        $stmtMurid = $pdo->prepare("
            SELECT m.*, k.nama_kelas
            FROM murid m
            LEFT JOIN kelas k ON m.id_kelas = k.id_kelas
            WHERE m.id_wali = :id_wali
            ORDER BY m.nama_siswa ASC
        ");
        $stmtMurid->execute(['id_wali' => $id_wali]);
        $all_students = $stmtMurid->fetchAll();
    }
}

// ── Pilih siswa aktif ───────────────────────────────────────────────────────
$student_row = null;
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
    'id_siswa'      => $student_row['id_siswa']      ?? 0,
    'nama'          => $student_row['nama_siswa']     ?? 'Tidak Ada Data Siswa',
    'nis'           => $student_row['nis']            ?? '-',
    'tanggal_lahir' => isset($student_row['tanggal_lahir'])
                        ? date('d F Y', strtotime($student_row['tanggal_lahir'])) : '-',
    'jenis_kelamin' => $student_row['jenis_kelamin']  ?? '-',
    'alamat'        => $student_row['alamat']         ?? '-',
    'orangtua'      => $nama_wali                     ?: '-',
    'kelas'         => str_replace('Kelas ', '', $student_row['nama_kelas'] ?? '-'),
];

$studentName = $student['nama'];

$db_bulan = date('Y-m');
$bulan_nama = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$current_month_name = $bulan_nama[date('m')];

// Data Absensi
$stmt_abs = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN a.status = 'Alpa' OR a.status = 'Alpha' THEN 1 ELSE 0 END) as alpha,
        SUM(CASE WHEN a.status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
        SUM(CASE WHEN a.status = 'Izin' THEN 1 ELSE 0 END) as izin
    FROM jadwal_belajar j
    LEFT JOIN absensi a ON j.tanggal = a.tanggal AND a.id_siswa = :id_siswa
    WHERE DATE_FORMAT(j.tanggal, '%Y-%m') = :bulan
");
$stmt_abs->execute(['id_siswa' => $student['id_siswa'], 'bulan' => $db_bulan]);
$abs = $stmt_abs->fetch();

$absensi_bulan = [
    'bulan' => $current_month_name,
    'hadir' => $abs['hadir'] ?? 0,
    'alpha' => $abs['alpha'] ?? 0,
    'sakit' => $abs['sakit'] ?? 0,
    'izin'  => $abs['izin'] ?? 0,
];

// Aktivitas Terakhir
$stmt_akt = $pdo->prepare("
    SELECT a.tanggal, a.jenis_kegiatan as kegiatan, a.kategori, g.nama_guru 
    FROM aktivitas a 
    LEFT JOIN guru g ON a.id_guru = g.id_guru 
    WHERE a.id_siswa = :id_siswa 
    ORDER BY a.tanggal DESC LIMIT 1
");
$stmt_akt->execute(['id_siswa' => $student['id_siswa']]);
$akt = $stmt_akt->fetch();

if ($akt) {
    $tgl_akt = strtotime($akt['tanggal']);
    $aktivitas_terakhir = [
        'tanggal'  => date('d', $tgl_akt),
        'bulan'    => $bulan_nama[date('m', $tgl_akt)],
        'kegiatan' => $akt['kategori'] ?: $akt['kegiatan'],
        'deskripsi'=> $akt['kegiatan'],
        'guru'     => $akt['nama_guru'] ? 'Bu ' . strtok($akt['nama_guru'], ' ') : '-',
    ];
} else {
    $aktivitas_terakhir = [
        'tanggal'  => '-',
        'bulan'    => '-',
        'kegiatan' => '-',
        'deskripsi'=> 'Belum ada aktivitas',
        'guru'     => '-',
    ];
}

// Perkembangan Terbaru
$stmt_perk = $pdo->prepare("
    SELECT p.bulan, p.aspek, p.catatan, g.nama_guru 
    FROM perkembangan_anak p 
    LEFT JOIN guru g ON p.id_guru = g.id_guru 
    WHERE p.id_siswa = :id_siswa 
    ORDER BY p.id_perkembangan DESC LIMIT 1
");
$stmt_perk->execute(['id_siswa' => $student['id_siswa']]);
$perk = $stmt_perk->fetch();

if ($perk) {
    $parts = explode(' ', $perk['bulan']);
    $bulan_perk = $parts[0] ?? '-';
    $tahun_perk = $parts[1] ?? '-';
    
    $perkembangan_terbaru = [
        'bulan'  => $bulan_perk,
        'tahun'  => $tahun_perk,
        'aspek'  => 'Aspek ' . $perk['aspek'],
        'catatan'=> $perk['catatan'],
        'guru'   => $perk['nama_guru'] ? 'Bu ' . strtok($perk['nama_guru'], ' ') : '-',
    ];
} else {
    $perkembangan_terbaru = [
        'bulan'  => '-',
        'tahun'  => '-',
        'aspek'  => '-',
        'catatan'=> 'Belum ada catatan perkembangan',
        'guru'   => '-',
    ];
}

include '../../../App/Layout/headers.php';
?>

<!-- MAIN LAYOUT -->
<div class="layout-container">
  <?php include '../../../App/Layout/sidebars.php'; ?>

    <main class="main-content">
      <div class="content-card">

        <!-- Welcome Banner -->
        <div class="welcome-banner">
          <h3>🎉 Selamat datang, Bapak/Ibu Wali Murid!</h3>
          <p>Berikut adalah ringkasan informasi anak Anda</p>
        </div>

        <?php if (!empty($all_students) && count($all_students) > 1): ?>
        <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <label style="font-weight: 800; color: #1e293b;">Pilih Anak:</label>
            <select onchange="window.location.href='dashboard.php?id_siswa='+this.value" style="padding: 8px; border-radius: 8px; border: 1px solid #cbd5e1;">
              <?php foreach ($all_students as $s): ?>
                <option value="<?= $s['id_siswa'] ?>" <?= $s['id_siswa'] == $student['id_siswa'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($s['nama_siswa']) ?> - Kelas <?= htmlspecialchars(str_replace('Kelompok ', '', $s['nama_kelas'] ?? '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Student Info Box -->
        <div class="student-info-box">
          <?= htmlspecialchars($student['nama']) ?>
          <div class="student-class">Kelompok <?= htmlspecialchars($student['kelas']) ?></div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">

          <!-- Biodata Anak -->
          <div class="biodata-card">
            <h4>🌱 Biodata Anak</h4>
            <table class="biodata-table">
              <tr>
                <td>Nama</td>
                <td>:</td>
                <td><?= htmlspecialchars($student['nama']) ?></td>
              </tr>
              <tr>
                <td>NIS</td>
                <td>:</td>
                <td><?= htmlspecialchars($student['nis']) ?></td>
              </tr>
              <tr>
                <td>Tanggal Lahir</td>
                <td>:</td>
                <td><?= htmlspecialchars($student['tanggal_lahir']) ?></td>
              </tr>
              <tr>
                <td>Jenis Kelamin</td>
                <td>:</td>
                <td><?= htmlspecialchars($student['jenis_kelamin']) ?></td>
              </tr>
              <tr>
                <td>Alamat</td>
                <td>:</td>
                <td><?= htmlspecialchars($student['alamat']) ?></td>
              </tr>
              <tr>
                <td>Orangtua</td>
                <td>:</td>
                <td><?= htmlspecialchars($student['orangtua']) ?></td>
              </tr>
              <tr>
                <td>Kelas</td>
                <td>:</td>
                <td><?= htmlspecialchars($student['kelas']) ?></td>
              </tr>
            </table>
          </div>

          <!-- Ringkasan Absensi -->
          <div class="absensi-card">
            <h4>Ringkasan Absensi Bulan <?= htmlspecialchars($absensi_bulan['bulan']) ?></h4>
            <div class="absensi-grid">
              <div class="absensi-item hadir">
                Hadir : <?= $absensi_bulan['hadir'] ?> Hari
              </div>
              <div class="absensi-item alpha">
                Alpha : <?= $absensi_bulan['alpha'] ?> Hari
              </div>
              <div class="absensi-item sakit">
                Sakit : <?= $absensi_bulan['sakit'] ?> Hari
              </div>
              <div class="absensi-item izin">
                Izin : <?= $absensi_bulan['izin'] ?> Hari
              </div>
            </div>
          </div>

          <!-- Aktivitas Terakhir -->
          <div class="info-card-mini">
            <h4>📅 Aktivitas Terakhir</h4>
            <div class="activity-item">
              <div class="activity-date">
                <span class="day-num"><?= htmlspecialchars($aktivitas_terakhir['tanggal']) ?></span>
                <?= htmlspecialchars($aktivitas_terakhir['bulan']) ?>
              </div>
              <div class="activity-info">
                <h5><?= htmlspecialchars($aktivitas_terakhir['kegiatan']) ?></h5>
                <p><?= htmlspecialchars($aktivitas_terakhir['deskripsi']) ?></p>
                <p class="teacher">Oleh <?= htmlspecialchars($aktivitas_terakhir['guru']) ?></p>
              </div>
            </div>
          </div>

          <!-- Perkembangan Terbaru -->
          <div class="info-card-mini">
            <h4>⭐ Perkembangan Terbaru</h4>
            <div class="dev-item">
              <div class="dev-month"><?= htmlspecialchars($perkembangan_terbaru['bulan']) ?> <?= htmlspecialchars($perkembangan_terbaru['tahun']) ?></div>
              <h5><?= htmlspecialchars($perkembangan_terbaru['aspek']) ?></h5>
              <p><?= htmlspecialchars($perkembangan_terbaru['catatan']) ?></p>
              <p class="teacher" style="margin-top:3px; font-size:0.7rem; color:#94a3b8;">
                Oleh <?= htmlspecialchars($perkembangan_terbaru['guru']) ?>
              </p>
            </div>
          </div>

        </div><!-- .dashboard-grid -->

      </div><!-- .content-card -->
    </main>
  </div><!-- .layout-container -->

<?php include '../../../App/Layout/footer.php'; ?>

