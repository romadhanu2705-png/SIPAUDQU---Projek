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