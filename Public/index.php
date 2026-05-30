<?php
// index.php (formerly login.php)
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['peran']) && in_array($_SESSION['peran'], ['Guru', 'Admin'])) {
        header('Location: App/View/Guru/dashboardguru.php');
    } else {
        header('Location: App/View/Wali murid/dashboard.php');
    }
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'register') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $nama_wali = trim($_POST['nama_wali'] ?? '');

        if (empty($username) || empty($password) || empty($nama_wali)) {
            $error = 'Semua field pendaftaran wajib diisi!';
        } else {
            require_once _DIR_ . '/App/Config/Database.php';
            try {
                $pdo = \App\Config\Database::connect();
                
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM pengguna WHERE nama = :username");
                $stmt->execute(['username' => $username]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Username sudah terdaftar! Gunakan username lain.';
                } else {
                    $pdo->beginTransaction()
                    
                    // Insert into pengguna
                    $stmt_u = $pdo->prepare("INSERT INTO pengguna (nama, kata_sandi, peran) VALUES (:nama, :pass, 'Wali_Murid')");
                    $stmt_u->execute([
                        'nama' => $username,
                        'pass' => $password
                    ]);
                    $new_uid = $pdo->lastInsertId();
                    
                    // Insert into wali_murid
                    $stmt_w = $pdo->prepare("INSERT INTO wali_murid (id_user, nama_wali) VALUES (:uid, :nama)");
                    $stmt_w->execute([
                        'uid' => $new_uid,
                        'nama' => $nama_wali
                    ]);
                    
                    $pdo->commit();
                    $success = 'Pendaftaran berhasil! Silakan login menggunakan akun Anda.';
                }
            } catch (\Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = 'Gagal mendaftar: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'forgot') {
        $username = trim($_POST['username'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');

        if (empty($username) || empty($new_password)) {
            $error = 'Username dan Password baru wajib diisi!';
        } else {
            require_once _DIR_ . '/App/Config/Database.php';
            try {
                $pdo = \App\Config\Database::connect();
                
                // Check if username exists
                $stmt = $pdo->prepare("SELECT id_user FROM pengguna WHERE nama = :username LIMIT 1");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    $error = 'Username tidak ditemukan!';
                } else {
                    // Update password
                    $stmt_up = $pdo->prepare("UPDATE pengguna SET kata_sandi = :pass WHERE id_user = :uid");
                    $stmt_up->execute([
                        'pass' => $new_password,
                        'uid' => $user['id_user']
                    ]);
                    $success = 'Password berhasil diperbarui! Silakan login.';
                }
            } catch (\Exception $e) {
                $error = 'Gagal mereset password: ' . $e->getMessage();
            }
        }
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = 'Username dan password wajib diisi!';
        } else {
            require_once _DIR_ . '/App/Config/Database.php';
            try {
                $pdo = \App\Config\Database::connect();

                $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE nama = :username AND kata_sandi = :password LIMIT 1");
                $stmt->execute([
                    'username' => $username,
                    'password' => $password
                ]);
                $user = $stmt->fetch();

                if ($user) {
                    $_SESSION['user_id']  = $user['id_user'];
                    $_SESSION['username'] = $user['nama'];
                    $_SESSION['peran']    = $user['peran'];

                    if ($user['peran'] === 'Wali_Murid') {
                        // Ambil id_wali dan nama_wali, simpan ke session
                        $stmtW = $pdo->prepare("SELECT id_wali, nama_wali FROM wali_murid WHERE id_user = :uid LIMIT 1");
                        $stmtW->execute(['uid' => $user['id_user']]);
                        $wali = $stmtW->fetch();
                        if ($wali) {
                            $_SESSION['id_wali']   = $wali['id_wali'];
                            $_SESSION['nama_wali'] = $wali['nama_wali'];
                        }
                        header('Location: App/View/Wali murid/dashboard.php');
                        exit;
                    }

                    if ($user['peran'] === 'Guru' || $user['peran'] === 'Admin') {
                        header('Location: App/View/Guru/dashboardguru.php');
                        exit;
                    }

                    header('Location: App/View/Wali murid/dashboard.php');
                    exit;

                } else {
                    $error = 'Username atau password salah. Silakan coba lagi.';
                }
            } catch (\Exception $e) {
                $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
            }
        }
    }
}
?>