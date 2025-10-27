<?php
require_once __DIR__ . '/../src/flow_guard.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/helpers.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '-----------------------------------------------------';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$full_name || !$email || !$password) {
        $message = "Tüm alanları doldurun.";
    } else {
        $db = getDB();
        $stmt = $db->prepare('SELECT id FROM User WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = "e-posta zaten kayıtlı.";
        } else {
            $id = uuid();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO User (id, full_name, email, password, role) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$id, $full_name, $email, $hash, 'user']);
            $_SESSION['user_id'] = $id;
            $_SESSION['role'] = 'user';
            $_SESSION['name'] = $full_name;
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Kayıt Ol</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 400px;">
  <h3 class="text-center mb-3">Kayıt Ol</h3>
  <?php if($message): ?>
    <div class="alert alert-warning"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Ad Soyad</label>
      <input type="text" name="full_name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">E-posta</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Şifre</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button class="btn btn-primary w-100">Kayıt Ol</button>
  </form>
  <p class="text-center mt-3">Zaten hesabın var mı <a href="login.php">Giriş Yap</a></
