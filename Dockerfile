# Usa una imagen oficial de PHP-FPM con Alpine (ligera y estable)
#FROM php:8.1-fpm-alpine
FROM php:8.1-fpm-bullseye

# Instala Nginx
RUN apk add --no-cache nginx

# Crea los directorios de logs de Nginx y asigna permisos
RUN mkdir -p /var/log/nginx \
    && chown -R nginx:nginx /var/log/nginx \
    && chmod -R 755 /var/log/nginx

# Copia tu nginx.conf personalizado para que reemplace el original
COPY nginx.conf /etc/nginx/nginx.conf

# --- NUEVAS LÍNEAS IMPORTANTES ---
# Copia tu aplicación PHP a la ubicación donde Nginx la busca
COPY . /var/www/html

# Copia el script de inicio
COPY start.sh /usr/local/bin/start.sh

# Haz el script ejecutable
RUN chmod +x /usr/local/bin/start.sh

# Expón el puerto 80 (Nginx)
EXPOSE 80

# Define el comando que inicia ambos servicios
CMD ["/usr/local/bin/start.sh"]
