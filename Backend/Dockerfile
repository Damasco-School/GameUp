FROM php:8.2-apache
RUN docker-php-ext-install mysqli
RUN a2enmod rewrite headers
COPY index.php /var/www/html/index.php
COPY .htaccess /var/www/html/.htaccess

# Passa le variabili d'ambiente Docker a PHP/Apache
RUN echo "PassEnv DB_HOST DB_USER DB_PASS DB_NAME" >> /etc/apache2/apache2.conf

# Abilita .htaccess nelle directory
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

EXPOSE 80
