<<<<<<< HEAD
# Bilet Satın Alma Platformu

Bu proje, PHP ve SQLite kullanılarak oluşturulmuş Docker tabanlı bir otobüs bileti satış platformudur.

## Kurulum Adımları

1.  Projeyi bilgisayarınıza indirin (`git clone` veya ZIP olarak).
2.  Terminali açın ve projenin ana dizinine gidin.
3.  Aşağıdaki komutlarla Docker container'ını başlatın ve veritabanını kurun:

    ```sh
    # 1. Container'ı başlatır
    docker-compose up -d --build

    # 2. Veritabanı tablolarını oluşturur
    docker exec bilet_satin php scripts/create_tables.php

    # 3. Test verilerini ve gizli şifreye sahip admin kullanıcısını oluşturur
    docker exec bilet_satin php scripts/seed_data.php
    ```

4.  Kurulum tamamlandıktan sonra, uygulamaya [http://localhost:8080](http://localhost:8080) adresinden erişebilirsiniz.

## Test Kullanıcı Bilgileri

Sistemi test etmek için aşağıdaki hesapları kullanabilirsiniz:

* **Firma Yetkilisi Hesabı:**
    * **E-posta:** `firma@example.com`


* **Standart Kullanıcı Hesabı:**
    * **E-posta:** `user@example.com`
    * **Şifre:** `user123`

* **Admin Hesabı:**
    * **E-posta:** `admin@example.com`

=======
# bilet-satin-alma
>>>>>>> 1c4271d945ccc231041356f0f016ca2216812591
