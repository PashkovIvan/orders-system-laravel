#!/bin/bash

echo "🚀 Запуск тестов системы заказов..."

# Цвета для вывода
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Функция для вывода времени выполнения
print_execution_time() {
    local start_time=$1
    local end_time=$2
    local duration=$(echo "$end_time - $start_time" | bc)
    echo -e "${YELLOW}⏱️  Время выполнения: ${duration} секунд${NC}"
}

# Запуск unit-тестов
echo -e "\n${YELLOW}🧪 Запуск unit-тестов...${NC}"
start_time=$(date +%s.%N)
docker-compose exec app php artisan test tests/Unit/OrderServiceTest.php
end_time=$(date +%s.%N)
print_execution_time $start_time $end_time

# Запуск тестов API
echo -e "\n${YELLOW}🌐 Запуск тестов API...${NC}"
start_time=$(date +%s.%N)
docker-compose exec app php artisan test tests/Feature/OrderControllerTest.php
end_time=$(date +%s.%N)
print_execution_time $start_time $end_time

# Запуск тестов валидации
echo -e "\n${YELLOW}✅ Запуск тестов валидации...${NC}"
start_time=$(date +%s.%N)
docker-compose exec app php artisan test tests/Feature/OrderValidationTest.php
end_time=$(date +%s.%N)
print_execution_time $start_time $end_time

# Запуск тестов очередей
echo -e "\n${YELLOW}🔄 Запуск тестов очередей...${NC}"
start_time=$(date +%s.%N)
docker-compose exec app php artisan test tests/Feature/OrderQueueTest.php
end_time=$(date +%s.%N)
print_execution_time $start_time $end_time

# Запуск тестов производительности
echo -e "\n${YELLOW}📊 Запуск тестов производительности...${NC}"
start_time=$(date +%s.%N)
docker-compose exec app php artisan test tests/Feature/OrderLoadTest.php
end_time=$(date +%s.%N)
print_execution_time $start_time $end_time

echo -e "\n${GREEN}✅ Все тесты завершены${NC}" 