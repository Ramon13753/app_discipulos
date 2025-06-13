# Usa una imagen base de PHP con Nginx y PHP-FPM
FROM richarvey/nginx-php-fpm:latest

# Copia tus archivos PHP al directorio de trabajo de Nginx
COPY . /var/www/html/

# ***************************************************************
# *** ESTA ES LA LÍNEA CRÍTICA QUE DEBEMOS REVISAR/AJUSTAR ****
# ***************************************************************
# Copia tu archivo de configuración personalizado de Nginx
# y sobrescribe el predeterminado en el contenedor
COPY nginx.conf /etc/nginx/sites-enabled/default.conf

# Expone el puerto 80 (puerto estándar HTTP)
EXPOSE 80

# Comando para iniciar Nginx y PHP-FPM usando supervisord (incluido en la imagen)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
