<?php
require_once __DIR__ . '/../../src/auth.php';
requireAuth(['company']); 

require_once __DIR__ . '/../../src/db.php';

$trip_id = $_GET['id'] ?? null;
if (!$trip_id) {
    header('Location: dashboard.php');
    exit;
}

$company_id = $_SESSION['company_id'];

$db = getDB();

$stmt = $db->prepare('DELETE FROM Trips WHERE id = ? AND company_id = ?');
$stmt->execute([$trip_id, $company_id]);

header('Location: dashboard.php');
exit;