<?php
require_once __DIR__ . '/../../src/auth.php';
requireAuth(['company']); // sadece frima sahipleri

require_once __DIR__ . '/../../src/db.php';

$coupon_id = $_GET['id'] ?? null;
if (!$coupon_id) {
    header('Location: manage_coupons.php');
    exit;
}

$company_id = $_SESSION['company_id'];
$db = getDB();


$stmt = $db->prepare('DELETE FROM Coupons WHERE id = ? AND company_id = ?');
$stmt->execute([$coupon_id, $company_id]);

header('Location: manage_coupons.php');
exit;