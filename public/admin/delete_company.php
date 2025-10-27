<?php
require_once __DIR__ . '/../../src/auth.php';
requireAuth(['admin']);

require_once __DIR__ . '/../../src/db.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = getDB()->prepare('DELETE FROM Bus_Company WHERE id = ?');
    $stmt->execute([$id]);
}
header('Location: manage_companies.php');
exit;