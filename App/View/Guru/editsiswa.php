<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . '/../../../../App/Config/Database.php';

$error = '';
$success = '';

$id_siswa = $_GET['id'] ?? null;
if (!$id_siswa) {
    die("ID Siswa tidak ditemukan.");
}

try {
    $pdo = \App\Config\Database::connect();
    
    // Fetch class list for dropdown
    $stmt_kelas = $pdo->query("SELECT id_kelas, nama_kelas FROM kelas ORDER BY nama_kelas ASC");
    $kelas_list = $stmt_kelas->fetchAll();
    
    // Fetch current student data including wali
    $stmt_student = $pdo->prepare("
        SELECT m.*, w.nama_wali 
        FROM murid m 
        LEFT JOIN wali_murid w ON m.id_wali = w.id_wali 
        WHERE m.id_siswa = :id
    ");
    $stmt_student->execute(['id' => $id_siswa]);
    $student = $stmt_student->fetch();
    
    if (!$student) {
        die("Data siswa tidak ditemukan.");
    }

} catch (\Exception $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama_siswa'] ?? '';
    $nis = $_POST['nis'] ?? '';
    $id_kelas = $_POST['id_kelas'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $nama_wali = $_POST['nama_wali'] ?? '';

    if (empty($nama) || empty($jenis_kelamin)) {
        $error = "Nama dan Jenis Kelamin wajib diisi.";
    } else {
        try {
            $pdo->beginTransaction();

            $id_wali = $student['id_wali'];
            if (!empty($nama_wali)) {
                $stmt_wali = $pdo->prepare("SELECT id_wali FROM wali_murid WHERE nama_wali = :nama LIMIT 1");
                $stmt_wali->execute(['nama' => $nama_wali]);
                $wali = $stmt_wali->fetch();
                if ($wali) {
                    $id_wali = $wali['id_wali'];
                } else {
                    $stmt_ins_wali = $pdo->prepare("INSERT INTO wali_murid (nama_wali) VALUES (:nama)");
                    $stmt_ins_wali->execute(['nama' => $nama_wali]);
                    $id_wali = $pdo->lastInsertId();
                }
            } else {
                $id_wali = NULL;
            }

            $query = "UPDATE murid SET 
                      id_kelas = :id_kelas,
                      id_wali = :id_wali,
                      nama_siswa = :nama_siswa, 
                      nis = :nis, 
                      jenis_kelamin = :jenis_kelamin, 
                      tanggal_lahir = :tanggal_lahir, 
                      alamat = :alamat
                      WHERE id_siswa = :id_siswa";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'id_kelas' => empty($id_kelas) ? NULL : $id_kelas,
                'id_wali' => $id_wali,
                'nama_siswa' => $nama,
                'nis' => empty($nis) ? NULL : $nis,
                'jenis_kelamin' => $jenis_kelamin,
                'tanggal_lahir' => empty($tanggal_lahir) ? NULL : $tanggal_lahir,
                'alamat' => empty($alamat) ? NULL : $alamat,
                'id_siswa' => $id_siswa
            ]);
            
            $pdo->commit();
            $_SESSION['flash_success'] = "Data siswa berhasil diperbarui.";
            header("Location: ../datasiswa.php");
            exit;
        } catch (\Exception $e) {
            $pdo->rollBack();
            $error = "Gagal memperbarui data: " . $e->getMessage();
        }
    }
}
