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

# Esto es lo que necesitas cambiar:
COPY nginx.conf /etc/nginx/nginx.conf
#COPY nginx.conf /etc/nginx/conf.d/default.conf
# Cambia esta línea CMD para enfocarte en Nginx
CMD ["sh", "-c", "/usr/sbin/nginx -t && /usr/sbin/nginx -g 'daemon off;'"]
