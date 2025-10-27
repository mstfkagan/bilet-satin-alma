<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['entry_point_accessed']) || $_SESSION['entry_point_accessed'] !== true) {
    header('Location: login.php');
    exit;
}