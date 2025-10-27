<?php
require_once __DIR__ . '/../src/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['entry_point_accessed'] = true;

$message = '';
$alert_type = 'danger'; 


if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $alert_type = 'warning'; 
    unset($_SESSION['message']);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $db = getDB();
    $stmt = $db->prepare('SELECT id, full_name, password, role FROM User WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['full_name'];

        
        if ($user['role'] === 'admin') {
            // eğer rolü adminse panele yönlendiiir
            header('Location: admin/dashboard.php');
        } elseif ($user['role'] === 'company') {
            
            $stmt_company = $db->prepare('SELECT company_id FROM User WHERE id = ?');
            $stmt_company->execute([$user['id']]);
            $user_company = $stmt_company->fetch(PDO::FETCH_ASSOC);
            $_SESSION['company_id'] = $user_company['company_id'];
            header('Location: company/dashboard.php');
        } else {
            
            header('Location: index.php');
        }
        exit;
    } else {
        $message = "E-posta veya şifre hatalı.";
        $alert_type = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Giriş Yap</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 400px;">
  <div class="card">
    <div class="card-body p-4">
      <h3 class="text-center mb-4">Giriş Yap</h3>

      <?php if($message): ?>
        <div class="alert alert-<?= $alert_type ?>"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">E-posta</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Şifre</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Giriş Yap</button>
      </form>

      <div class="text-center my-3">
        <span class="text-muted">veya</span>
      </div>

      <a href="index.php" class="btn btn-outline-secondary w-100 mb-3">Misafir Olarak Devam Et</a>

      <p class="text-center mt-3 mb-0">
        Hesabın yok mu? <a href="register.php">Hemen Kayıt Ol</a>
      </p>
    </div>
  </div>
</div>
</body>
</html>