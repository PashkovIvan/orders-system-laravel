# Orders System

Система обработки заказов на Laravel с использованием RabbitMQ для асинхронной обработки.

## Технологический стек

- PHP 8.3
- Laravel 11.0
- PostgreSQL
- RabbitMQ
- Docker
- Docker Compose
- Nginx
- L5-Swagger (API документация)

## Требования

- Docker
- Docker Compose

## Быстрый старт

1. Клонируйте репозиторий:
```bash
git clone <repository-url>
cd orders-system-laravel
```

2. Запустите скрипт установки:
```bash
sh setup.sh
```

Скрипт выполнит следующие действия:
- Проверит наличие Docker и Docker Compose
- Создаст .env файл из .env.example
- Соберет и запустит контейнеры
- Установит зависимости Composer
- Сгенерирует ключ приложения
- Выполнит миграции
- Очистит кэш

После успешной установки сервисы будут доступны по следующим адресам:
- API: http://localhost
- Swagger UI: http://localhost/api/documentation
- RabbitMQ Management: http://localhost:15672
- PostgreSQL: localhost:5432

## Дополнительные скрипты

### full-rebuild.sh
Полная пересборка проекта:
```bash
sh full-rebuild.sh
```

### main-test.sh
Запуск всех тестов проекта:
```bash
sh main-test.sh
```
Выполняет:
- Unit тесты (OrderServiceTest)
- Feature тесты (OrderControllerTest)
- Тесты валидации (OrderValidationTest)
- Тесты очередей (OrderQueueTest)
- Тесты производительности (OrderLoadTest)

## API Документация

### Swagger UI

Для просмотра API документации в формате Swagger UI:

1. Убедитесь, что проект запущен
2. Выполните команду для генерации документации:
```bash
docker-compose exec app php artisan l5-swagger:generate
```
3. Откройте в браузере:
- Swagger UI: http://localhost/api/documentation

### Доступные эндпоинты

- `GET /api/v1/orders` - Получение списка заказов
  - Query параметры:
    - `page` (опционально) - номер страницы
    - `per_page` (опционально) - количество записей на странице
- `POST /api/v1/orders` - Создание нового заказа
  - Тело запроса:
    ```json
    {
      "customer_name": "string",
      "customer_email": "string",
      "items": [
        {
          "product_name": "string",
          "quantity": 1,
          "price": 0.00
        }
      ]
    }
    ```
- `GET /api/v1/orders/{id}` - Получение информации о заказе
- `PATCH /api/v1/orders/{id}/status` - Обновление статуса заказа
  - Тело запроса:
    ```json
    {
      "status": "pending|processing|completed|cancelled"
    }
    ```

## Архитектура

### Основные компоненты

- **Controllers**: Обработка HTTP запросов (`App\Http\Controllers\Api\V1`)
- **Services**: Бизнес-логика и обработка заказов
- **Jobs**: Асинхронные задачи для обработки заказов
- **DTOs**: Объекты передачи данных (`OrderData`, `OrderProcessingData`)
- **Models**: Eloquent модели для работы с базой данных
- **Messages**: Сообщения для RabbitMQ
- **Contracts**: Интерфейсы для реализации паттерна Repository

### Обработка заказов

1. Создание заказа:
   - Валидация входных данных через `OrderRequest`
   - Преобразование в `OrderData` DTO
   - Сохранение через `OrderService`
   - Отправка в очередь через `OrderProcessingService`

2. Обновление статуса:
   - Валидация через `OrderStatusRequest`
   - Использование `OrderProcessingData` DTO
   - Обработка через `OrderProcessingService`
   - Асинхронное обновление через RabbitMQ

### Статусы заказов

- `pending` - начальный статус
- `processing` - заказ в обработке
- `completed` - заказ выполнен
- `failed` - ошибка обработки
- `cancelled` - заказ отменен

Разрешенные переходы статусов:
- `pending` → `processing`, `cancelled`
- `processing` → `completed`, `failed`, `cancelled`
- `failed` → `processing`
- `completed` → нет переходов
- `cancelled` → нет переходов

## Docker контейнеры и ресурсы

- `nginx`: Веб-сервер
- `app`: Приложение/API
- `postgres`: База данных
- `rabbitmq`: Очередь сообщений

## Безопасность

- Валидация всех входных данных
- Безопасная обработка очередей
- Транзакционная обработка заказов
- Логирование всех операций
- Обработка ошибок и исключений

## Мониторинг

- RabbitMQ Management UI: http://localhost:15672
  - Логин: guest
  - Пароль: guest
- Логи приложения: 
```bash
docker-compose exec app tail -f storage/logs/laravel.log
```

## Code Style

Проект следует PSR-12 стандарту. Для проверки и исправления стиля кода:

```bash
# Проверка
docker-compose exec app composer format-check

# Исправление
docker-compose exec app composer format
```

## Лицензия

MIT