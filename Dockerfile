# Dùng ảnh gốc là PHP 8.2 và máy chủ Apache
FROM php:8.2-apache

# Cài đặt các công cụ cần thiết và thư viện database (Hỗ trợ cả MySQL và PostgreSQL)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

# Bật tính năng định tuyến của Apache cho Laravel
RUN a2enmod rewrite

# Đổi thư mục gốc của web vào thẳng thư mục public/ của Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy toàn bộ code của bạn vào trong máy chủ
COPY . /var/www/html

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache