-- Создаем базу данных если не существует
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_database WHERE datname = 'orders_db') THEN
        EXECUTE 'CREATE DATABASE orders_db';
    END IF;
END $$;

-- Подключаемся к созданной БД
\c orders_db

-- Создаем расширения
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Даем права пользователю www-data
GRANT ALL PRIVILEGES ON DATABASE orders_db TO "www-data";
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "www-data";
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "www-data";

-- Создаем схему
CREATE SCHEMA IF NOT EXISTS orders;