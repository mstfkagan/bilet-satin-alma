#!/bin/sh

# /var/www/html/data klasörünün sahibini www-data kullanıcısı yap.
# Bu, veritabanına yazma izni sorununu çözer.
chown -R www-data:www-data /var/www/html/data

# Dockerfile'da CMD olarak belirtilen asıl komutu çalıştır.
# Bizim durumumuzda bu komut "apache2-foreground" olacak.
exec "$@"