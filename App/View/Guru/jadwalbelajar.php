<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../../../App/Config/Database.php';

$pdo = \App\Config\Database::connect();

$current_date = $_GET['bulan'] ?? date('Y-m'); 
$current_tema = $_GET['tema'] ?? 'Binatang';

$query = "
    SELECT id_jadwal, tanggal, hari, halaman, kegiatan, deskripsi, tema
    FROM jadwal_belajar
    WHERE DATE_FORMAT(tanggal, '%Y-%m') = :bulan
      AND tema = :tema
    ORDER BY tanggal ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute(['bulan' => $current_date, 'tema' => $current_tema]);
$schedules = $stmt->fetchAll();

$stmt_tema = $pdo->query("SELECT DISTINCT tema FROM jadwal_belajar");
$themes = $stmt_tema->fetchAll(PDO::FETCH_COLUMN);
if(empty($themes)) $themes = ['Binatang', 'Diri Sendiri', 'Keluargaku', 'Lingkungan', 'Alam Semesta'];
if(!in_array($current_tema, $themes)) $themes[] = $current_tema;

function getBadgeClass($kegiatan) {
    $kegiatan = strtolower($kegiatan);
    if (strpos($kegiatan, 'mengenal') !== false) return 'mengenal';
    if (strpos($kegiatan, 'mewarnai') !== false) return 'mewarnai';
    if (strpos($kegiatan, 'menghitung') !== false) return 'menghitung';
    if (strpos($kegiatan, 'motorik') !== false) return 'motorik';
    if (strpos($kegiatan, 'bermain') !== false) return 'bermain';
    return 'default-badge';
}

include __DIR__ . '/../../../App/Layout/header.php';
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
        font-size: 1.4rem;
        font-weight: 800;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .filter-section {
        display: flex;
        align-items: flex-end;
        gap: 20px;
        margin-bottom: 30px;
        margin-top: 25px;
    }
    
    .filter-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .filter-label {
        font-size: 0.85rem;
        font-weight: 800;
        color: #0f172a;
        margin-left: 15px;
    }
    
    .filter-input-wrapper {
        border: 1px solid #475569;
        border-radius: 20px;
        padding: 8px 18px;
        display: inline-flex;
        align-items: center;
        background: white;
        height: 40px;
        box-sizing: border-box;
    }
    
    .filter-input-wrapper input[type="month"], .filter-input-wrapper select {
        border: none;
        outline: none;
        font-family: 'Nunito', sans-serif;
        font-weight: 700;
        color: #475569;
        background: transparent;
        font-size: 0.9rem;
    }
    
    .jadwal-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #cbd5e1;
    }
    
    .jadwal-table th {
        background: #e0f2fe;
        color: #0f172a;
        font-weight: 800;
        padding: 15px;
        text-align: center;
        border-bottom: 2px solid #cbd5e1;
    }
    
    .jadwal-table td {
        padding: 15px;
        border-bottom: 1px solid #e2e8f0;
        color: #0f172a;
        font-weight: 700;
        font-size: 0.9rem;
        vertical-align: middle;
        border-right: 1px solid #e2e8f0;
    }

    .jadwal-table td:last-child {
        border-right: none;
    }
    
    .badge-pill {
        display: inline-block;
        padding: 6px 20px;
        border-radius: 20px;
        font-weight: 800;
        font-size: 0.8rem;
        text-align: center;
        width: 100px;
    }
    
    .badge-pill.mengenal { background: #fee2e2; color: #ef4444; }
    .badge-pill.mewarnai { background: #f3e8ff; color: #a855f7; }
    .badge-pill.menghitung { background: #fef3c7; color: #f59e0b; }
    .badge-pill.motorik { background: #dcfce7; color: #22c55e; }
    .badge-pill.bermain { background: #e0f2fe; color: #3b82f6; }
    .badge-pill.default-badge { background: #f1f5f9; color: #475569; }
    
    .action-btn {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 4px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .btn-edit { background: #4f46e5; }
    .btn-delete { background: #ef4444; }
    
    .action-icon {
        width: 16px;
        height: 16px;
        stroke: white;
        stroke-width: 2.5;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
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

<div class="layout-container">
        <?php include __DIR__ . '/../../../App/Layout/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="absensi-card">
                <div class="page-title">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    Jadwal Kegiatan Pembelajaran (Berbasis Tema)
                </div>
                <p style="color: #64748b; font-size: 0.9rem; font-weight: 600; margin-left: 40px; margin-top: 0;">
                    Atur jadwal kegiatan belajar sesuai tema dan halaman buku
                </p>
                
                <form id="filterForm" method="GET" action="jadwalbelajar.php">
                    <div class="filter-section">
                        <div class="filter-item">
                            <span class="filter-label">Bulan</span>
                            <div class="filter-input-wrapper">
                                <input type="month" name="bulan" value="<?php echo htmlspecialchars($current_date); ?>" onchange="document.getElementById('filterForm').submit();">
                            </div>
                        </div>
                        <div class="filter-item">
                            <span class="filter-label">Tema</span>
                            <div class="filter-input-wrapper">
                                <select name="tema" onchange="document.getElementById('filterForm').submit();">
                                    <?php foreach($themes as $t): ?>
                                        <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $current_tema === $t ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($t); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>