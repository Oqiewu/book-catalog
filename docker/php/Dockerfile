# Используем PHP 8.2 FPM
FROM php:8.2-fpm

# Устанавливаем системные зависимости и расширения PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    libonig-dev \
    && docker-php-ext-install pdo pdo_mysql gd zip mbstring

# Установка Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Копируем весь проект
COPY . .

# Устанавливаем зависимости внутри контейнера
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Команда по умолчанию
CMD ["php-fpm"]
