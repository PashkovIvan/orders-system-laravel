FROM php:8.3-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    librabbitmq-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Установка PHP расширений
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    zip \
    opcache

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка PHP
COPY php.ini /usr/local/etc/php/php.ini
COPY conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Настройка для RabbitMQ
RUN pecl install amqp \
    && docker-php-ext-enable amqp

# Рабочая директория
WORKDIR /var/www/html

# Права на папки
RUN chown -R www-data:www-data /var/www/html

# Пользователь
USER www-data