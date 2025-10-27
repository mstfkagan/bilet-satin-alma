<?php

require_once __DIR__ . '/../src/flow_guard.php';

require_once __DIR__ . '/../src/db.php';


$db = getDB();


$user_balance = null;
if (isset($_SESSION['user_id'])) {
    $stmt_balance = $db->prepare('SELECT balance FROM User WHERE id = ?');
    $stmt_balance->execute([$_SESSION['user_id']]);
    $result = $stmt_balance->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $user_balance = $result['balance'];
    }
}

$where = '';
$params = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_GET['from']) && !empty($_GET['to'])) {
        $where = 'WHERE departure_city = ? AND destination_city = ?';
        $params = [$_GET['from'], $_GET['to']];
    }
}

// sefer sorgusu
$sql = "SELECT t.id, c.name AS company, t.departure_city, t.destination_city,
        t.departure_time, t.arrival_time, t.price
        FROM Trips t
        JOIN Bus_Company c ON t.company_id = c.id
        $where
        ORDER BY t.departure_time";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Bilet Satın Alma Platformu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Bilet Satın Alma Platformu</h2>
    <div>
      <?php if(isset($_SESSION['user_id'])): ?>
        <span>
             <?= htmlspecialchars($_SESSION['name']) ?>
            <?php if ($user_balance !== null): ?>
                <span class="badge bg-success ms-2"><?= htmlspecialchars($user_balance) ?> ₺</span>
            <?php endif; ?>
        </span>
        <?php if($_SESSION['role'] === 'user'): ?>
            <a href="my_tickets.php" class="btn btn-sm btn-outline-info ms-2">Biletlerim</a>
        <?php elseif($_SESSION['role'] === 'admin'): ?>
            <a href="./admin/dashboard.php" class="btn btn-sm btn-danger ms-2">Admin Paneli</a>
        <?php elseif($_SESSION['role'] === 'company'): ?>
            <a href="./company/dashboard.php" class="btn btn-sm btn-warning ms-2">Firma Paneli</a>
        <?php endif; ?>

        <a href="./logout.php" class="btn btn-sm btn-outline-danger ms-2">Çıkış</a>
      <?php else: ?>
        <a href="./login.php" class="btn btn-sm btn-outline-primary">Giriş</a>
        <a href="./register.php" class="btn btn-sm btn-outline-success">Kayıt Ol</a>
      <?php endif; ?>
    </div>
  </div>

  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label">Kalkış Şehri</label>
      <input type="text" name="from" class="form-control" placeholder="İstanbul" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Varış Şehri</label>
      <input type="text" name="to" class="form-control" placeholder="Ankara" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button class="btn btn-primary w-100">Ara</button>
    </div>
  </form>

  <?php if($trips): ?>
    <table class="table table-bordered bg-white">
      <thead class="table-light">
        <tr>
          <th>Firma</th>
          <th>Kalkış</th>
          <th>Varış</th>
          <th>Kalkış Saati</th>
          <th>Varış Saati</th>
          <th>Fiyat</th>
          <th>İşlem</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($trips as $t): ?>
        <tr>
          <td><?= htmlspecialchars($t['company']) ?></td>
          <td><?= htmlspecialchars($t['departure_city']) ?></td>
          <td><?= htmlspecialchars($t['destination_city']) ?></td>
          <td><?= htmlspecialchars(date("d-m-Y H:i", strtotime($t['departure_time']))) ?></td>
          <td><?= htmlspecialchars(date("d-m-Y H:i", strtotime($t['arrival_time']))) ?></td>
          <td><?= htmlspecialchars($t['price']) ?> ₺</td>
          <td>
            <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
              <a href="trip_details.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-info">Sefer Detayları</a>
            <?php elseif(!isset($_SESSION['user_id'])): ?>
              <a href="login.php" class="btn btn-sm btn-outline-secondary">Giriş Yap</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php elseif(isset($_GET['from'])): ?>
    <div class="alert alert-warning">Sefer bulunamadı.</div>
  <?php endif; ?>

</div>
</body>
</html>