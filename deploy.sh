#!/bin/bash

# Naviguer vers le répertoire du projet
cd /home/ubuntu/var/www/Metalcash-Attendance-App

# Tirer les nouvelles modifications depuis GitHub
git pull origin main

# Installer les dépendances PHP
composer install --no-interaction --prefer-dist --optimize-autoloader

# Compiler les assets pour Inertia et Vue
npm install
npm run build

# Appliquer les migrations de la base de données si nécessaire
php artisan migrate --force

# Donner les bonnes permissions aux fichiers
chown -R www-data:www-data /home/ubuntu/var/www/Metalcash-Attendance-App
chmod -R 775 /home/ubuntu/var/www/Metalcash-Attendance-App/storage
chmod -R 775 /home/ubuntu/var/www/Metalcash-Attendance-App/bootstrap/cache

# Redémarrer les services si nécessaire
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
