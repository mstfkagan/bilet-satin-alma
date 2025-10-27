# PHP 8.2 Apache imajını kullan
FROM php:8.2-apache

# Sistem güncellemesi ve SQLite kurulumu
RUN apt-get update && apt-get install -y libsqlite3-dev unzip && \
    docker-php-ext-install pdo pdo_sqlite

# Apache yapılandırması
RUN a2enmod rewrite

# Proje dosyalarını container içine kopyala
COPY . /var/www/html/

# ----- YENİ EKLENEN KISIM -----
# Başlangıç script'ini kopyala ve çalıştırılabilir yap
COPY ./docker-entrypoint/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Container başladığında bu script'i çalıştır
ENTRYPOINT ["entrypoint.sh"]
# ENTRYPOINT'ten sonra çalışacak varsayılan komutu belirt
CMD ["apache2-foreground"]
# ----- YENİ EKLENEN KISIM SONU -----

# www-data kullanıcısına yetki ver
# NOT: Bu satırı artık entrypoint script'i yönettiği için silebiliriz veya bırakabiliriz.
# Temizlik açısından silelim.
# RUN chown -R www-data:www-data /var/www/html

# Port aç
EXPOSE 80