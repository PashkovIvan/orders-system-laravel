-- Создаем пользователя www-data если не существует
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'www-data') THEN
        CREATE USER "www-data" WITH PASSWORD 'www-data';
    ELSE
        -- Если пользователь существует, обновляем пароль
        ALTER USER "www-data" WITH PASSWORD 'www-data';
    END IF;
END $$; 