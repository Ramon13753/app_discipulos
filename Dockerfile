# Usa una imagen oficial de PHP-FPM con Bullseye
FROM php:8.1-fpm-bullseye

# Instala las extensiones PHP necesarias para MySQL
# 'pdo' y 'pdo_mysql' también son buenas prácticas para bases de datos
RUN docker-php-ext-install pdo pdo_mysql mysqli && \
    docker-php-ext-enable pdo pdo_mysql mysqli

# Instala Nginx y otras herramientas básicas
# Usamos apt-get en imágenes basadas en Debian como Bullseye
# --no-install-recommends ayuda a mantener la imagen más pequeña
RUN apt-get update && \
    apt-get install -y --no-install-recommends nginx procps

# Copia tu nginx.conf personalizado
COPY nginx.conf /etc/nginx/nginx.conf

# Crea y asigna permisos a los directorios de logs de Nginx
# En imágenes PHP-FPM basadas en Debian, 'www-data' es el usuario común para webserver/PHP.
# Configuraremos Nginx para que corra bajo 'www-data' también.
RUN mkdir -p /var/log/nginx && \
    chown -R www-data:www-data /var/log/nginx && \
    chmod -R 755 /var/log/nginx

# Copia el código de tu aplicación PHP a la ubicación donde Nginx la busca
COPY . /var/www/html

# Copia el script de inicio
COPY start.sh /usr/local/bin/start.sh

# Haz el script ejecutable
RUN chmod +x /usr/local/bin/start.sh

# Expón el puerto 80 (Nginx)
EXPOSE 80

# Define el comando que inicia ambos servicios usando el script de inicio
CMD ["/usr/local/bin/start.sh"]
