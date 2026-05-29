<?php
// login.php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['peran']) && in_array($_SESSION['peran'], ['Guru', 'Admin'])) {
        header('Location: ../Guru/dashboardguru.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi!';
    } else {
        require_once __DIR__ . '/../../Config/Database.php';
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
                    header('Location: dashboard.php');
                    exit;
                }

                if ($user['peran'] === 'Guru' || $user['peran'] === 'Admin') {
                    header('Location: ../Guru/dashboardguru.php');
                    exit;
                }

                header('Location: dashboard.php');
                exit;

            } else {
                $error = 'Username atau password salah. Silakan coba lagi.';
            }
        } catch (\Exception $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
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
  <link rel="stylesheet" href="/SIPAUDQU/Public/css/style.css">
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
      <h2>Login</h2>

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php">
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
            <span class="eye-icon" onclick="togglePassword()">
              <i class="fasfa-eye" id="eyeIcon"></i>
            </span>
          </div>
        </div>

        <button type="submit" class="btn-login">
          <i class="fas fa-sign-in-alt"></i> Login
        </button>
      </form>
    </div>

  </div>
</div>

<!-- Footer -->
<footer class="site-footer">
  <p>&copy; <?= date('Y') ?> <strong>SIPAUDQU</strong> &mdash; Sistem Informasi PAUD Qur'an</p>
</footer>

<script>
function togglePassword() {
  const input   = document.getElementById('passwordInput');
  const eyeIcon = document.getElementById('eyeIcon');
  if (input.type === 'password') {
    input.type = 'text';
    eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    input.type = 'password';
    eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
  }
}
</script>

</body>
</html>