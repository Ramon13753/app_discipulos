# Usa una imagen oficial de PHP-FPM con Alpine (ligera y estable)
FROM php:8.1-fpm-alpine

# Instala Nginx
RUN apk add --no-cache nginx

# Crea los directorios de logs de Nginx y asigna permisos
RUN mkdir -p /var/log/nginx \
    && chown -R nginx:nginx /var/log/nginx \
    && chmod -R 755 /var/log/nginx

# Crea el directorio de configuración para Nginx si no existe
RUN mkdir -p /etc/nginx/conf.d

# Copia tu archivo de configuración de Nginx personalizado
COPY nginx.conf /etc/nginx/conf.d/default.conf

# ... (el resto de tu Dockerfile se mantiene igual) ...

# REEMPLAZA las líneas de ENTRYPOINT y CMD anteriores con esta ÚNICA LÍNEA:
CMD ["sh", "-c", "php-fpm && nginx -g 'daemon off;'"]
