# Usa una imagen oficial de PHP-FPM con Alpine (ligera y estable)
FROM php:8.1-fpm-alpine

# Instala Nginx
RUN apk add --no-cache nginx

# Configuración para Nginx
# Aquí creamos un archivo de configuración de Nginx directamente
RUN echo 'server { listen 80; root /var/www/html; index index.php index.html; location / { try_files $uri $uri/ =404; } location ~ \.php$ { fastcgi_pass 127.0.0.1:9000; fastcgi_index index.php; include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; } }' > /etc/nginx/conf.d/default.conf

# Configuración para PHP-FPM (para que escuche en el puerto 9000)
RUN echo '[www] user = www-data; group = www-data; listen = 127.0.0.1:9000; pm = dynamic; pm.max_children = 5; pm.start_servers = 2; pm.min_spare_servers = 1; pm.max_spare_servers = 3; catch_workers_output = yes;' > /usr/local/etc/php-fpm.d/www.conf

# Copia tus archivos PHP al directorio de documentos web de Nginx
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/

# Exponer el puerto 80
EXPOSE 80

# Comando para iniciar PHP-FPM y Nginx en primer plano
CMD ["sh", "-c", "php-fpm && nginx -g 'daemon off;'"]
