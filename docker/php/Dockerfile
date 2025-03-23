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

# Создание директорий для логов и кэша
RUN mkdir -p /var/log/php && \
    mkdir -p /var/www/.composer/cache && \
    chown -R www-data:www-data /var/log/php && \
    chown -R www-data:www-data /var/www/.composer

# Установка PHP расширений
RUN docker-php-ext-install -j$(nproc) pdo_pgsql pgsql zip opcache sockets \
    && docker-php-ext-enable pdo_pgsql pgsql zip opcache sockets

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка PHP
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Настройка для RabbitMQ
RUN pecl install amqp \
    && docker-php-ext-enable amqp

# Настройка Git для безопасной директории
RUN git config --system --add safe.directory '*'

# Рабочая директория
WORKDIR /var/www/html

# Копирование всего проекта
COPY . .

# Установка прав доступа
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# Пользователь
USER www-data