FROM nginx:alpine

# Удаляем дефолтный конфиг
RUN rm /etc/nginx/conf.d/default.conf

# Копируем наш конфиг
COPY conf.d/default.conf /etc/nginx/conf.d/

# Создаем директории для логов
RUN mkdir -p /var/log/nginx && \
    chown -R nginx:nginx /var/log/nginx

EXPOSE 80

# Запускаем nginx
CMD ["nginx", "-g", "daemon off;"]