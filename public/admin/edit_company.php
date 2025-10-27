<?php
require_once __DIR__ . '/../../src/auth.php';
requireAuth(['admin']);
require_once __DIR__ . '/../../src/helpers.php';
require_once __DIR__ . '/../../src/db.php';

$db = getDB();
$errors = [];

$mode = isset($_GET['id']) ? 'edit' : 'add';
$pageTitle = ($mode === 'edit') ? 'Firmayı Düzenle' : 'Yeni Firma ve Yönetici Ekle';
$company_id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mode === 'add') {
    $company_name = trim($_POST['company_name']);
    $admin_full_name = trim($_POST['admin_full_name']);
    $admin_email = trim($_POST['admin_email']);
    $admin_password = $_POST['admin_password'];


    if (empty($company_name)) $errors[] = "Firma adı boş bırakılamaz.";
    if (empty($admin_full_name)) $errors[] = "Yönetici adı boş bırakılamaz.";
    if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Geçerli bir e-posta adresi girin.";
    if (empty($admin_password)) $errors[] = "Yönetici için şifre zorunludur.";

    // e postadan başka var mı kotnrol etme yeri
    $stmt_check = $db->prepare("SELECT id FROM User WHERE email = ?");
    $stmt_check->execute([$admin_email]);
    if ($stmt_check->fetch()) {
        $errors[] = 'Bu e-posta adresi zaten başka bir kullanıcı tarafından kullanılıyor.';
    }

    if (empty($errors)) {
        $db->beginTransaction();
        try {
            $new_company_id = uuid();
            $stmt_company = $db->prepare('INSERT INTO Bus_Company (id, name) VALUES (?, ?)');
            $stmt_company->execute([$new_company_id, $company_name]);

            $new_admin_id = uuid();
            $hash = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt_admin = $db->prepare("INSERT INTO User (id, full_name, email, password, role, company_id) VALUES (?, ?, ?, ?, 'company', ?)");
            $stmt_admin->execute([$new_admin_id, $admin_full_name, $admin_email, $hash, $new_company_id]);

            //her şey doğruysa tamamla
            $db->commit();
            header('Location: manage_companies.php');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = "Veritabanı hatası: " . $e->getMessage();
        }
    }
}

$company = ['id' => null, 'name' => ''];
$admins = [];
$coupons = [];
if ($mode === 'edit') {
}
?>
<!DOCTYPE html>
<html lang="tr">
<head><title><?= htmlspecialchars($pageTitle) ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<div class="container py-5" style="max-width: 700px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= htmlspecialchars($pageTitle) ?></h2>
        <a href="manage_companies.php" class="btn btn-secondary">Listeye Dön</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <?php if ($mode === 'add'): ?>
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <h5 class="mb-3">Firma Bilgileri</h5>
                <div class="mb-3">
                    <label class="form-label">Firma Adı</label>
                    <input type="text" name="company_name" class="form-control" required>
                </div>
                <hr>
                <h5 class="mb-3">İlk Yönetici Bilgileri</h5>
                <div class="mb-3">
                    <label class="form-label">Yönetici Adı Soyadı</label>
                    <input type="text" name="admin_full_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Yönetici E-posta</label>
                    <input type="email" name="admin_email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Yönetici Şifre</label>
                    <input type="password" name="admin_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Firmayı ve Yöneticiyi Oluştur</button>
            </form>
        </div>
    </div>
    
    <?php else: ?>
    
        <?php endif; ?>
</div>
</body>
</html>