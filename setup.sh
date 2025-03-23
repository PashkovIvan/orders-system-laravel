#!/bin/bash

# Проверка наличия Docker
if ! command -v docker &> /dev/null; then
    echo "🚫 Docker не установлен. Пожалуйста, установите Docker."
    exit 1
fi

# Проверка наличия Docker Compose
if ! command -v docker-compose &> /dev/null; then
    echo "🚫 Docker Compose не установлен. Пожалуйста, установите Docker Compose."
    exit 1
fi

echo "🚀 Начинаем установку проекта..."

# Включаем оптимизацию сборки
#export COMPOSE_BAKE=true
#export DOCKER_BUILDKIT=1
#export COMPOSE_DOCKER_CLI_BUILD=1

# Копирование .env файла
if [ ! -f .env ]; then
    echo "📄 Копирование .env файла..."
    cp .env.example .env
fi

# Очистка неиспользуемых ресурсов Docker
echo "🧹 Очистка неиспользуемых ресурсов Docker..."
docker system prune -f

# Сборка и запуск контейнеров
echo "🏗️  Сборка и запуск контейнеров..."
docker-compose up -d --build

# Установка зависимостей
echo "📦 Установка зависимостей Composer..."
if ! docker-compose exec -T app composer install; then
    echo "📦 Установка зависимостей не удалась, пробуем обновить..."
    if ! docker-compose exec -T app composer update; then
        echo "❌ Ошибка установки зависимостей Composer."
        exit 1
    fi
fi

# Генерация ключа приложения
echo "🔑 Генерация ключа приложения..."
docker-compose exec -T app php artisan key:generate || {
    echo "❌ Ошибка генерации ключа приложения."
    exit 1
}

# Ожидание готовности базы данных
echo "⏳ Ожидание готовности PostgreSQL..."
until docker-compose exec -T postgres pg_isready -U www-data; do
    sleep 2
done

# Миграции
echo "🔄 Выполнение миграций..."
docker-compose exec -T app php artisan migrate || {
    echo "❌ Ошибка выполнения миграций."
    exit 1
}

# Очистка кэша
echo "🧹 Очистка кэша..."
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan route:clear

echo "✨ Установка завершена!"
echo "🌐 Проект доступен по адресу: http://localhost"
echo "🐰 RabbitMQ Management: http://localhost:15672"
echo "🐘 PostgreSQL доступен на порту: 5432"
