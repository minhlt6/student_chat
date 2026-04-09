# 1. Dùng ảnh gốc là PHP 8.2 kèm máy chủ Apache
FROM php:8.2-apache

# 2. Cài đặt các công cụ hệ thống và thư viện database (PostgreSQL cho Neon)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpq-dev \
    libpng-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip gd

# 3. Bật tính năng rewrite của Apache để Laravel chạy được các đường dẫn (Route)
RUN a2enmod rewrite

# 4. Đổi thư mục gốc của web vào thư mục public/ của Laravel (Bảo mật)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Copy toàn bộ code từ máy Tài (hoặc Repo) vào trong máy chủ Docker
WORKDIR /var/www/html
COPY . /var/www/html

# 6. Cài đặt Composer phiên bản mới nhất
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 7. Cài đặt các thư viện PHP (vendor) và tối ưu hóa
RUN composer install --no-dev --optimize-autoloader

# 8. Cấp quyền ghi cho thư mục storage và cache (Nếu không cấp sẽ bị lỗi 500)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 9. LỆNH "VÀNG" ĐỂ VƯỢT RÀO GÓI FREE:
# Tự động chạy Migrate sang Neon rồi mới bật Apache để chạy Web
CMD php artisan migrate --force && apache2-foreground