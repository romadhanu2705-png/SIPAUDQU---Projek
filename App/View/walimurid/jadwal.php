<?php
// jadwal.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../Config/Database.php';

$pageTitle   = 'Jadwal Belajar';
$currentPage = 'jadwal';

try {
    $pdo = \App\Config\Database::connect();
    
    // ── Ambil siswa berdasarkan id_wali dari session ──────────────────────────
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

    // Ambil daftar bulan yang tersedia dari database
    $months_query = $pdo->query("SELECT DISTINCT DATE_FORMAT(tanggal, '%Y-%m') as bulan FROM jadwal_belajar ORDER BY tanggal DESC");
    $available_months = $months_query->fetchAll(PDO::FETCH_COLUMN);

    // Ambil daftar tema yang tersedia dari database
    $themes_query = $pdo->query("SELECT DISTINCT tema FROM jadwal_belajar ORDER BY tema ASC");
    $available_themes = $themes_query->fetchAll(PDO::FETCH_COLUMN);

    if (empty($available_months)) {
        $available_months = [date('Y-m')];
    }
    if (empty($available_themes)) {
        $available_themes = ['Binatang'];
    }

    $selected_month = $_GET['bulan'] ?? ($available_months[0] ?? date('Y-m'));
    $selected_tema  = $_GET['tema'] ?? ($available_themes[0] ?? 'Binatang');

    $months_id = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni',
        7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
    ];

    $parts = explode('-', $selected_month);
    $year = $parts[0];
    $month_num = (int)$parts[1];
    $formatted_month_year = $months_id[$month_num] . ' ' . $year;

    $jadwal_info = [
        'tema'  => strtoupper($selected_tema),
        'bulan' => $formatted_month_year,
    ];

    // Ambil data jadwal dari database
    $query = "
        SELECT tanggal, hari, halaman, kegiatan, deskripsi, tema
        FROM jadwal_belajar
        WHERE DATE_FORMAT(tanggal, '%Y-%m') = :bulan
          AND tema = :tema
        ORDER BY tanggal ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['bulan' => $selected_month, 'tema' => $selected_tema]);
    $schedules = $stmt->fetchAll();

    function getBadgeClass($kegiatan) {
        $kegiatan = strtolower($kegiatan);
        if (strpos($kegiatan, 'mengenal') !== false) return 'badge-mengenal';
        if (strpos($kegiatan, 'mewarnai') !== false) return 'badge-mewarnai';
        if (strpos($kegiatan, 'menghitung') !== false) return 'badge-menghitung';
        if (strpos($kegiatan, 'motorik') !== false) return 'badge-motorik';
        if (strpos($kegiatan, 'bermain') !== false) return 'badge-bermain';
        if (strpos($kegiatan, 'outdoor') !== false) return 'badge-outdoor';
        return 'badge-mengenal';
    }

    $jadwal_list = [];
    foreach ($schedules as $row) {
        $day_num = (int)date('d', strtotime($row['tanggal']));
        $m_num = (int)date('m', strtotime($row['tanggal']));
        $m_name = $months_id[$m_num] ?? '';
        
        $jadwal_list[] = [
            'tanggal'     => $day_num,
            'hari'        => $row['hari'] ?? '',
            'bulan'       => $m_name,
            'halaman'     => $row['halaman'],
            'badge'       => $row['kegiatan'],
            'badge_class' => getBadgeClass($row['kegiatan']),
            'deskripsi'   => $row['deskripsi'],
        ];
    }

    // Grouping by week
    $weeks = [1 => [], 2 => [], 3 => [], 4 => [], 5 => []];
    foreach ($jadwal_list as $item) {
        $day = $item['tanggal'];
        if ($day <= 7) {
            $weeks[1][] = $item;
        } elseif ($day <= 14) {
            $weeks[2][] = $item;
        } elseif ($day <= 21) {
            $weeks[3][] = $item;
        } elseif ($day <= 28) {
            $weeks[4][] = $item;
        } else {
            $weeks[5][] = $item;
        }
    }

} catch (\Exception $e) {
    // Fallback jika database bermasalah
    $student = [
        'nama'  => 'Ahmad Fauzan Al Farizi',
        'kelas' => 'A',
    ];
    $jadwal_info = [
        'tema'  => 'BINATANG',
        'bulan' => 'April 2026',
    ];
    $jadwal_list = [];
    $weeks = [1 => [], 2 => [], 3 => [], 4 => [], 5 => []];
}

include '../../../App/Layout/headers.php';
?>

<style>
.week-container {
    margin-bottom: 25px;
    background: #f8fafc;
    border-radius: 16px;
    padding: 20px;
    border: 1px solid #cbd5e1;
}
.week-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: #1e3a8a;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 2px solid #cbd5e1;
    padding-bottom: 8px;
}
.horizontal-cards {
    display: flex;
    gap: 15px;
    overflow-x: auto;
    padding-bottom: 12px;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
/* For Webkit browsers (Chrome, Safari) */
.horizontal-cards::-webkit-scrollbar {
    height: 6px;
}
.horizontal-cards::-webkit-scrollbar-track {
    background: transparent;
}
.horizontal-cards::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.horizontal-cards::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.weekly-card {
    flex: 0 0 240px; /* fixed width for gorgeous horizontal scrolling cards */
    background: white;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}
.weekly-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
}
.weekly-card-header {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: white;
    padding: 10px 15px;
    text-align: center;
}
.weekly-card-header.alt-bg {
    background: linear-gradient(135deg, #10b981, #047857);
}
.weekly-card-day {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    opacity: 0.9;
}
.weekly-card-date {
    font-size: 1.05rem;
    font-weight: 800;
    margin-top: 2px;
}
.weekly-card-body {
    padding: 15px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex-grow: 1;
}
.weekly-card-title {
    font-size: 0.88rem;
    font-weight: 800;
    color: #1e293b;
    margin: 0;
    line-height: 1.3;
}
.weekly-card-badge {
    align-self: flex-start;
    font-size: 0.68rem;
    padding: 3px 8px;
}
.weekly-card-desc {
    font-size: 0.78rem;
    color: #64748b;
    margin: 0;
    line-height: 1.4;
}
</style>

<div class="layout-container">
  <?php include '../../../App/Layout/sidebars.php'; ?>

    <main class="main-content">
      <div class="content-card">

        <!-- Welcome Banner -->
        <div class="welcome-banner">
          <h3>🎉 Selamat datang, Bapak/Ibu Wali Murid!</h3>
          <p>Berikut adalah Jadwal kegiatan belajar anak Anda</p>
        </div>

        <!-- Student Info Box -->
        <div class="student-info-box">
          <?= htmlspecialchars($student['nama']) ?>
          <div class="student-class">Kelompok <?= htmlspecialchars($student['kelas']) ?></div>
        </div>

        <!-- Section Title -->
        <div class="section-heading">
          <i class="fas fa-calendar-alt" style="color:#3b82f6;"></i>
          Jadwal Belajar
        </div>
        <p style="font-size:0.8rem; color:#64748b; margin-bottom:15px;">
          Berikut adalah Jadwal kegiatan belajar anak Anda
        </p>

        <!-- Month & Theme Filter -->
        <div class="month-filter">
          <form id="filterForm" method="GET" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            
            <?php if (!empty($all_students) && count($all_students) > 1): ?>
            <label for="id_siswa" style="font-weight: 800;">Pilih Anak:</label>
            <select name="id_siswa" id="id_siswa" onchange="document.getElementById('filterForm').submit();" style="border: 1px solid #cbd5e1; margin-right: 15px;">
              <?php foreach ($all_students as $s): ?>
                <option value="<?= $s['id_siswa'] ?>" <?= $s['id_siswa'] == $student['id_siswa'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($s['nama_siswa']) ?> - Kelas <?= htmlspecialchars(str_replace('Kelompok ', '', $s['nama_kelas'] ?? '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <label for="bulan" style="font-weight: 800;">Bulan :</label>
            <select name="bulan" id="bulan" onchange="document.getElementById('filterForm').submit();" style="border: 1px solid #cbd5e1;">
              <?php foreach ($available_months as $m_val): 
                $m_parts = explode('-', $m_val);
                $m_year = $m_parts[0];
                $m_month_num = (int)$m_parts[1];
                $m_formatted_name = $months_id[$m_month_num] . ' ' . $m_year;
              ?>
                <option value="<?= htmlspecialchars($m_val) ?>" <?= $m_val === $selected_month ? 'selected' : '' ?>>
                  <?= htmlspecialchars($m_formatted_name) ?>
                </option>
              <?php endforeach; ?>
            </select>

            <label for="tema" style="font-weight: 800; margin-left: 10px;">Tema :</label>
            <select name="tema" id="tema" onchange="document.getElementById('filterForm').submit();" style="border: 1px solid #cbd5e1;">
              <?php foreach ($available_themes as $t_val): ?>
                <option value="<?= htmlspecialchars($t_val) ?>" <?= $t_val === $selected_tema ? 'selected' : '' ?>>
                  <?= htmlspecialchars(ucfirst($t_val)) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>

        <!-- Theme Banner -->
        <div class="theme-banner">
          <i class="fas fa-tag" style="color:#f59e0b;"></i>
          <span>Tema Bulan ini : </span>
          <span class="theme-highlight"><?= htmlspecialchars($jadwal_info['tema']) ?></span>
          <span style="margin-left:15px; color:#64748b;">Bulan : <?= htmlspecialchars($jadwal_info['bulan']) ?></span>
        </div>

        <!-- Schedule List -->
        <div class="schedule-list">
          <?php 
          $has_schedules = false;
          foreach ($weeks as $w_num => $w_items) {
              if (!empty($w_items)) {
                  $has_schedules = true;
                  break;
              }
          }
          
          if (!$has_schedules): ?>
            <div style="text-align: center; padding: 40px 20px; color: #94a3b8; font-weight: 700; background: white; border: 1px dashed #cbd5e1; border-radius: 8px;">
              <i class="fas fa-calendar-times" style="font-size: 2.2rem; margin-bottom: 12px; color: #cbd5e1; display: block;"></i>
              Tidak ada jadwal kegiatan belajar untuk bulan dan tema ini.
            </div>
          <?php else: ?>
            <?php foreach ($weeks as $w_num => $w_items): 
              if (empty($w_items)) continue;
            ?>
              <div class="week-container">
                <div class="week-title">
                  <i class="fas fa-calendar-week" style="color: #2563eb;"></i>
                  Minggu <?= htmlspecialchars($w_num) ?>
                </div>
                <div class="horizontal-cards">
                  <?php foreach ($w_items as $idx => $item): 
                    $header_class = ($idx % 2 === 0) ? '' : 'alt-bg';
                  ?>
                    <div class="weekly-card">
                      <div class="weekly-card-header <?= $header_class ?>">
                        <div class="weekly-card-day"><?= htmlspecialchars($item['hari']) ?></div>
                        <div class="weekly-card-date"><?= htmlspecialchars($item['tanggal']) ?> <?= htmlspecialchars($item['bulan']) ?></div>
                      </div>
                      <div class="weekly-card-body">
                        <h5 class="weekly-card-title"><?= htmlspecialchars($item['halaman']) ?></h5>
                        <span class="badge <?= htmlspecialchars($item['badge_class']) ?> weekly-card-badge"><?= htmlspecialchars($item['badge']) ?></span>
                        <p class="weekly-card-desc"><?= htmlspecialchars($item['deskripsi']) ?></p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      </div><!-- .content-card -->
    </main>
  </div><!-- .layout-container -->

<?php include '../../../App/Layout/footer.php'; ?>
