<?php
require_once __DIR__ . '/../../src/auth.php';
requireAuth(['company']);

require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/helpers.php';

$db = getDB();
$company_id = $_SESSION['company_id'];
$errors = [];

$mode = isset($_GET['id']) ? 'edit' : 'add';
$pageTitle = ($mode === 'edit') ? 'Seferi Düzenle' : 'Yeni Sefer Ekle';

// burayı dolu bırakınca hata veriyo, doldurma
$trip = [
    'id' => null,
    'departure_city' => '',
    'destination_city' => '',
    'departure_time' => '',
    'arrival_time' => '',
    'price' => '',
    'capacity' => ''
];


if ($mode === 'edit') {
    $stmt = $db->prepare('SELECT * FROM Trips WHERE id = ? AND company_id = ?');
    $stmt->execute([$_GET['id'], $company_id]);
    $trip_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($trip_data) {
        $trip = $trip_data;
    } else {
        header('Location: dashboard.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formdan gelen verileri $trip dizisine ata
    $trip = array_merge($trip, $_POST);
    $trip['id'] = $_POST['trip_id'] ?: null; // Gizli input'tan ID'yi al

    // Doğrulama (Validation)
    if (empty($trip['departure_city'])) $errors[] = 'Kalkış şehri boş bırakılamaz.';
    if (empty($trip['destination_city'])) $errors[] = 'Varış şehri boş bırakılamaz.';
    if (empty($trip['departure_time'])) $errors[] = 'Kalkış zamanı boş bırakılamaz.';
    if (empty($trip['arrival_time'])) $errors[] = 'Varış zamanı boş bırakılamaz.';
    if (!empty($trip['departure_time']) && !empty($trip['arrival_time']) && $trip['arrival_time'] <= $trip['departure_time']) {
        $errors[] = 'Varış zamanı, kalkış zamanından sonra olmalıdır.';
    }
    if (!is_numeric($trip['price']) || $trip['price'] <= 0) $errors[] = 'Fiyat geçerli bir sayı olmalıdır.';
    if (!is_numeric($trip['capacity']) || $trip['capacity'] <= 0) $errors[] = 'Kapasite geçerli bir sayı olmalıdır.';

    // Hata yoksa veritabanı işlemini yap
    if (empty($errors)) {
        if ($mode === 'edit') {
            // GÜNCELLEME
            $stmt = $db->prepare(
                'UPDATE Trips SET departure_city = ?, destination_city = ?, departure_time = ?, arrival_time = ?, price = ?, capacity = ? 
                 WHERE id = ? AND company_id = ?'
            );
            $stmt->execute([
                $trip['departure_city'], $trip['destination_city'], $trip['departure_time'],
                $trip['arrival_time'], $trip['price'], $trip['capacity'],
                $trip['id'], $company_id
            ]);
        } else {
            // YENİ KAYIT
            $trip_id = uuid();
            $stmt = $db->prepare(
                'INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $trip_id, $company_id, $trip['departure_city'], $trip['destination_city'],
                $trip['departure_time'], $trip['arrival_time'], $trip['price'], $trip['capacity']
            ]);
        }
        header('Location: dashboard.php');
        exit;
    }
}

// Formdaki tarih alanları için doğru formatı hazırla
$departure_time_formatted = !empty($trip['departure_time']) ? date('Y-m-d\TH:i', strtotime($trip['departure_time'])) : '';
$arrival_time_formatted = !empty($trip['arrival_time']) ? date('Y-m-d\TH:i', strtotime($trip['arrival_time'])) : '';

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5" style="max-width: 700px;">
    <div class="d-flex justify-content-between align-items_center mb-4">
        <h2><?= htmlspecialchars($pageTitle) ?></h2>
        <a href="dashboard.php" class="btn btn-secondary">Panele Dön</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p class="mb-0"><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="trip_id" value="<?= htmlspecialchars($trip['id']) ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kalkış Şehri</label>
                        <input type="text" name="departure_city" class="form-control" value="<?= htmlspecialchars($trip['departure_city']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Varış Şehri</label>
                        <input type="text" name="destination_city" class="form-control" value="<?= htmlspecialchars($trip['destination_city']) ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kalkış Zamanı</label>
                        <input type="datetime-local" name="departure_time" class="form-control" value="<?= $departure_time_formatted ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Varış Zamanı</label>
                        <input type="datetime-local" name="arrival_time" class="form-control" value="<?= $arrival_time_formatted ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fiyat (₺)</label>
                        <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($trip['price']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kapasite (Koltuk Sayısı)</label>
                        <input type="number" name="capacity" class="form-control" value="<?= htmlspecialchars($trip['capacity']) ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Kaydet</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>