FROM php:8.1-apache

# تثبيت المكتبات المطلوبة
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libonig-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_sqlite mbstring zip

# تعطيل MPMs المتعارضة وتفعيل prefork
RUN a2dismod mpm_event mpm_worker && a2enmod mpm_prefork

# تفعيل mod_rewrite
RUN a2enmod rewrite

# نسخ الملفات
COPY . /var/www/html

# إعداد صلاحيات المجلدات
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/database \
    && chmod 777 /var/www/html/database

# إعداد Apache DocumentRoot
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80
