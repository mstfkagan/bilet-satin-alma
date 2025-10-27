<?php
function getDB(): PDO {
    static $db = null;
    if ($db === null) {
        $path = __DIR__ . '/../data/database.db';
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $db = new PDO('sqlite:' . $path);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec('PRAGMA foreign_keys = ON;');
    }
    return $db;
}
