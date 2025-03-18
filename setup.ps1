# Проверка наличия Docker
if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Host "Docker не установлен. Пожалуйста, установите Docker."
    exit 1
}

# Проверка наличия Docker Compose
if (-not (Get-Command docker-compose -ErrorAction SilentlyContinue)) {
    Write-Host "Docker Compose не установлен. Пожалуйста, установите Docker Compose."
    exit 1
}

Write-Host "Начинаем установку проекта..."

# Включаем оптимизацию сборки
$env:COMPOSE_BAKE = "true"

# Копирование .env файла
if (-not (Test-Path .env)) {
    Write-Host "Копирование .env файла..."
    Copy-Item .env.example .env
}

# Сборка и запуск контейнеров
Write-Host "Сборка и запуск контейнеров..."
docker-compose up -d --build

# Ожидание готовности базы данных
Write-Host "Ожидание готовности PostgreSQL..."
do {
    Start-Sleep -Seconds 2
    $result = docker-compose exec -T postgres pg_isready -U www-data
} while ($LASTEXITCODE -ne 0)

# Установка зависимостей
Write-Host "Установка зависимостей Composer..."
docker-compose exec -T app composer install
if ($LASTEXITCODE -ne 0) {
    Write-Host "Ошибка установки зависимостей Composer."
    exit 1
}

# Генерация ключа приложения
Write-Host "Генерация ключа приложения..."
docker-compose exec -T app php artisan key:generate
if ($LASTEXITCODE -ne 0) {
    Write-Host "Ошибка генерации ключа приложения."
    exit 1
}

# Миграции
Write-Host "Выполнение миграций..."
docker-compose exec -T app php artisan migrate
if ($LASTEXITCODE -ne 0) {
    Write-Host "Ошибка выполнения миграций."
    exit 1
}

# Очистка кэша
Write-Host "Очистка кэша..."
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan route:clear

Write-Host "Установка завершена!"
Write-Host "Проект доступен по адресу: http://localhost"
Write-Host "RabbitMQ Management: http://localhost:15672"
Write-Host "PostgreSQL доступен на порту: 5432" 