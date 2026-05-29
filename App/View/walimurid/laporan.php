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
