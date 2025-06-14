#!/bin/sh

# Iniciar PHP-FPM en segundo plano
php-fpm -F &

# Iniciar Nginx en primer plano (para mantener el contenedor vivo)
nginx -g 'daemon off;'
