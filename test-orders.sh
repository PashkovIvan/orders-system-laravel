#!/bin/bash

# Цвета для вывода
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}🚀 Начинаем тестирование системы заказов...${NC}"

# Проверяем, запущены ли все сервисы
echo -e "\n${YELLOW}Проверка статуса сервисов...${NC}"
if ! docker-compose ps | grep -q "app.*running"; then
    echo "❌ Приложение не запущено"
    exit 1
fi

if ! docker-compose ps | grep -q "rabbitmq.*running"; then
    echo "❌ RabbitMQ не запущен"
    exit 1
fi

echo -e "${GREEN}✓ Все сервисы запущены${NC}"

# Очищаем логи
echo -e "\n${YELLOW}Очистка логов...${NC}"
docker-compose exec app truncate -s 0 storage/logs/laravel.log
echo -e "${GREEN}✓ Логи очищены${NC}"

# Запускаем мониторинг в отдельном терминале
echo -e "\n${YELLOW}Запуск мониторинга...${NC}"
gnome-terminal -- docker-compose exec app php artisan orders:monitor || \
xterm -e "docker-compose exec app php artisan orders:monitor" || \
echo "❌ Не удалось открыть новый терминал для мониторинга"

# Запускаем тесты
echo -e "\n${YELLOW}Запуск тестовых сценариев...${NC}"
docker-compose exec app php artisan orders:test

# Показываем логи
echo -e "\n${YELLOW}Последние записи лога:${NC}"
docker-compose exec app tail -n 20 storage/logs/laravel.log

echo -e "\n${GREEN}✅ Тестирование завершено${NC}"
echo "Для просмотра полных логов выполните:"
echo "docker-compose exec app tail -f storage/logs/laravel.log" 