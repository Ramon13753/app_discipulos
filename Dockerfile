# Usa una imagen base oficial de PHP-FPM y Nginx
FROM richarvey/nginx-php-fpm:latest

# Copia todos tus archivos del repositorio a la carpeta web de Nginx
COPY . /var/www/html/

# Copia TU ARCHIVO nginx.conf para que sobrescriba la configuración por defecto de Nginx
COPY nginx.conf /etc/nginx/sites-enabled/default.conf

# Declara que el contenedor expone el puerto 80
EXPOSE 80

# Comando para iniciar PHP-FPM y Nginx usando supervisord (gestor de procesos)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
