<?php
require_once __DIR__ . '/../../src/auth.php';
requireAuth(['admin']);

require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/helpers.php';

$db = getDB();
$errors = [];
$mode = 'add';
$pageTitle = 'Yeni Firma Yöneticisi Ekle';

$admin = ['id' => null, 'full_name' => '', 'email' => '', 'company_id' => $_GET['company_id'] ?? null];

if (isset($_GET['id'])) {
    $mode = 'edit';
    $pageTitle = 'Firma Yöneticisini Düzenle';
    $stmt = $db->prepare("SELECT id, full_name, email, company_id FROM User WHERE id = ? AND role = 'company'");
    $stmt->execute([$_GET['id']]);
    $admin_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin_data) $admin = $admin_data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?: null;
    $company_id = $_POST['company_id'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($full_name)) $errors[] = "Ad Soyad boş bırakılamaz.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Geçerli bir e-posta adresi girin.";
    
    $stmt = $db->prepare("SELECT id FROM User WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) $errors[] = 'Bu e-posta adresi zaten başka bir kullanıcı tarafından kullanılıyor.';

    if ($mode === 'add' && empty($password)) $errors[] = "Yeni kullanıcı için şifre zorunludur.";

    if (empty($errors)) {
        if ($mode === 'edit' && $id) {

            $sql = "UPDATE User SET full_name = ?, email = ? ";
            $params = [$full_name, $email];
            if (!empty($password)) {
                $sql .= ", password = ? ";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            $sql .= "WHERE id = ?";
            $params[] = $id;
            $db->prepare($sql)->execute($params);
    
        } else {
            $new_id = uuid();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO User (id, full_name, email, password, role, company_id) VALUES (?, ?, ?, ?, 'company', ?)";
            $db->prepare($sql)->execute([$new_id, $full_name, $email, $hash, $company_id]);
        }
        header('Location: edit_company.php?id=' . $company_id);
        exit;
    } else {
        $admin = ['id' => $id, 'full_name' => $full_name, 'email' => $email, 'company_id' => $company_id];
    }
}

$cancel_url = $admin['company_id'] ? "edit_company.php?id=" . htmlspecialchars($admin['company_id']) : "manage_companies.php";
?>
<!DOCTYPE html>
<html lang="tr">
<head><title><?= $pageTitle ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<div class="container py-5" style="max-width:600px">
    <h2><?= $pageTitle ?></h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger mt-3"><?php foreach($errors as $error) echo "<p class='mb-0'>".htmlspecialchars($error)."</p>"; ?></div>
    <?php endif; ?>
    <form method="POST" class="mt-4">
        <input type="hidden" name="id" value="<?= htmlspecialchars($admin['id']) ?>">
        <input type="hidden" name="company_id" value="<?= htmlspecialchars($admin['company_id']) ?>">
        <div class="mb-3">
            <label class="form-label">Ad Soyad</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($admin['full_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">E-posta</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Şifre</label>
            <input type="password" name="password" class="form-control" <?= $mode === 'add' ? 'required' : '' ?>>
            <?php if ($mode === 'edit'): ?><small class="form-text text-muted">Değiştirmek istemiyorsanız boş bırakın.</small><?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Kaydet</button>
        <a href="<?= $cancel_url ?>" class="btn btn-secondary">İptal</a>
    </form>
</div>
</body>
</html>