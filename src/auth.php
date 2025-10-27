<?php
// session başlamıdysa başlatır
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function requireAuth(array $allowedRoles = []): void
{
   
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = 'Bu sayfayı görmek için giriş yapmalısınız.';
        header('Location: login.php');
        exit;
    }

    if (!empty($allowedRoles) && !in_array($_SESSION['role'], $allowedRoles)) {
        $message = 'Bu sayfaya erişim yetkiniz bulunmuyor. Lütfen yetkili bir hesapla giriş yapın.';
        
        session_unset();
        session_destroy();

        session_start();
        $_SESSION['message'] = $message;
        
        header('Location: login.php');
        exit;
    }
}