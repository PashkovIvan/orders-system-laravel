#!/bin/bash

# Проверка наличия Docker
if ! command -v docker &> /dev/null; then
    echo "Docker не установлен. Пожалуйста, установите Docker."
    exit 1
fi

# Проверка наличия Docker Compose
if ! command -v docker-compose &> /dev/null; then
    echo "Docker Compose не установлен. Пожалуйста, установите Docker Compose."
    exit 1
fi

echo "🚀 Начинаем установку проекта..."

# Копирование .env файла
if [ ! -f .env ]; then
    echo "📝 Копирование .env файла..."
    cp .env.example .env
fi

# Сборка и запуск контейнеров
echo "🏗️  Сборка и запуск контейнеров..."
docker-compose up -d --build

# Ожидание готовности базы данных
echo "⏳ Ожидание готовности PostgreSQL..."
until docker-compose exec -T postgres pg_isready -U www-data; do
    sleep 2
done

# Установка зависимостей
echo "📦 Установка зависимостей Composer..."
if ! docker-compose exec -T app composer install; then
    echo "❌ Ошибка установки зависимостей Composer."
    exit 1
fi

# Генерация ключа приложения
echo "🔑 Генерация ключа приложения..."
if ! docker-compose exec -T app php artisan key:generate; then
    echo "❌ Ошибка генерации ключа приложения."
    exit 1
fi

# Миграции
echo "🔄 Выполнение миграций..."
if ! docker-compose exec -T app php artisan migrate; then
    echo "❌ Ошибка выполнения миграций."
    exit 1
fi

# Права на папки
echo "📂 Настройка прав на папки..."
docker-compose exec -T app bash -c "chmod -R 755 storage bootstrap/cache"

# Очистка кэша
echo "🧹 Очистка кэша..."
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan route:clear

echo "✅ Установка завершена!"
echo "🌐 Проект доступен по адресу: http://localhost"
echo "📊 RabbitMQ Management: http://localhost:15672"
echo "💾 PostgreSQL доступен на порту: 5432"
