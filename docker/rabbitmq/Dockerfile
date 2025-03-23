FROM rabbitmq:3-management-alpine

# Копируем конфигурации
COPY ./rabbitmq.conf /etc/rabbitmq/rabbitmq.conf
COPY ./definitions.json /etc/rabbitmq/definitions.json
COPY ./enabled_plugins /etc/rabbitmq/enabled_plugins

# Устанавливаем права
RUN chown rabbitmq:rabbitmq /etc/rabbitmq/rabbitmq.conf \
    && chown rabbitmq:rabbitmq /etc/rabbitmq/definitions.json \
    && chown rabbitmq:rabbitmq /etc/rabbitmq/enabled_plugins