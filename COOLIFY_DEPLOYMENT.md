# 🚀 Guide de Déploiement Coolify - CAN 2025

## 🎯 Problème Résolu : Styles ne s'appliquent pas

### Cause
Laravel générait des URLs HTTP au lieu de HTTPS, causant des erreurs "Mixed Content" bloquées par le navigateur.

### Solution Appliquée ✅
Activation de `URL::forceScheme('https')` dans `AppServiceProvider.php`

---

## 📋 Checklist Déploiement Coolify

### 1. Configuration de l'Environnement (.env)

Dans Coolify, configure les variables d'environnement suivantes :

```env
# Application
APP_NAME="CAN 2025 Kinshasa"
APP_ENV=production
APP_KEY=base64:... (généré par php artisan key:generate)
APP_DEBUG=false
APP_URL=https://wabracongo.ywcdigital.com

# Database
DB_CONNECTION=mysql
DB_HOST=mysql  # ou l'IP de ton service MySQL Coolify
DB_PORT=3306
DB_DATABASE=can_activation
DB_USERNAME=root
DB_PASSWORD=ton_password_securise

# Twilio (à configurer)
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# IMPORTANT pour Coolify/Docker
ASSET_URL=https://wabracongo.ywcdigital.com
```

**⚠️ IMPORTANT :**
- `APP_ENV` DOIT être `production` (pas `local`)
- `APP_URL` DOIT être en `https://`

---

### 2. Build des Assets

Avec Coolify, tu as 2 options :

#### Option A : Build dans le Dockerfile (Recommandé)

Ajoute ces commandes à ton Dockerfile ou au script de build Coolify :

```dockerfile
# Install Node.js dependencies
RUN npm install

# Build assets pour production
RUN npm run build

# S'assurer que les assets sont accessibles
RUN chmod -R 755 public/build
```

#### Option B : Build en local et commit

```bash
# Sur ton PC local
npm run build

# Commit le dossier build
git add public/build/ -f
git commit -m "Add production build assets"
git push origin main
```

---

### 3. Configuration Coolify

Dans les paramètres de ton application Coolify :

#### Build Command
```bash
composer install --optimize-autoload --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Start Command
```bash
php artisan migrate --force
php artisan serve --host=0.0.0.0 --port=8000
```

Ou avec un serveur web :
```bash
apache2-foreground
# ou
php-fpm
```

---

### 4. Volumes Persistants

Configure ces volumes dans Coolify pour persister les données :

```yaml
volumes:
  - ./storage:/var/www/html/storage
  - ./public/build:/var/www/html/public/build
```

---

### 5. CRON pour le Scheduler

Ajoute un service séparé dans Coolify ou configure le CRON dans ton conteneur :

**Dans le Dockerfile :**
```dockerfile
# Installer cron
RUN apt-get update && apt-get install -y cron

# Ajouter le crontab Laravel
RUN echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" | crontab -

# Démarrer cron
CMD cron && apache2-foreground
```

**Ou créer un service séparé :**
```yaml
scheduler:
  image: ton-image-laravel
  command: |
    while true; do
      php /var/www/html/artisan schedule:run
      sleep 60
    done
```

---

### 6. Trusted Proxies (Important pour HTTPS)

Si les URLs sont toujours en HTTP, édite `app/Http/Middleware/TrustProxies.php` :

```php
protected $proxies = '*';  // Faire confiance à tous les proxies en production
```

---

## 🔍 Vérification Post-Déploiement

### Test 1 : Vérifier que les assets sont en HTTPS

```bash
curl -I https://wabracongo.ywcdigital.com/build/assets/app-Bz2lFR3n.css
```

**Résultat attendu :** HTTP/1.1 200 OK

### Test 2 : Vérifier le HTML généré

```bash
curl -s https://wabracongo.ywcdigital.com/admin/login | grep "stylesheet"
```

**Résultat attendu :** Les URLs doivent être en `https://`

### Test 3 : Vérifier dans le navigateur

1. Ouvre : https://wabracongo.ywcdigital.com/admin/login
2. Ouvre la Console du navigateur (F12)
3. Onglet "Network" → Vérifie qu'il n'y a pas d'erreurs "Mixed Content"
4. Les styles doivent être appliqués ✅

---

## 🐛 Troubleshooting

### Problème 1 : Les styles ne s'appliquent toujours pas

**Vérifier APP_ENV :**
```bash
# Dans le conteneur Coolify
php artisan tinker
>>> config('app.env')
// Doit retourner "production"
```

**Si ce n'est pas "production" :**
1. Va dans Coolify → Variables d'environnement
2. Change `APP_ENV=production`
3. Redémarre le conteneur

### Problème 2 : Erreur 404 sur les assets

**Vérifier que les fichiers existent :**
```bash
# Dans le conteneur
ls -la /var/www/html/public/build/
ls -la /var/www/html/public/build/assets/
```

**Si le dossier est vide :**
- Option 1 : Rebuild avec `npm run build` inclus
- Option 2 : Commit le dossier `public/build/` dans Git

### Problème 3 : Erreur "Vite manifest not found"

**Vérifier que le manifest existe :**
```bash
ls -la /var/www/html/public/build/manifest.json
```

**Si absent :**
```bash
# Copier depuis .vite/
cp /var/www/html/public/build/.vite/manifest.json /var/www/html/public/build/
```

### Problème 4 : Permissions

**Corriger les permissions :**
```bash
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/public/build
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/public/build
```

---

## 📝 Commandes Utiles Coolify

### Accéder au conteneur
```bash
# Depuis Coolify UI : Terminal
# Ou depuis SSH :
docker exec -it nom-du-conteneur bash
```

### Vider les caches
```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Vérifier les logs
```bash
tail -f /var/www/html/storage/logs/laravel.log
```

### Rebuild les assets
```bash
npm run build
```

---

## ✅ Checklist Finale

Après le déploiement, vérifie :

- [ ] ✅ `APP_ENV=production` dans .env
- [ ] ✅ `APP_URL=https://...` dans .env
- [ ] ✅ Les assets sont buildés (`public/build/` existe)
- [ ] ✅ Le manifest existe (`public/build/manifest.json`)
- [ ] ✅ Les styles s'appliquent sur le site
- [ ] ✅ Les migrations sont exécutées
- [ ] ✅ Au moins 1 admin créé
- [ ] ✅ Au moins 1 village actif créé
- [ ] ✅ CRON configuré pour le scheduler
- [ ] ✅ Credentials Twilio configurés
- [ ] ✅ APIs testées avec Postman

---

## 🎉 Résultat Attendu

Après avoir appliqué ces corrections :

1. ✅ Les styles CSS Tailwind s'appliquent correctement
2. ✅ Le dashboard admin s'affiche avec le design complet
3. ✅ Pas d'erreurs "Mixed Content" dans la console
4. ✅ Toutes les pages fonctionnent avec les styles

---

## 📞 Prochaines Étapes

1. **Tester les endpoints API** avec Postman
2. **Créer des données de test** (villages, matchs, etc.)
3. **Configurer Twilio Studio** avec les URLs de prod
4. **Tester le flow complet** WhatsApp

---

**Le déploiement Coolify est maintenant correctement configuré ! 🚀**
