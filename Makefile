.PHONY: setup start stop restart logs ps shell test

# Основные команды
setup: ## Первоначальная настройка проекта
	@chmod +x setup.sh
	@./setup.sh

build: ## Сборка образа
	docker-compose build --no-cache

start: ## Запуск контейнеров
	docker-compose up -d

stop: ## Остановка контейнеров
	docker-compose down

restart: stop start ## Перезапуск контейнеров

# Логи
logs: ## Просмотр логов всех контейнеров
	docker-compose logs -f

logs-app: ## Просмотр логов PHP
	docker-compose logs -f app

logs-nginx: ## Просмотр логов Nginx
	docker-compose logs -f nginx

# Работа с приложением
shell: ## Bash в контейнере PHP
	docker-compose exec app bash

test: ## Запуск тестов
	docker-compose exec app php artisan test

# Composer
composer-install: ## Установка зависимостей
	docker-compose exec app composer install

composer-update: ## Обновление зависимостей
	docker-compose exec app composer update

# Artisan команды
migrate: ## За��уск миграций
	docker-compose exec app php artisan migrate

migrate-fresh: ## Пересоздание таблиц
	docker-compose exec app php artisan migrate:fresh

cache-clear: ## Очистка кэша
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear

# Статус
ps: ## Статус контейнеров
	docker-compose ps

help: ## Помощь
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.DEFAULT_GOAL := help 