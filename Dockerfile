# Usa una imagen oficial de PHP-FPM con Alpine (ligera y estable)
FROM php:8.1-fpm-alpine

# Instala Nginx
RUN apk add --no-cache nginx

# Crea el directorio de configuración para Nginx si no existe
RUN mkdir -p /etc/nginx/conf.d

# Copia tu archivo de configuración de Nginx personalizado
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Crea el directorio de configuración para PHP-FPM si no existe
RUN mkdir -p /usr/local/etc/php-fpm.d

# Copia tu archivo de configuración de PHP-FPM
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copia tus archivos PHP al directorio de documentos web de Nginx
COPY . /var/www/html/
# Asegura que los permisos de los archivos sean para el usuario 'nginx'
RUN chown -R nginx:nginx /var/www/html/

# Exponer el puerto 80
EXPOSE 80

# Comando para iniciar PHP-FPM y Nginx en primer plano
CMD ["sh", "-c", "php-fpm && nginx -g 'daemon off;'"]
