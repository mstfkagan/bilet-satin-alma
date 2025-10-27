<?php
require_once __DIR__ . '/../../src/auth.php';
requireAuth(['company']);

require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/helpers.php';

$company_id = $_SESSION['company_id'] ?? null;
if (!$company_id) {
    die('HATA: Atanmış bir şirketiniz bulunmuyor.');
}

$db = getDB();
$stmt = $db->prepare('SELECT * FROM Trips WHERE company_id = ? ORDER BY departure_time DESC');
$stmt->execute([$company_id]);
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firma Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Firma Paneli - Seferler</h2>
        <div>
            <span> <?= htmlspecialchars($_SESSION['name']) ?></span>
            <a href="../logout.php" class="btn btn-sm btn-outline-danger ms-2">Çıkış</a>
            <a href="../index.php" class="btn btn-sm btn-outline-primary ms-2">Ana Sayfaya Dön</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <span>Firmanıza Ait Seferler</span>
            <a href="manage_trip.php" class="btn btn-sm btn-success">Yeni Sefer Ekle</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Kalkış</th>
                    <th>Varış</th>
                    <th>Kalkış Zamanı</th>
                    <th>Fiyat</th>
                    <th>Kapasite</th>
                    <th>İşlemler</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($trips): ?>
                    <?php foreach ($trips as $trip): ?>
                        <tr>
                            <td><?= htmlspecialchars($trip['departure_city']) ?></td>
                            <td><?= htmlspecialchars($trip['destination_city']) ?></td>
                            <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($trip['departure_time']))) ?></td>
                            <td><?= htmlspecialchars($trip['price']) ?> ₺</td>
                            <td><?= htmlspecialchars($trip['capacity']) ?></td>
                            <td>
                                <a href="manage_trip.php?id=<?= $trip['id'] ?>" class="btn btn-sm btn-primary">Düzenle</a>
                                <a href="delete_trip.php?id=<?= $trip['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu seferi silmek istediğinizden emin misiniz?')">Sil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Henüz oluşturulmuş bir seferiniz yok.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
     <div class="mt-4">
        <a href="manage_coupons.php" class="btn btn-info">Kuponları Yönet</a>
    </div>
</div>
</body>
</html>