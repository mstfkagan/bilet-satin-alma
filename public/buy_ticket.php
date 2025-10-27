<?php
require_once __DIR__ . '/../src/auth.php';
requireAuth(['user']);

require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }

$trip_id = $_POST['trip_id'] ?? null;
$selected_seats = $_POST['seats'] ?? [];
$user_id = $_SESSION['user_id'];
$applied_coupon_code = $_POST['applied_coupon'] ?? '';

if (!$trip_id || empty($selected_seats)) {
    $_SESSION['error_message'] = 'Lütfen en az bir koltuk seçin.';
    header('Location: trip_details.php?id=' . $trip_id);
    exit;
}

$db = getDB();
$db->beginTransaction();

try {
    $stmt = $db->prepare('SELECT price FROM Trips WHERE id = ?');
    $stmt->execute([$trip_id]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $db->prepare('SELECT balance FROM User WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trip || !$user) throw new Exception('Sefer veya kullanıcı bulunamadı.');

    $total_price = count($selected_seats) * $trip['price'];
    $final_price = $total_price;
    $valid_coupon_id = null;

    if (!empty($applied_coupon_code)) {
        $stmt_coupon = $db->prepare("SELECT c.id, c.discount, c.usage_limit, c.expire_date FROM Coupons c JOIN Trips t ON (c.company_id = t.company_id OR c.company_id IS NULL) WHERE c.code = ? AND t.id = ?");
        $stmt_coupon->execute([$applied_coupon_code, $trip_id]);
        $coupon = $stmt_coupon->fetch(PDO::FETCH_ASSOC);
        
        if ($coupon && strtotime($coupon['expire_date']) >= time()) {
            $stmt_usage = $db->prepare("SELECT COUNT(*) as use_count FROM User_Coupons WHERE coupon_id = ?");
            $stmt_usage->execute([$coupon['id']]);
            $usage = $stmt_usage->fetch(PDO::FETCH_ASSOC);
            
            $stmt_user_usage = $db->prepare("SELECT id FROM User_Coupons WHERE coupon_id = ? AND user_id = ?");
            $stmt_user_usage->execute([$coupon['id'], $user_id]);

            if ($usage['use_count'] < $coupon['usage_limit'] && !$stmt_user_usage->fetch()) {
                // indirimi uygula
                $final_price = $total_price * (1 - ($coupon['discount'] / 100));
                $valid_coupon_id = $coupon['id'];
            }
        }
    }

    if ($user['balance'] < $final_price) throw new Exception('Yetersiz bakiye!');

    // loltukların dolu olup olmadığını kontrol etme kısmı

    $ticket_id = uuid();
    $stmt = $db->prepare('INSERT INTO Tickets (id, trip_id, user_id, total_price) VALUES (?, ?, ?, ?)');
    $stmt->execute([$ticket_id, $trip_id, $user_id, $final_price]); // Fiyat olarak final_price'ı kaydet

    $stmt_seats = $db->prepare('INSERT INTO Booked_Seats (id, ticket_id, seat_number) VALUES (?, ?, ?)');
    foreach ($selected_seats as $seat) $stmt_seats->execute([uuid(), $ticket_id, $seat]);

    // kuponu kullandığında kaydetme 
    if ($valid_coupon_id) {
        $stmt_use_coupon = $db->prepare("INSERT INTO User_Coupons (id, coupon_id, user_id) VALUES (?, ?, ?)");
        $stmt_use_coupon->execute([uuid(), $valid_coupon_id, $user_id]);
    }

    $new_balance = $user['balance'] - $final_price;
    $stmt = $db->prepare('UPDATE User SET balance = ? WHERE id = ?');
    $stmt->execute([$new_balance, $user_id]);

    $db->commit();
    $_SESSION['success_message'] = 'Biletiniz başarıyla oluşturuldu!';
    header('Location: my_tickets.php');
    exit;

} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error_message'] = 'Bir hata oluştu: ' . $e->getMessage();
    header('Location: trip_details.php?id=' . $trip_id);
    exit;
}