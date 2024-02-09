# Используем официальный образ PHP с FPM
FROM php:8.3-fpm

# Устанавливаем необходимые расширения PHP
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring xml

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Рабочая директория внутри контейнера
WORKDIR /var/www/symfony

# Копируем зависимости проекта
COPY . /var/www/symfony

# Устанавливаем зависимости с использованием Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install

# Устанавливаем права доступа (это зависит от конфигурации вашего приложения)
RUN chown -R www-data:www-data /var/www/symfony/var

# Запускаем PHP-FPM
CMD ["php-fpm"]
