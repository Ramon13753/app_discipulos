# Usa una imagen base de PHP con Nginx y PHP-FPM
# Usa una imagen base oficial de PHP-FPM para Alpine (ligera)
FROM php:8.1-fpm-alpine

# Instala Nginx
RUN apk add --no-cache nginx

# Copia tus archivos PHP al directorio de documentos web de Nginx
# y configura permisos
COPY . /var/www/html/
RUN chown -R nginx:nginx /var/www/html/

# Quita la configuración predeterminada de Nginx
RUN rm /etc/nginx/conf.d/default.conf

# Copia tu archivo de configuración de Nginx personalizado
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copia la configuración de PHP-FPM
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Exponer el puerto 80
EXPOSE 80

# Comando para iniciar PHP-FPM y Nginx
CMD ["sh", "-c", "php-fpm -F & nginx -g 'daemon off;'"]
