# Image de base PHP avec PHP-FPM
# PHP-FPM est utilisé par Nginx pour exécuter PHP
FROM php:8.4-fpm


# Installation des dépendances système nécessaires
# Ces librairies permettent de compiler certaines extensions PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zip \
    curl \
    && rm -rf /var/lib/apt/lists/*


# Installation des extensions PHP nécessaires pour Symfony
RUN docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip \
    opcache


# Installation de Composer depuis l'image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# Dossier de travail de l'application
WORKDIR /var/www/html


# Configuration PHP pour les uploads d'images
RUN echo "upload_max_filesize=20M\npost_max_size=50M" > /usr/local/etc/php/conf.d/uploads.ini


# Création du dossier où seront stockées les images uploadées
RUN mkdir -p /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html/public/uploads


# Commande exécutée au démarrage du container
CMD ["php-fpm"]