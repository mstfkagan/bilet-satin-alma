<?php
require_once __DIR__ . '/../../src/auth.php';
requireAuth(['company']);

require_once __DIR__ . '/../../src/db.php';

$company_id = $_SESSION['company_id'];
$db = getDB();

$stmt = $db->prepare(
    'SELECT *, 
           (SELECT COUNT(*) FROM User_Coupons WHERE coupon_id = Coupons.id) as use_count 
     FROM Coupons 
     WHERE company_id = ? 
     ORDER BY created_at DESC'
);
$stmt->execute([$company_id]);
$coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kupon Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Firma Paneli - Kuponlar</h2>
        <a href="dashboard.php" class="btn btn-secondary">Panele Dön</a>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <span>Firmanıza Ait Kuponlar</span>
            <a href="edit_coupon.php" class="btn btn-sm btn-success">Yeni Kupon Ekle</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Kod</th>
                    <th>İndirim (%)</th>
                    <th>Limit</th>
                    <th>Kalan Hak</th> <th>Son Tarih</th>
                    <th>İşlemler</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($coupons): ?>
                    <?php foreach ($coupons as $coupon): ?>
                        <tr>
                            <td><?= htmlspecialchars($coupon['code']) ?></td>
                            <td><?= htmlspecialchars($coupon['discount']) ?>%</td>
                            <td><?= htmlspecialchars($coupon['usage_limit']) ?></td>
                            <td>
                                <?php $remaining = $coupon['usage_limit'] - $coupon['use_count']; ?>
                                <span class="badge bg-<?= $remaining > 0 ? 'success' : 'danger' ?>">
                                    <?= $remaining ?>
                                </span>
                                <small class="text-muted">(kullanılan: <?= $coupon['use_count'] ?>)</small>
                            </td>
                            <td><?= htmlspecialchars(date('d.m.Y', strtotime($coupon['expire_date']))) ?></td>
                            <td>
                                <a href="edit_coupon.php?id=<?= $coupon['id'] ?>" class="btn btn-sm btn-primary">Düzenle</a>
                                <a href="delete_coupon.php?id=<?= $coupon['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu kuponu silmek istediğinizden emin misiniz?')">Sil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Henüz oluşturulmuş bir kuponunuz yok.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>