<?php
require_once __DIR__ . '/../../src/auth.php';
requireAuth(['admin']);

require_once __DIR__ . '/../../src/db.php';
$companies = getDB()->query('SELECT * FROM Bus_Company ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firma Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Firma Yönetimi</h2>
        <a href="dashboard.php" class="btn btn-secondary">Panele Dön</a>
    </div>
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <span>Tüm Firmalar</span>
            <a href="edit_company.php" class="btn btn-sm btn-success">Yeni Firma Ekle</a>
        </div>
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Firma Adı</th><th>İşlemler</th></tr></thead>
                <tbody>
                <?php foreach ($companies as $company): ?>
                    <tr>
                        <td><?= htmlspecialchars($company['name']) ?></td>
                        <td>
                            <a href="edit_company.php?id=<?= $company['id'] ?>" class="btn btn-sm btn-primary">Düzenle</a>
                            <a href="delete_company.php?id=<?= $company['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu firmayı silmek istediğinizden emin misiniz? Bu firmaya ait tüm seferler, biletler ve kuponlar da silinecektir!')">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>