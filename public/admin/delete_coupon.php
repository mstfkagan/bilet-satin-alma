<?php
require_once __DIR__ . '/../../src/auth.php';
requireAuth(['admin']);

require_once __DIR__ . '/../../src/db.php';

$id = $_GET['id'] ?? null;
$company_id = $_GET['company_id'] ?? null; 
// geri dönmek için firma id'sini alır

if ($id) {
    $stmt = getDB()->prepare('DELETE FROM Coupons WHERE id = ?');
    $stmt->execute([$id]);
}

if ($company_id) {
    header('Location: edit_company.php?id=' . $company_id);
} else {
    header('Location: manage_companies.php');
}
exit;