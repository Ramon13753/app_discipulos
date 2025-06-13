# Usa una imagen base de PHP con Nginx y PHP-FPM
FROM richarvey/nginx-php-fpm:latest

# Copia todos tus archivos (scripts PHP, etc.) al directorio de trabajo de Nginx
COPY . /var/www/html/

# Expone el puerto 80 (puerto estándar HTTP)
EXPOSE 80

# Comando para iniciar Nginx y PHP-FPM usando supervisord (incluido en la imagen)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
