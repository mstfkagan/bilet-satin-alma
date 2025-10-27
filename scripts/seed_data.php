<?php
// Bu script, dışarıdan hiçbir hassas bilgi okumaz.
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/helpers.php';

echo "Seed işlemi başlatılıyor...\n";

try {
    $db = getDB();
    $db->beginTransaction();

   
    $admin_email = 'admin@example.com';
    $admin_password_hash = '$2y$10$zUzd09zT5K7kjRCzYqXLCOU1IPZd97OxRi3U9O/103ehCpUX8IOhK'; 

    $stmt = $db->prepare("SELECT id FROM User WHERE email = ?");
    $stmt->execute([$admin_email]);
    if (!$stmt->fetch()) {
        $admin_id = uuid();
        $db->prepare("INSERT INTO User (id, full_name, email, role, password, balance) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$admin_id, 'Admin Kullanıcı', $admin_email, 'admin', $admin_password_hash, 1000]);
        echo "Güvenli admin kullanıcısı oluşturuldu.\n";
    } else {
        echo "ℹAdmin kullanıcısı zaten mevcut, atlandı.\n";
    }


    $company_name = 'Metro Turizm';
    $stmt = $db->prepare("SELECT id FROM Bus_Company WHERE name = ?");
    $stmt->execute([$company_name]);
    $company = $stmt->fetch();
    if (!$company) {
        $company_id = uuid();
        $db->prepare("INSERT INTO Bus_Company (id, name) VALUES (?, ?)")
           ->execute([$company_id, $company_name]);
        echo " '{$company_name}' firması oluşturuldu.\n";
    } else {
        $company_id = $company['id'];
        echo "ℹ'{$company_name}' firması zaten mevcut, atlandı.\n";
    }

    $user_email = 'user@example.com';
    $stmt = $db->prepare("SELECT id FROM User WHERE email = ?");
    $stmt->execute([$user_email]);
    if (!$stmt->fetch()) {
        $user_id = uuid();
        $db->prepare("INSERT INTO User (id, full_name, email, role, password, balance) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$user_id, 'Ali Veli', $user_email, 'user', password_hash('user123', PASSWORD_DEFAULT), 5000]);
        echo "Standart kullanıcı '{$user_email}' oluşturuldu.\n";
    } else {
        echo "ℹStandart kullanıcı '{$user_email}' zaten mevcut, atlandı.\n";
    }


    $company_admin_email = 'firma@example.com';
    $stmt = $db->prepare("SELECT id FROM User WHERE email = ?");
    $stmt->execute([$company_admin_email]);
    if (!$stmt->fetch()) {
        $company_admin_id = uuid();
        $db->prepare("INSERT INTO User (id, full_name, email, role, password, company_id) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$company_admin_id, 'Firma Yetkilisi', $company_admin_email, 'company', password_hash('firma123', PASSWORD_DEFAULT), $company_id]);
        echo "firma yöneticisi '{$company_admin_email}' oluşturuldu ve '{$company_name}' firmasına atandı.\n";
    } else {
        echo "ℹFirma yöneticisi '{$company_admin_email}' zaten mevcut, atlandı.\n";
    }
    

    $stmt = $db->prepare("SELECT id FROM Trips WHERE departure_city = ? AND destination_city = ?");
    $stmt->execute(['İstanbul', 'Ankara']);
    if(!$stmt->fetch()){
        $trip_id = uuid();
        $db->prepare("INSERT INTO Trips (id, company_id, destination_city, arrival_time, departure_time, departure_city, price, capacity) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
           ->execute([$trip_id, $company_id, 'Ankara', '2025-11-01 12:00', '2025-11-01 08:00', 'İstanbul', 250, 40]);
        echo "Test seferi (İstanbul -> Ankara) oluşturuldu.\n";
    } else {
        echo "iTest seferi zaten mevcut, atlandı.\n";
    }

 
    $coupon_code = 'HOSGELDIN10';
    $stmt = $db->prepare("SELECT id FROM Coupons WHERE code = ?");
    $stmt->execute([$coupon_code]);
    if(!$stmt->fetch()){
        $coupon_id = uuid();
        $db->prepare("INSERT INTO Coupons (id, code, discount, company_id, usage_limit, expire_date) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$coupon_id, $coupon_code, 10, $company_id, 100, '2026-12-31']);
        echo "Test kuponu ('{$coupon_code}') oluşturuldu.\n";
    } else {
        echo "iTest kuponu zaten .\n";
    }

    $db->commit();
    echo "işlem başarıyla tamamlandı.\n";

} catch (Exception $e) {
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "Seed hatası: " . $e->getMessage() . "\n";
}