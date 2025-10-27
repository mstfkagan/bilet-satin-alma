<?php
require_once __DIR__ . '/../src/auth.php';
requireAuth(['user']);

require_once __DIR__ . '/../src/db.php';

$ticket_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$ticket_id) {
    header('Location: my_tickets.php');
    exit;
}

$db = getDB();
$db->beginTransaction();

try {
    $stmt = $db->prepare(
        'SELECT t.id, t.total_price, t.status, tr.departure_time FROM Tickets t
         JOIN Trips tr ON t.trip_id = tr.id
         WHERE t.id = ? AND t.user_id = ?'
    );
    $stmt->execute([$ticket_id, $user_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket || $ticket['status'] !== 'active') {
        throw new Exception('İptal edilecek bilet bulunamadı veya bilet zaten aktif değil.');
    }

    $departure = new DateTime($ticket['departure_time']);
    $now = new DateTime();
    if ($now >= $departure->modify('-1 hour')) {
        throw new Exception('Kalkışa bir saatten az kaldığı için bilet iptal edilemez.');
    }

    $stmt = $db->prepare('DELETE FROM Booked_Seats WHERE ticket_id = ?');
    $stmt->execute([$ticket_id]);

   
    $stmt = $db->prepare('UPDATE Tickets SET status = ? WHERE id = ?');
    $stmt->execute(['canceled', $ticket_id]);

   
    $stmt = $db->prepare('UPDATE User SET balance = balance + ? WHERE id = ?');
    $stmt->execute([$ticket['total_price'], $user_id]);

    $db->commit();
    $_SESSION['success_message'] = 'Biletiniz başarıyla iptal edildi ve ücret iadesi yapıldı.';

} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error_message'] = 'İptal işlemi başarısız: ' . $e->getMessage();
}

header('Location: my_tickets.php');
exit;