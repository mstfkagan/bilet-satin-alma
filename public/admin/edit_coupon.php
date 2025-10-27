<?php
require_once __DIR__ . '/../../src/auth.php';
requireAuth(['admin']);

require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/helpers.php';

$db = getDB();
$errors = [];
$pageTitle = 'Yeni Kupon Ekle';
$mode = 'add';
$company_id = $_GET['company_id'] ?? null; 
// yeni kuponun hangi şirkete ait olacağını urlden alöa

$coupon = ['id' => null, 'code' => '', 'discount' => '', 'usage_limit' => '', 'expire_date' => '', 'company_id' => $company_id];

if (isset($_GET['id'])) {
    $mode = 'edit';
    $pageTitle = 'Kuponu Düzenle';
    $stmt = $db->prepare('SELECT * FROM Coupons WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $coupon_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($coupon_data) $coupon = $coupon_data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?: null;
    $company_id = $_POST['company_id'];
    $code = trim($_POST['code']);
    $discount = trim($_POST['discount']);
    $usage_limit = trim($_POST['usage_limit']);
    $expire_date = $_POST['expire_date'];


    if (empty($errors)) {
        if ($mode === 'edit' && $id) {
            $stmt = $db->prepare('UPDATE Coupons SET code = ?, discount = ?, usage_limit = ?, expire_date = ? WHERE id = ?');
            $stmt->execute([$code, $discount, $usage_limit, $expire_date, $id]);
        } else {
            $new_id = uuid();
            $stmt = $db->prepare('INSERT INTO Coupons (id, company_id, code, discount, usage_limit, expire_date) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$new_id, $company_id, $code, $discount, $usage_limit, $expire_date]);
        }
        header('Location: edit_company.php?id=' . $company_id); // Geri dönülecek firma sayfasını belirt
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head><title><?= $pageTitle ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<div class="container py-5" style="max-width:600px">
    <h2><?= $pageTitle ?></h2>
    <form method="POST" class="mt-4">
        <input type="hidden" name="id" value="<?= htmlspecialchars($coupon['id']) ?>">
        <input type="hidden" name="company_id" value="<?= htmlspecialchars($coupon['company_id']) ?>">
        
        <div class="mb-3">
            <label class="form-label">Kupon Kodu</label>
            <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($coupon['code']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">İndirim Oranı (%)</label>
            <input type="number" name="discount" class="form-control" value="<?= htmlspecialchars($coupon['discount']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Kullanım Limiti</label>
            <input type="number" name="usage_limit" class="form-control" value="<?= htmlspecialchars($coupon['usage_limit']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Son Kullanma Tarihi</label>
            <input type="date" name="expire_date" class="form-control" value="<?= htmlspecialchars($coupon['expire_date']) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Kaydet</button>
        <a href="edit_company.php?id=<?= htmlspecialchars($coupon['company_id']) ?>" class="btn btn-secondary">İptal</a>
    </form>
</div>
</body>
</html>