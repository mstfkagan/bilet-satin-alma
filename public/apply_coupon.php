<?php
header('Content-Type: application/json'); 

require_once __DIR__ . '/../src/auth.php';
requireAuth(['user']);

require_once __DIR__ . '/../src/db.php';

function json_response(bool $success, string $message, ?float $discount = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'discount' => $discount]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Geçersiz istek metodu.');
}

$coupon_code = $_POST['coupon_code'] ?? '';
$trip_id = $_POST['trip_id'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($coupon_code) || empty($trip_id)) {
    json_response(false, 'Eksik bilgi.');
}

$db = getDB();

$stmt = $db->prepare(
    "SELECT c.id, c.discount, c.usage_limit, c.expire_date
     FROM Coupons c
     JOIN Trips t ON (c.company_id = t.company_id OR c.company_id IS NULL)
     WHERE c.code = ? AND t.id = ?"
);
$stmt->execute([$coupon_code, $trip_id]);
$coupon = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$coupon) {
    json_response(false, 'Geçersiz veya bu sefer için uygun olmayan kupon kodu.');
}

if (strtotime($coupon['expire_date']) < time()) {
    json_response(false, 'Bu kuponun süresi dolmuş.');
}

$stmt_usage = $db->prepare("SELECT COUNT(*) as use_count FROM User_Coupons WHERE coupon_id = ?");
$stmt_usage->execute([$coupon['id']]);
$usage = $stmt_usage->fetch(PDO::FETCH_ASSOC);

if ($usage['use_count'] >= $coupon['usage_limit']) {
    json_response(false, 'Bu kupon kullanım limitine ulaştı.');
}

$stmt_user_usage = $db->prepare("SELECT id FROM User_Coupons WHERE coupon_id = ? AND user_id = ?");
$stmt_user_usage->execute([$coupon['id'], $user_id]);
if ($stmt_user_usage->fetch()) {
    json_response(false, 'Bu kuponu zaten kullandınız.');
}

json_response(true, 'Kupon başarıyla uygulandı!', (float)$coupon['discount']);