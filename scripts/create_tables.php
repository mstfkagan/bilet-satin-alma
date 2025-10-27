<?php
require_once __DIR__ . '/../src/db.php';

try {
    $db = getDB();
    $sql = file_get_contents(__DIR__ . '/../schema.sql');
    if ($sql === false) throw new Exception('schema.sql okunamadi');
    $db->exec($sql);
    echo "veritabani yapisi olusturuldu";
} catch (Exception $e) {
    echo "Hata " . $e->getMessage();
}
