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

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - SIPAUDQU</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="Public/css/style.css">
  <style>
    .card-footer-links {
      margin-top: 20px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      text-align: center;
      font-size: 0.85rem;
    }
    .card-footer-links a {
      color: #3b82f6;
      text-decoration: none;
      font-weight: 800;
      transition: all 0.2s ease;
      cursor: pointer;
    }
    .card-footer-links a:hover {
      color: #1d4ed8;
      text-decoration: underline;
    }
    .form-section {
      display: none;
    }
    .form-section.active {
      display: block;
      animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .eye-icon {
      padding: 8px;
      margin-left: -5px;
      border-radius: 50%;
      transition: background-color 0.2s;
    }
    .eye-icon:hover {
      background-color: rgba(0, 0, 0, 0.05);
    }
  </style>
</head>
<body>

<!-- Clouds -->
<div class="cloud cloud-1"></div>
<div class="cloud cloud-2"></div>
<div class="cloud cloud-3"></div>

<div class="page-wrapper">
  <div class="login-wrapper">

    <!-- Brand -->
    <div class="login-brand">
      <div class="logo-text">
        <span class="s">S</span><span class="i">I</span><span class="p">P</span><span class="a">A</span><span class="u">U</span><span class="d">D</span><span class="q">Q</span><span class="u2">U</span>
      </div>
      <div class="logo-subtitle">Sistem Informasi PAUD <span>Qur'an</span></div>
    </div>

    <!-- Login Card -->
    <div class="login-card">
      
      <!-- Notifications -->
      <?php if ($error): ?>
        <div class="alert alert-danger" id="alertBox">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success" id="alertBox">
          <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <!-- 1. LOGIN FORM SECTION -->
      <div id="loginSection" class="form-section active">
        <h2>Login</h2>
        <form method="POST" action="index.php">
          <input type="hidden" name="action" value="login">
          <!-- Username -->
          <div class="form-group">
            <div class="input-wrapper">
              <span class="input-icon"><i class="fas fa-user"></i></span>
              <input
                type="text"
                name="username"
                placeholder="Username"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                autocomplete="username"
                required
              >
            </div>
          </div>

          <!-- Password -->
          <div class="form-group">
            <div class="input-wrapper">
              <span class="input-icon"><i class="fas fa-lock"></i></span>
              <input
                type="password"
                name="password"
                id="passwordInput"
                placeholder="Password"
                autocomplete="current-password"
                required
              >
              <span class="eye-icon" onclick="togglePassword('passwordInput', 'eyeIcon')">
                <i class="fas fa-eye" id="eyeIcon"></i>
              </span>
            </div>
          </div>

          <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> Login
          </button>
        </form>
        
        <div class="card-footer-links">
          <a onclick="showSection('forgotSection')">Lupa Password?</a>
          <a onclick="showSection('registerSection')">Belum punya akun? Daftar Sekarang</a>
        </div>
      </div>

      <!-- 2. REGISTER FORM SECTION -->
      <div id="registerSection" class="form-section">
        <h2>Daftar Akun</h2>
        <form method="POST" action="index.php">
          <input type="hidden" name="action" value="register">
          
          <!-- Full Name -->
          <div class="form-group">
            <div class="input-wrapper">
              <span class="input-icon"><i class="fas fa-id-card"></i></span>
              <input
                type="text"
                name="nama_wali"
                placeholder="Nama Lengkap Orang Tua"
                required
              >
            </div>
          </div>

          <!-- Username -->
          <div class="form-group">
            <div class="input-wrapper">
              <span class="input-icon"><i class="fas fa-user"></i></span>
              <input
                type="text"
                name="username"
                placeholder="Username Baru"
                required
              >
            </div>
          </div>

          <!-- Password -->
          <div class="form-group">
            <div class="input-wrapper">
              <span class="input-icon"><i class="fas fa-lock"></i></span>
              <input
                type="password"
                name="password"
                id="registerPasswordInput"
                placeholder="Kata Sandi"
                required
              >
              <span class="eye-icon" onclick="togglePassword('registerPasswordInput', 'registerEyeIcon')">
                <i class="fas fa-eye" id="registerEyeIcon"></i>
              </span>
            </div>
          </div>

          <button type="submit" class="btn-login">
            <i class="fas fa-user-plus"></i> Daftar Akun
          </button>
        </form>
        
        <div class="card-footer-links">
          <a onclick="showSection('loginSection')">Sudah punya akun? Login</a>
        </div>
      </div>

       <!-- 3. FORGOT PASSWORD SECTION -->
      <div id="forgotSection" class="form-section">
        <h2>Reset Password</h2>
        <form method="POST" action="index.php">
          <input type="hidden" name="action" value="forgot">
          
          <!-- Username -->
          <div class="form-group">
            <div class="input-wrapper">
              <span class="input-icon"><i class="fas fa-user"></i></span>
              <input
                type="text"
                name="username"
                placeholder="Username Anda"
                required
              >
            </div>
          </div>

          <!-- New Password -->
          <div class="form-group">
            <div class="input-wrapper">
              <span class="input-icon"><i class="fas fa-key"></i></span>
              <input
                type="password"
                name="new_password"
                id="forgotPasswordInput"
                placeholder="Password Baru"
                required
              >
              <span class="eye-icon" onclick="togglePassword('forgotPasswordInput', 'forgotEyeIcon')">
                <i class="fas fa-eye" id="forgotEyeIcon"></i>
              </span>
            </div>
          </div>

          <button type="submit" class="btn-login">
            <i class="fas fa-save"></i> Perbarui Password
          </button>
        </form>
        
        <div class="card-footer-links">
          <a onclick="showSection('loginSection')">Kembali ke Login</a>
        </div>
      </div>

    </div>

  </div>
</div>

<!-- Footer -->
<footer class="site-footer">
  <p>&copy; <?= date('Y') ?> <strong>SIPAUDQU</strong> &mdash; Sistem Informasi PAUD Qur'an</p>
</footer>

<script>
// Toggle Password Visibility
function togglePassword(inputId, iconId) {
  const input = document.getElementById(inputId);
  const eyeIcon = document.getElementById(iconId);
  if (input.type === 'password') {
    input.type = 'text';
    eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    input.type = 'password';
    eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
  }
}

// Show/Hide Sections
function showSection(sectionId) {
  // Hide all sections
  document.querySelectorAll('.form-section').forEach(section => {
    section.classList.remove('active');
  });
  
  // Show target section
  const target = document.getElementById(sectionId);
  if (target) {
    target.classList.add('active');
  }
  
  // Hide alerts when switching sections so it looks clean
  const alertBox = document.getElementById('alertBox');
  if (alertBox) {
    alertBox.style.display = 'none';
  }
}
</script>

</body>
</html>