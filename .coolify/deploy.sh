#!/bin/bash

# Script de déploiement automatique pour Coolify
# Ce script s'exécute après chaque déploiement

echo "🚀 Début du déploiement..."

# 1. Créer le lien symbolique storage
echo "📁 Création du lien symbolique storage..."
php artisan storage:link --force

# 2. Donner les bonnes permissions
echo "🔐 Configuration des permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 755 storage/app/public

# 3. Appliquer les migrations
echo "💾 Application des migrations..."
php artisan migrate --force

# 4. Vider les caches
echo "🧹 Nettoyage des caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Déploiement terminé avec succès !"
