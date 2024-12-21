# Order System

Система обработки и хранения заказов на Laravel.

## Технологии

- PHP 8.3
- Laravel 10.x
- PostgreSQL 15
- RabbitMQ 3.x
- Nginx
- Docker & Docker Compose

## Структура проекта

project/
├── docker/ # Docker конфигурации
│ ├── nginx/ # Настройки веб-сервера
│ ├── php/ # Настройки PHP и FPM
│ ├── postgres/ # Инициализация БД
│ └── rabbitmq/ # Настройки очередей
├── app/ # Основной код
├── config/ # Конфигурация
├── database/ # Миграции и сиды
├── tests/ # Тесты
├── ...
├── .editorconfig # Настройки редактора
├── .env.example # Пример переменных окружения
├── docker-compose.yml # Конфигурация Docker
├── Makefile # Команды для управления
└── README.md # Документация

## Быстрый старт

1. Клонировать репозиторий:

```bash
git clone <repository-url>
```

2. Запустить установку:

```bash
make setup
```

или из корня проекта для Windows (если нет make расширения)

```bash
sh setup.sh
```

После установки сервисы будут доступны:
- API: http://localhost
- RabbitMQ Management: http://localhost:15672
- PostgreSQL: localhost:5432

## API Endpoints

### Заказы
- `POST /api/orders` - Создание заказа
- `GET /api/orders` - Список заказов
- `GET /api/orders/{id}` - Детали заказа
- `PUT /api/orders/{id}` - Обновление заказа
- `DELETE /api/orders/{id}` - Удаление заказа

## Команды Make

### Основные команды
- `make setup` - Первоначальная настройка проекта
- `make start` - Запуск контейнеров
- `make stop` - Остановка контейнеров
- `make restart` - Перезапуск контейнеров

### Разработка
- `make shell` - Доступ к контейнеру PHP
- `make logs` - Просмотр логов
- `make test` - Запуск тестов

### Composer
- `make composer-install` - Установка зависимостей
- `make composer-update` - Обновление зависимостей

### Работа с БД
- `make migrate` - Запуск миграций
- `make migrate-fresh` - Пересоздание таблиц

## Разработка

### Запуск тестов

Все тесты
```bash
make test
```

Конкретный тест
```bash
docker-compose exec app php artisan test --filter=OrderTest
```

### Работа с очередями

Запуск обработчика
```bash
docker-compose exec app php artisan queue:work
```

Мониторинг
```bash
docker-compose exec app php artisan queue:monitor
```

### Создание тестовых данных
```bash
docker-compose exec app php artisan db:seed
```

## Мониторинг

### RabbitMQ Management
- URL: http://localhost:15672
- Login: guest
- Password: guest

### Логи

Все логи
```bash
make logs
```

Логи конкретного сервиса
```bash
docker-compose logs [service_name]
```