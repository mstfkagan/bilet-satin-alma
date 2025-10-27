<?php
require_once __DIR__ . '/../../src/auth.php';
requireAuth(['company']);

require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/helpers.php';

$db = getDB();
$company_id = $_SESSION['company_id'];
$pageTitle = 'Yeni Kupon Ekle';
$errors = [];

$coupon = [
    'id' => null,
    'code' => '',
    'discount' => '',
    'usage_limit' => '',
    'expire_date' => ''
];

// DÜZENLEME MODU
if (isset($_GET['id'])) {
    $pageTitle = 'Kuponu Düzenle';
    
    $stmt = $db->prepare('SELECT * FROM Coupons WHERE id = ? AND company_id = ?');
    $stmt->execute([$_GET['id'], $company_id]);
    $coupon_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($coupon_data) {
        $coupon = $coupon_data;
    } else {
        header('Location: manage_coupons.php'); 
        // yetkisiz erişimi veya geçersiz idyi engelleme amaçlo
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coupon['id'] = $_POST['coupon_id'] ?: null;
    $coupon['code'] = trim($_POST['code']);
    $coupon['discount'] = trim($_POST['discount']);
    $coupon['usage_limit'] = trim($_POST['usage_limit']);
    $coupon['expire_date'] = $_POST['expire_date'];

    if (empty($coupon['code'])) $errors[] = 'Kupon kodu boş bırakılamaz.';
    if (!is_numeric($coupon['discount']) || $coupon['discount'] < 1 || $coupon['discount'] > 100) $errors[] = 'İndirim oranı 1 ile 100 arasında bir sayı olmalıdır.';
    if (!is_numeric($coupon['usage_limit']) || $coupon['usage_limit'] < 1) $errors[] = 'Kullanım limiti 1 veya daha büyük bir sayı olmalıdır.';
    if (empty($coupon['expire_date'])) $errors[] = 'Son kullanma tarihi boş bırakılamaz.';

    if (empty($errors)) {
        if ($coupon['id']) {
            $stmt = $db->prepare('UPDATE Coupons SET code = ?, discount = ?, usage_limit = ?, expire_date = ? WHERE id = ? AND company_id = ?');
            $stmt->execute([$coupon['code'], $coupon['discount'], $coupon['usage_limit'], $coupon['expire_date'], $coupon['id'], $company_id]);
        } else {
            $coupon_id = uuid();
            $stmt = $db->prepare('INSERT INTO Coupons (id, company_id, code, discount, usage_limit, expire_date) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$coupon_id, $company_id, $coupon['code'], $coupon['discount'], $coupon['usage_limit'], $coupon['expire_date']]);
        }
        header('Location: manage_coupons.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5" style="max-width: 600px;">
    <h2><?= htmlspecialchars($pageTitle) ?></h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger mt-3">
            <?php foreach ($errors as $error): ?><p class="mb-0"><?= htmlspecialchars($error) ?></p><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <input type="hidden" name="coupon_id" value="<?= htmlspecialchars($coupon['id']) ?>">
        <div class="mb-3">
            <label class="form-label">Kupon Kodu</label>
            <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($coupon['code']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">İndirim Oranı (%)</label>
            <input type="number" name="discount" class="form-control" value="<?= htmlspecialchars($coupon['discount']) ?>" min="1" max="100" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Kullanım Limiti</label>
            <input type="number" name="usage_limit" class="form-control" value="<?= htmlspecialchars($coupon['usage_limit']) ?>" min="1" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Son Kullanma Tarihi</label>
            <input type="date" name="expire_date" class="form-control" value="<?= htmlspecialchars($coupon['expire_date']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Kaydet</button>
        <a href="manage_coupons.php" class="btn btn-secondary">İptal</a>
    </form>
</div>
</body>
</html>