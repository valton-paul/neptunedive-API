FROM php:8.3-apache

# Installer les dépendances nécessaires pour Composer et Node.js
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    && apt-get clean

# Installer Node.js 20.17 et npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs=20.17.0-1nodesource1 \
    && apt-get clean

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Activer le module de réécriture d'Apache
RUN a2enmod rewrite

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql

# Copier le code source backend de l'application
COPY . /var/www/html/neptunedive

# Copier la configuration d'Apache personnalisée
COPY ./docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Activer le site Apache par défaut
RUN a2ensite 000-default.conf

# Configurer les permissions pour le dossier public
RUN chown -R www-data:www-data /var/www/html/neptunedive/public
RUN chmod -R 755 /var/www/html/neptunedive/public

# Exposer le port 80
EXPOSE 80
