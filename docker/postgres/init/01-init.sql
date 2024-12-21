DO $$
BEGIN
    -- Проверяем, существует ли база данных, и создаем её, если нет
    IF NOT EXISTS (SELECT 1 FROM pg_database WHERE datname = 'orders_db') THEN
        EXECUTE 'CREATE DATABASE orders_db';
    END IF;
END $$;

-- Подключаемся к созданной БД
\c orders_db

-- Создаем расширения
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Создаем схему
CREATE SCHEMA IF NOT EXISTS orders;