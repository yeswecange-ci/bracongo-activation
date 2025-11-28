# 🖼️ Fix Permanent - Images en Production

## 🎯 Problème

Les images des partenaires et autres médias ne s'affichent pas en production.

**Symptômes :**
- ❌ Logos des partenaires vides
- ❌ Images uploadées ne s'affichent pas
- ❌ Erreur 404 sur `/storage/...`

---

## 🔍 Causes Possibles

### 1. **Lien symbolique manquant**
Laravel stocke les fichiers publics dans `storage/app/public` mais ils doivent être accessibles via `public/storage`.

### 2. **Permissions incorrectes**
Les dossiers `storage/` n'ont pas les bonnes permissions.

### 3. **URL HTTPS non forcée**
En production derrière un proxy (Coolify), Laravel doit forcer HTTPS.

---

## ✅ SOLUTION PERMANENTE

### **A. Script de Déploiement Automatique**

Un script `.coolify/deploy.sh` a été créé pour **automatiser** ces étapes à chaque déploiement.

**Contenu du script :**
```bash
#!/bin/bash

# 1. Créer le lien symbolique
php artisan storage:link --force

# 2. Permissions
chmod -R 755 storage
chmod -R 755 storage/app/public

# 3. Migrations
php artisan migrate --force

# 4. Caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **B. Configuration Coolify**

#### **1. Dans les paramètres de l'application Coolify**

**General → Post Deployment Command :**
```bash
bash .coolify/deploy.sh
```

**Ou directement :**
```bash
php artisan storage:link --force && chmod -R 755 storage && php artisan migrate --force && php artisan optimize:clear
```

#### **2. Variables d'environnement**

Vérifier dans **Environment Variables** :
```env
APP_ENV=production
APP_URL=https://wabracongo.ywcdigital.com
FILESYSTEM_DISK=public
```

---

## 🛠️ FIX MANUEL (si besoin)

### **1. Connexion SSH au container**

Dans Coolify → **Terminal**

### **2. Exécuter les commandes**

```bash
# Créer le lien symbolique
php artisan storage:link --force

# Vérifier que le lien existe
ls -la public/storage

# Devrait afficher : public/storage -> ../storage/app/public

# Donner les permissions
chmod -R 755 storage
chmod -R 755 storage/app/public
chmod -R 755 bootstrap/cache

# Vérifier les permissions
ls -la storage/app/

# Devrait afficher : drwxr-xr-x pour public/

# Vider les caches
php artisan optimize:clear
php artisan config:cache
```

### **3. Tester**

```bash
# Créer un fichier test
echo "TEST" > storage/app/public/test.txt

# Vérifier qu'il est accessible
curl https://wabracongo.ywcdigital.com/storage/test.txt

# Devrait afficher : TEST

# Supprimer le test
rm storage/app/public/test.txt
```

---

## 🔧 Configuration Laravel

### **A. AppServiceProvider.php**

**Fichier :** `app/Providers/AppServiceProvider.php`

```php
public function boot(): void
{
    Schema::defaultStringLength(191);

    // Forcer HTTPS en production
    if (config('app.env') === 'production' || request()->header('X-Forwarded-Proto') === 'https') {
        URL::forceScheme('https');
    }
}
```

✅ **Déjà configuré**

### **B. bootstrap/app.php**

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ]);

    // Faire confiance aux proxies
    $middleware->trustProxies(at: '*');
})
```

✅ **Déjà configuré**

### **C. config/filesystems.php**

```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

✅ **Déjà configuré**

---

## 📋 Checklist de Vérification

Après chaque déploiement, vérifier :

- [ ] Le lien symbolique existe : `ls -la public/storage`
- [ ] Les permissions sont correctes : `ls -la storage/app/`
- [ ] Les images s'affichent : https://wabracongo.ywcdigital.com/admin/partners
- [ ] Les uploads fonctionnent : Uploader un logo de partenaire
- [ ] L'URL est en HTTPS : Vérifier le src des images dans le HTML

---

## 🧪 Tests

### **Test 1 : Upload d'image**

1. Aller sur **Admin → Partenaires → Créer**
2. Uploader un logo
3. Sauvegarder
4. **Vérifier** : Le logo s'affiche dans la liste

### **Test 2 : Inspection HTML**

1. Aller sur **Admin → Partenaires**
2. **Clic droit → Inspecter** sur une image
3. Vérifier l'URL : `https://wabracongo.ywcdigital.com/storage/partners/...`
4. **Tester l'URL** : Ouvrir dans un nouvel onglet
5. **Doit afficher** : L'image

### **Test 3 : Vérification du lien**

```bash
# Dans le terminal Coolify
ls -la public/storage

# Résultat attendu :
# lrwxrwxrwx 1 user user 27 Nov 28 12:00 public/storage -> ../storage/app/public
```

---

## 🚨 Troubleshooting

### **Problème 1 : Lien symbolique manquant**

**Symptôme :** `ls -la public/storage` retourne "No such file or directory"

**Solution :**
```bash
php artisan storage:link --force
```

### **Problème 2 : Permission denied**

**Symptôme :** Erreur lors de l'upload ou images cassées

**Solution :**
```bash
chmod -R 755 storage
chmod -R 755 storage/app/public
chown -R www-data:www-data storage  # Si nécessaire
```

### **Problème 3 : Images en HTTP au lieu de HTTPS**

**Symptôme :** Les URLs des images sont `http://` au lieu de `https://`

**Solution :**
1. Vérifier que `APP_URL=https://...` dans `.env`
2. Vérifier que `URL::forceScheme('https')` est actif
3. Vider les caches : `php artisan optimize:clear`

### **Problème 4 : Images cassées après redéploiement**

**Symptôme :** Après chaque déploiement, les images ne s'affichent plus

**Solution :**
1. Configurer le **Post Deployment Command** dans Coolify
2. Ou exécuter manuellement après chaque déploiement :
```bash
php artisan storage:link --force
chmod -R 755 storage
```

---

## 🎯 Solution Rapide (1 minute)

Si les images ne s'affichent plus, exécuter dans le terminal Coolify :

```bash
php artisan storage:link --force && chmod -R 755 storage && php artisan optimize:clear
```

---

## 📌 Configuration Recommandée Coolify

### **Post Deployment Command**
```bash
bash .coolify/deploy.sh
```

### **Ou (sans script) :**
```bash
php artisan storage:link --force && chmod -R 755 storage && php artisan migrate --force && php artisan optimize:clear && php artisan config:cache
```

---

## ✅ Vérification Finale

Une fois tout configuré, vérifier :

1. **Page Partenaires :** https://wabracongo.ywcdigital.com/admin/partners
   - ✅ Les logos s'affichent

2. **Upload Test :**
   - ✅ Uploader un nouveau logo
   - ✅ Le logo s'affiche immédiatement

3. **Après Redéploiement :**
   - ✅ Les images persistent
   - ✅ Pas besoin d'action manuelle

---

## 📖 Documentation Technique

### **Comment Laravel gère les images publiques**

```
storage/app/public/       ← Fichiers réels stockés ici
         └── partners/
             └── logo.png

public/storage/           ← Lien symbolique (accessible via web)
       └── partners/
           └── logo.png  (lien vers storage/app/public/partners/logo.png)
```

**URL générée :**
```
https://wabracongo.ywcdigital.com/storage/partners/logo.png
```

**Résolution :**
```
public/storage/partners/logo.png → storage/app/public/partners/logo.png
```

---

## 🎉 Résultat Final

Après configuration :

✅ **Les images fonctionnent toujours**
✅ **Même après redéploiement**
✅ **Aucune action manuelle requise**
✅ **URLs en HTTPS**
✅ **Permissions correctes**

---

**Date de création :** 28 Novembre 2025
**Status :** ✅ Solution permanente implémentée
