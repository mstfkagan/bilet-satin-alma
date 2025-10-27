<?php
require_once __DIR__ . '/../../src/auth.php';
requireAuth(['admin']); 
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Admin Paneli</h2>
        <div>
            <span><?= htmlspecialchars($_SESSION['name']) ?></span>
            <a href="../logout.php" class="btn btn-sm btn-outline-danger ms-2">Çıkış</a>
            <a href="../index.php" class="btn btn-sm btn-outline-primary ms-2">Ana Sayfaya Dön</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Firma Yönetimi</h5>
                    <p class="card-text">Yeni otobüs firmaları ekleyin, mevcutları düzenleyin veya silin.</p>
                    <a href="manage_companies.php" class="btn btn-primary">Firmaları Yönet</a>
                </div>
            </div>
        </div>
        
        </div>
</div>
</body>
</html>