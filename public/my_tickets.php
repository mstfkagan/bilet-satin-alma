<?php
require_once __DIR__ . '/../src/auth.php';
requireAuth(['user']);

require_once __DIR__ . '/../src/db.php';

$user_id = $_SESSION['user_id'];
$db = getDB();

$stmt = $db->prepare(
    'SELECT t.id as ticket_id, t.total_price, t.status, tr.departure_time, 
            tr.departure_city, tr.destination_city, bc.name as company_name
     FROM Tickets t
     JOIN Trips tr ON t.trip_id = tr.id
     JOIN Bus_Company bc ON tr.company_id = bc.id
     WHERE t.user_id = ?
     ORDER BY tr.departure_time DESC'
);
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Biletlerim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Biletlerim</h2>
        <a href="index.php" class="btn btn-primary">Yeni Sefer Ara</a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th>Firma</th>
                    <th>Güzergah</th>
                    <th>Kalkış Zamanı</th>
                    <th>Durum</th>
                    <th>Maliyet</th>
                    <th>İşlemler</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($tickets)): ?>
                    <tr><td colspan="6" class="text-center">Biletiniz bulunmamaktadır.</td></tr>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?= htmlspecialchars($ticket['company_name']) ?></td>
                            <td><?= htmlspecialchars($ticket['departure_city']) ?> → <?= htmlspecialchars($ticket['destination_city']) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($ticket['departure_time'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $ticket['status'] === 'active' ? 'success' : ($ticket['status'] === 'canceled' ? 'danger' : 'secondary') ?>">
                                    <?= ucfirst($ticket['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($ticket['total_price']) ?> ₺</td>
                            <td>
                                <?php
                                
                                $can_be_cancelled = false;
                                if ($ticket['status'] === 'active') {
                                    $departure_timestamp = strtotime($ticket['departure_time']);


                                    $one_hour_from_now_timestamp = strtotime('+1 hour');

                                    // kalkışa 1 saat kala iptl etme işi
                                    if ($departure_timestamp > $one_hour_from_now_timestamp) {
                                        $can_be_cancelled = true;
                                    }
                                }

                                if ($can_be_cancelled):
                                ?>
                                    <a href="cancel_ticket.php?id=<?= $ticket['ticket_id'] ?>" class="btn btn-sm btn-warning" onclick="return confirm('Bileti iptal etmek istediğinizden emin misiniz? Ücret hesabınıza iade edilecektir.')">İptal Et</a>
                                <?php endif; ?>
                                <a href="download_pdf.php?id=<?= $ticket['ticket_id'] ?>" class="btn btn-sm btn-info">PDF İndir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>