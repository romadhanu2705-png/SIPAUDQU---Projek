<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . '/../../../../App/Config/Database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_guru = trim($_POST['nama_guru'] ?? '');
    $no_hp     = trim($_POST['no_hp'] ?? '');
    $status    = $_POST['status'] ?? 'Aktif';
    $kata_sandi = trim($_POST['kata_sandi'] ?? '');
    $peran     = $_POST['peran'] ?? 'Guru';

    if (empty($nama_guru)) {
        $error = "Nama Guru wajib diisi.";
    } elseif (empty($kata_sandi)) {
        $error = "Kata Sandi wajib diisi.";
    } else {
        try {
            $pdo = \App\Config\Database::connect();
            $pdo->beginTransaction();

            // Insert ke tabel pengguna
            $stmt_p = $pdo->prepare("INSERT INTO pengguna (nama, kata_sandi, peran) VALUES (:nama, :kata_sandi, :peran)");
            $stmt_p->execute([
                'nama'       => $nama_guru,
                'kata_sandi' => $kata_sandi,
                'peran'      => $peran,
            ]);
            $id_user = $pdo->lastInsertId();

            // Insert ke tabel guru
            $stmt_g = $pdo->prepare("INSERT INTO guru (id_user, nama_guru, no_hp, status) VALUES (:id_user, :nama_guru, :no_hp, :status)");
            $stmt_g->execute([
                'id_user'   => $id_user,
                'nama_guru' => $nama_guru,
                'no_hp'     => empty($no_hp) ? null : $no_hp,
                'status'    => $status,
            ]);

            $pdo->commit();
            $_SESSION['flash_success'] = "Data guru berhasil ditambahkan.";
            header("Location: ../dataguru.php");
            exit;
        } catch (\Exception $e) {
            $pdo->rollBack();
            $error = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}
