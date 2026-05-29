<?php
session_start();
require_once __DIR__ . '/../../../App/Config/Database.php';

$pdo = \App\Config\Database::connect();

// Get ID Guru
$id_guru = 1; // Default fallback
if (isset($_SESSION['user_id'])) {
    $stmt_guru = $pdo->prepare("SELECT id_guru FROM guru WHERE id_user = ?");
    $stmt_guru->execute([$_SESSION['user_id']]);
    $guru = $stmt_guru->fetch();
    if ($guru) {
        $id_guru = $guru['id_guru'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $status_array = $_POST['status'] ?? [];
    
    // Check if it's a single row save
    if (isset($_POST['single_save'])) {
        $id_siswa = $_POST['single_save'];
        if (isset($status_array[$id_siswa])) {
            $status = $status_array[$id_siswa];
            $stmt_check = $pdo->prepare("SELECT id_absensi FROM absensi WHERE id_siswa = ? AND tanggal = ?");
            $stmt_check->execute([$id_siswa, $tanggal]);
            if ($stmt_check->rowCount() > 0) {
                $stmt_update = $pdo->prepare("UPDATE absensi SET status = ?, id_guru = ? WHERE id_siswa = ? AND tanggal = ?");
                $stmt_update->execute([$status, $id_guru, $id_siswa, $tanggal]);
            } else {
                $stmt_insert = $pdo->prepare("INSERT INTO absensi (id_siswa, id_guru, tanggal, status) VALUES (?, ?, ?, ?)");
                $stmt_insert->execute([$id_siswa, $id_guru, $tanggal, $status]);
            }
        }
        $_SESSION['flash_success'] = "Absensi baris berhasil disimpan! ✓";
    } else {
        // Bulk save
        foreach($status_array as $id_siswa => $status) {
            $stmt_check = $pdo->prepare("SELECT id_absensi FROM absensi WHERE id_siswa = ? AND tanggal = ?");
            $stmt_check->execute([$id_siswa, $tanggal]);
            if ($stmt_check->rowCount() > 0) {
                $stmt_update = $pdo->prepare("UPDATE absensi SET status = ?, id_guru = ? WHERE id_siswa = ? AND tanggal = ?");
                $stmt_update->execute([$status, $id_guru, $id_siswa, $tanggal]);
            } else {
                $stmt_insert = $pdo->prepare("INSERT INTO absensi (id_siswa, id_guru, tanggal, status) VALUES (?, ?, ?, ?)");
                $stmt_insert->execute([$id_siswa, $id_guru, $tanggal, $status]);
            }
        }
        $_SESSION['flash_success'] = "Semua Absensi Berhasil disimpan! ✓";
    }
    
    header("Location: absensi.php?date=" . urlencode($tanggal));
    exit;
}

// Get date from parameter or use today
$current_date = $_GET['date'] ?? date('Y-m-d');

// Translate date to Indonesian format
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$date_parts = explode('-', $current_date);
$display_date_text = (int)$date_parts[2] . ' ' . $months[(int)$date_parts[1]] . ' ' . $date_parts[0];

// Fetch students and their attendance for the chosen date
$query = "
    SELECT m.id_siswa, m.nama_siswa, a.status 
    FROM murid m 
    LEFT JOIN absensi a ON m.id_siswa = a.id_siswa AND a.tanggal = :tanggal
    ORDER BY m.nama_siswa ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute(['tanggal' => $current_date]);
$students = $stmt->fetchAll();

// Calculate attendance summary
$hadir = count(array_filter($students, fn($s) => $s['status'] === 'Hadir'));
$izin = count(array_filter($students, fn($s) => $s['status'] === 'Izin'));
$sakit = count(array_filter($students, fn($s) => $s['status'] === 'Sakit'));
$alpa = count(array_filter($students, fn($s) => $s['status'] === 'Alpa'));

include '../../../App/Layout/header.php';
?>

<style>
    .absensi-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        font-family: 'Nunito', sans-serif;
    }
    
    .page-title {
        color: #2563eb;
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .date-section {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .date-input-wrapper {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 15px;
        display: inline-flex;
        align-items: center;
        background: white;
    }
    
    .date-input-wrapper input[type="date"] {
        border: none;
        outline: none;
        font-family: 'Nunito', sans-serif;
        font-weight: 600;
        color: #334155;
        background: transparent;
    }
    
    .date-text {
        color: #3b82f6;
        font-weight: 700;
        font-size: 0.95rem;
    }
    
    .summary-pills {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .pill {
        padding: 8px 25px;
        border-radius: 20px;
        font-weight: 800;
        font-size: 0.9rem;
    }
    
    .pill.hadir { background: #dcfce7; color: #16a34a; }
    .pill.izin { background: #fef3c7; color: #d97706; }
    .pill.sakit { background: #fee2e2; color: #dc2626; }
    .pill.alpa { background: #e0f2fe; color: #0284c7; }
    
    .absensi-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    
    .absensi-table th {
        background: #f0f9ff;
        color: #1e293b;
        font-weight: 800;
        padding: 12px 15px;
        text-align: left;
        border-bottom: 2px solid #e2e8f0;
        border-top: 1px solid #e2e8f0;
    }
    
    .absensi-table th:first-child { border-top-left-radius: 8px; border-left: 1px solid #e2e8f0; }
    .absensi-table th:last-child { border-top-right-radius: 8px; border-right: 1px solid #e2e8f0; }
    
    .absensi-table td {
        padding: 15px;
        border-bottom: 1px solid #e2e8f0;
        color: #0f172a;
        font-weight: 700;
        font-size: 0.9rem;
        border-left: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
    }
    
    .radio-group {
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .radio-label {
        display: inline-block;
        padding: 6px 20px;
        border-radius: 20px;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 800;
        color: #475569;
        transition: all 0.2s;
        background: white;
    }
    
    input[type="radio"] {
        display: none;
    }
    
    input[type="radio"]:checked + .radio-label.hadir { background: #22c55e; color: white; border-color: #22c55e; }
    input[type="radio"]:checked + .radio-label.izin { background: #fbbf24; color: white; border-color: #fbbf24; }
    input[type="radio"]:checked + .radio-label.sakit { background: #ef4444; color: white; border-color: #ef4444; }
    input[type="radio"]:checked + .radio-label.alpa { background: #94a3b8; color: white; border-color: #94a3b8; }
    
    .btn-simpan {
        background: #60a5fa;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 8px 15px;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        font-family: 'Nunito', sans-serif;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-simpan-semua {
        background: #60a5fa;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 800;
        font-size: 0.95rem;
        cursor: pointer;
        font-family: 'Nunito', sans-serif;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .bottom-bar {
        border-top: 1px solid #e2e8f0;
        padding-top: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .bottom-text {
        color: #64748b;
        font-size: 0.9rem;
    }
    
    .toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #22c55e;
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        font-weight: 700;
        box-shadow: 0 10px 25px rgba(34, 197, 94, 0.4);
        z-index: 9999;
        animation: slideIn 0.3s ease-out forwards;
        font-family: 'Nunito', sans-serif;
    }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
</style>

<div class="page-wrapper">
    <div class="layout-container">
        <?php include '../../../App/Layout/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="absensi-card">
                <div class="page-title">
                    📝 Absensi Siswa
                </div>
                
                <form id="dateForm" method="GET" action="absensi.php">
                    <div class="date-section">
                        <div class="date-input-wrapper">
                            <input type="date" name="date" id="dateInput" value="<?php echo htmlspecialchars($current_date); ?>" onchange="document.getElementById('dateForm').submit();">
                        </div>
                        <div class="date-text">
                            <?php echo $display_date_text; ?>
                        </div>
                    </div>
                </form>
                
                <div class="summary-pills">
                    <div class="pill hadir">Hadir : <?php echo $hadir; ?></div>
                    <div class="pill izin">Izin : <?php echo $izin; ?></div>
                    <div class="pill sakit">Sakit : <?php echo $sakit; ?></div>
                    <div class="pill alpa">Alpa : <?php echo $alpa; ?></div>
                </div>
                
                <form method="POST" action="absensi.php">
                    <input type="hidden" name="tanggal" value="<?php echo htmlspecialchars($current_date); ?>">
                    
                    <div style="overflow-x: auto;">
                        <table class="absensi-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Nama</th>
                                    <th style="text-align: center;">Status Kehadiran</th>
                                    <th style="width: 120px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($students)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #94a3b8;">Tidak ada data murid.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach($students as $student): ?>
                                        <tr>
                                            <td style="text-align: center;"><?php echo $no++; ?>.</td>
                                            <td><?php echo htmlspecialchars($student['nama_siswa']); ?></td>
                                            <td>
                                                <div class="radio-group">
                                                    <label>
                                                        <input type="radio" name="status[<?php echo $student['id_siswa']; ?>]" value="Hadir" <?php echo ($student['status'] === 'Hadir') ? 'checked' : ''; ?>>
                                                        <span class="radio-label hadir">Hadir</span>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="status[<?php echo $student['id_siswa']; ?>]" value="Izin" <?php echo ($student['status'] === 'Izin') ? 'checked' : ''; ?>>
                                                        <span class="radio-label izin">Izin</span>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="status[<?php echo $student['id_siswa']; ?>]" value="Sakit" <?php echo ($student['status'] === 'Sakit') ? 'checked' : ''; ?>>
                                                        <span class="radio-label sakit">Sakit</span>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="status[<?php echo $student['id_siswa']; ?>]" value="Alpa" <?php echo ($student['status'] === 'Alpa') ? 'checked' : ''; ?>>
                                                        <span class="radio-label alpa">Alpa</span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <button type="submit" name="single_save" value="<?php echo $student['id_siswa']; ?>" class="btn-simpan">
                                                    💾 Simpan
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="bottom-bar">
                        <div class="bottom-text">
                            <span style="font-size: 1.2rem; vertical-align: middle;">🖱️</span> Klik <strong>Simpan</strong> per baris atau simpan semua sekaligus
                        </div>
                        <button type="submit" name="bulk_save" value="1" class="btn-simpan-semua">
                            💾 Simpan Semua
                        </button>
                    </div>
                </form>
                
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="toast" id="toast">
        <?php 
            echo htmlspecialchars($_SESSION['flash_success']); 
            unset($_SESSION['flash_success']);
        ?>
    </div>
    <script>
        setTimeout(function() {
            var toast = document.getElementById('toast');
            if(toast) {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.5s';
                setTimeout(function(){ toast.remove(); }, 500);
            }
        }, 3000);
    </script>
<?php endif; ?>

<?php include '../../../App/Layout/footer.php'; ?>