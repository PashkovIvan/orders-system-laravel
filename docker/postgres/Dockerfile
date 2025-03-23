FROM postgres:15-alpine

# Устанавливаем пакеты для работы с локалями
RUN apk add --no-cache musl-locales musl-locales-lang

# Копируем конфиг
COPY ./postgresql.conf /etc/postgresql/postgresql.conf

# Копируем скрипты инициализации
COPY ./init/ /docker-entrypoint-initdb.d/

# Устанавливаем параметры локали
ENV LANG=en_US.utf8
ENV LC_ALL=en_US.utf8