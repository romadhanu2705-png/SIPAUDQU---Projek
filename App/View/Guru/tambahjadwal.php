<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . '/../../../../App/Config/Database.php';

$error = '';
$success = '';

try {
    $pdo = \App\Config\Database::connect();
} catch (\Exception $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tema = trim($_POST['tema'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? '');
    $hari = trim($_POST['hari'] ?? '');
    $halaman = trim($_POST['halaman'] ?? '');
    $kegiatan = trim($_POST['kegiatan'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (empty($tema) || empty($tanggal) || empty($kegiatan)) {
        $error = "Tema, Tanggal, dan Jenis Kegiatan wajib diisi.";
    } else {
        try {
            // Dapatkan id_guru dari user_id session jika ada
            $id_user = $_SESSION['user_id'] ?? null;
            $id_guru = 1; // Default
            if ($id_user) {
                $stmt_guru = $pdo->prepare("SELECT id_guru FROM guru WHERE id_user = :uid LIMIT 1");
                $stmt_guru->execute(['uid' => $id_user]);
                $guru_row = $stmt_guru->fetch();
                if ($guru_row) {
                    $id_guru = $guru_row['id_guru'];
                }
            }

            // Jika hari kosong, coba deteksi nama hari dari tanggal
            if (empty($hari)) {
                $day_index = date('N', strtotime($tanggal));
                $hari_list = [
                    1 => 'Senin',
                    2 => 'Selasa',
                    3 => 'Rabu',
                    4 => 'Kamis',
                    5 => 'Jumat',
                    6 => 'Sabtu',
                    7 => 'Minggu'
                ];
                $hari = $hari_list[$day_index] ?? '';
            }
