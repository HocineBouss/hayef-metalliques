FROM php:8.2-apache

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=prod \
    APP_DEBUG=0 \
    APACHE_DOCUMENT_ROOT=/var/www/html/public

# Activer mod_rewrite pour les routes
RUN a2enmod rewrite

# Installer les dépendances système et PHP nécessaires
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev \
 && docker-php-ext-install pdo pdo_mysql intl \
 && rm -rf /var/lib/apt/lists/*

# Copier le binaire composer officiel
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier tout le projet (avec les assets)
COPY . .

# Donner les bons droits sur les fichiers publics
RUN chown -R www-data:www-data public || true
RUN [ -d var ] && chown -R www-data:www-data var || true


# Configurer Apache pour pointer vers /public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf

# Forcer la réécriture vers index.php
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/symfony.conf \
 && a2enconf symfony

EXPOSE 80
CMD ["apache2-foreground"]
