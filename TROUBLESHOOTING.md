# 🔧 Dépannage - CAN Activation Kinshasa

## ✅ Problèmes Résolus

### 1. Erreur QR Code: `Call to undefined method Builder::create()`

**Symptôme:**
```
Error: Call to undefined method Endroid\QrCode\Builder\Builder::create()
```

**Cause:** API de Endroid QR Code v6.0 a changé

**Solution:** ✅ CORRIGÉ
- Changement de `Builder::create()` vers `new Builder()`
- Utilisation de named parameters PHP 8.2+

---

### 2. Erreur WhatsApp: `error setting certificate file: C:\wamp64\cacert.pem`

**Symptôme:**
```
WhatsApp send error: error setting certificate file: C:\wamp64\cacert.pem
```

**Cause:** cURL ne trouve pas le certificat SSL sur Windows/WAMP

**Solution:** ✅ CORRIGÉ
- Désactivation de la vérification SSL en environnement local
- Pour production, utiliser un vrai certificat

**Code ajouté dans `WhatsAppService.php`:**
```php
if (app()->environment('local')) {
    $httpClient = new \GuzzleHttp\Client(['verify' => false]);
    $this->twilio->setHttpClient($httpClient);
}
```

---

## ⚠️ Configuration WhatsApp

### Numéro FROM pour WhatsApp Sandbox

Il y a **deux types de numéros** dans Twilio :

#### **1. WhatsApp Sandbox (Gratuit - Pour Dev)**
```env
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
```
- Numéro Twilio partagé
- Gratuit
- Limite: 5 contacts max
- Les utilisateurs doivent rejoindre le sandbox ("join <code>")

#### **2. WhatsApp Business API (Payant - Pour Prod)**
```env
TWILIO_WHATSAPP_FROM=whatsapp:+243XXXXXXXXX
```
- Votre propre numéro WhatsApp
- Payant (~$0.005-0.01/msg)
- Illimité
- Nécessite vérification Facebook Business

### Comment vérifier votre numéro FROM

1. Connectez-vous à Twilio Console
2. Allez dans **Messaging** → **Try it out** → **Send a WhatsApp message**
3. Le numéro affiché est votre **FROM number**
4. Pour sandbox : `+1 415 523 8886`
5. Pour API : Votre numéro acheté

---

## 🧪 Tests

### Test 1: Générer un QR Code

```bash
# Aller sur
http://localhost/admin/qrcodes/create

# Remplir:
Source: "Test Village Gombe"
✓ QR Code actif

# Cliquer: "Générer le QR Code"
```

**Résultat attendu:**
- QR Code créé avec succès
- Image affichée
- Téléchargement possible

---

### Test 2: Tester l'envoi WhatsApp

```bash
php artisan whatsapp:test +243XXXXXXXXX
```

**Résultat attendu:**
```
✅ Service configuré
✅ Routes API OK
✅ Villages actifs
✅ Message envoyé avec succès !
```

**Si erreur:**
```
❌ Échec de l'envoi - Vérifier les logs
```

Vérifier:
```bash
tail -50 storage/logs/laravel.log
```

---

## 🔍 Problèmes Courants

### Erreur: "The number +243XXXXX is unverified"

**Cause:** En mode Sandbox, seuls les numéros vérifiés peuvent recevoir des messages

**Solution:**
1. Ouvrir WhatsApp sur le téléphone destinataire
2. Scanner le QR code sandbox Twilio
3. Envoyer le code d'activation: `join <votre-code>`
4. Attendre confirmation
5. Réessayer l'envoi

---

### Erreur: "Unable to create record: Permission to send an SMS has not been enabled"

**Cause:** Compte Twilio Trial non vérifié

**Solution:**
1. Vérifier votre numéro de téléphone dans Twilio
2. Ou passer au compte payant (ajouter $20 minimum)

---

### Erreur: "Authenticate" (Code 20003)

**Cause:** Mauvais ACCOUNT_SID ou AUTH_TOKEN

**Solution:**
```bash
# Vérifier dans .env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxx

# Nettoyer le cache
php artisan config:clear
php artisan cache:clear

# Retester
php artisan whatsapp:test
```

---

### Webhook ne reçoit pas les messages

**Cause:** URL webhook non accessible publiquement

**Solution (Dev avec ngrok):**
```bash
# Démarrer ngrok
ngrok http 8000

# Copier l'URL: https://abc123.ngrok.io

# Configurer dans Twilio:
https://abc123.ngrok.io/api/webhook/whatsapp
```

**Vérifier:**
```bash
# Tester l'URL en direct
curl -X POST https://abc123.ngrok.io/api/webhook/whatsapp
```

---

### Messages non reçus par l'utilisateur

**Checklist:**

1. ✅ Numéro a rejoint le sandbox ?
2. ✅ Numéro FROM correct dans .env ?
3. ✅ Solde Twilio > $0 ?
4. ✅ Message < 1600 caractères ?
5. ✅ Pas de spam (max 1 msg/sec) ?

---

### QR Code généré mais scan ne fonctionne pas

**Cause:** URL de scan incorrecte

**Vérifier:**
```bash
# Scanner le QR code avec un lecteur QR
# Devrait afficher: http://votre-domaine.com/qr/ABC123XYZ

# Tester manuellement
curl http://localhost/qr/ABC123XYZ
```

**Devrait rediriger vers:**
```
https://wa.me/243XXXXXXXXX?text=Je veux m'inscrire à CAN2025 avec le code: ABC123XYZ
```

---

## 📊 Logs Utiles

### Voir les logs en temps réel
```bash
tail -f storage/logs/laravel.log
```

### Rechercher erreurs WhatsApp
```bash
grep "WhatsApp" storage/logs/laravel.log
```

### Rechercher erreurs Twilio
```bash
grep "Twilio" storage/logs/laravel.log
```

### Vider les logs
```bash
echo "" > storage/logs/laravel.log
```

---

## 🔐 Vérifier la Configuration

```bash
# Test complet
php artisan whatsapp:test

# Vérifier .env
cat .env | grep TWILIO

# Vérifier routes
php artisan route:list --path=api

# Vérifier migrations
php artisan migrate:status
```

---

## 🆘 Encore des Problèmes ?

1. **Vider le cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

2. **Réinstaller Twilio:**
   ```bash
   composer remove twilio/sdk
   composer require twilio/sdk
   ```

3. **Vérifier les permissions:**
   ```bash
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

4. **Consulter les logs Twilio:**
   - https://console.twilio.com/
   - Monitor → Logs → WhatsApp

---

## 📞 Support Twilio

- Documentation: https://www.twilio.com/docs/whatsapp
- Console: https://console.twilio.com/
- Support: https://support.twilio.com/

---

## ✅ Checklist Avant Production

- [ ] Compte Twilio vérifié et payant
- [ ] WhatsApp Business API activée
- [ ] Numéro WhatsApp dédié acheté
- [ ] Webhook en HTTPS avec certificat SSL valide
- [ ] Validation des signatures Twilio activée
- [ ] Rate limiting configuré
- [ ] Queue system (Redis) configuré
- [ ] Monitoring et alertes en place
- [ ] Backup automatique de la DB
- [ ] Tests de charge effectués
