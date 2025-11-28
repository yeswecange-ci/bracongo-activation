# 📱 Configuration WhatsApp - CAN Activation Kinshasa

## ✅ Système d'Inscription Implémenté

Le système d'enregistrement utilisateurs via WhatsApp est maintenant **100% opérationnel** !

---

## 🚀 Fonctionnalités Disponibles

### ✅ **Inscription Conversationnelle**
- Scan du QR Code → Message WhatsApp automatique
- Demande du nom
- Choix du village
- Création automatique du compte
- Message de bienvenue

### ✅ **Menu Interactif**
- **MENU** - Affiche le menu principal
- **MATCHS** (ou 1) - Voir les prochains matchs
- **PRONOSTIC** (ou 2) - Faire un pronostic (à venir)
- **MES PRONOS** (ou 3) - Voir mes pronostics
- **CLASSEMENT** (ou 4) - Voir le classement (à venir)

### ✅ **Gestion des Sessions**
- Sessions de conversation avec état
- Timeout automatique après 30 minutes
- Nettoyage des sessions expirées

---

## 📋 Configuration Twilio

### **Étape 1: Créer un compte Twilio**

1. Aller sur https://www.twilio.com/try-twilio
2. S'inscrire gratuitement
3. Vérifier votre email et numéro

### **Étape 2: Configurer WhatsApp Sandbox**

Pour le développement, Twilio fournit un **WhatsApp Sandbox** gratuit :

1. Dans le dashboard Twilio: **Messaging** → **Try it out** → **Send a WhatsApp message**
2. Scanner le QR code avec WhatsApp
3. Envoyer le code d'activation (ex: `join <votre-code>`)
4. Vous recevrez une confirmation

### **Étape 3: Récupérer les credentials**

Dans le dashboard Twilio:
- **Account SID** : Visible sur la page principale
- **Auth Token** : Cliquer sur "Show" à côté de Account SID
- **WhatsApp Number** : Dans Messaging → Try WhatsApp → From (ex: `whatsapp:+14155238886`)

### **Étape 4: Configurer le fichier .env**

```env
# Twilio Configuration
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886

# WhatsApp Number (pour QR codes)
WHATSAPP_NUMBER=243812345678

# URL publique de l'application (pour webhooks)
APP_PUBLIC_URL=https://votre-domaine.com
```

### **Étape 5: Exposer votre application (Développement)**

Pour que Twilio puisse envoyer les webhooks, votre application doit être accessible publiquement.

**Option 1: ngrok (Recommandé pour dev)**
```bash
# Installer ngrok: https://ngrok.com/download
ngrok http 8000
```

Vous obtiendrez une URL comme: `https://abc123.ngrok.io`

**Option 2: Déploiement en production**
Déployez sur un serveur avec domaine public (ex: DigitalOcean, AWS, Heroku)

### **Étape 6: Configurer le webhook dans Twilio**

1. Dans Twilio: **Messaging** → **Try WhatsApp** → **Sandbox settings**
2. Section **WHEN A MESSAGE COMES IN**:
   - URL: `https://votre-domaine.com/api/webhook/whatsapp`
   - Method: `POST`
3. Section **STATUS CALLBACK URL** (optionnel):
   - URL: `https://votre-domaine.com/api/webhook/whatsapp/status`
   - Method: `POST`
4. Sauvegarder

---

## 🧪 Tester le Système

### **Test 1: Vérifier les routes**
```bash
php artisan route:list --path=api
```

Vous devriez voir:
```
POST api/webhook/whatsapp ................ Api\WhatsAppWebhookController@receiveMessage
POST api/webhook/whatsapp/status ......... Api\WhatsAppWebhookController@statusCallback
```

### **Test 2: Scanner un QR Code**

1. Générer un QR code depuis `/admin/qrcodes/create`
2. Scanner avec votre téléphone
3. Vous devriez recevoir un message WhatsApp: "Comment t'appelles-tu ?"

### **Test 3: Flow complet d'inscription**

1. Scanner le QR code
2. **Bot**: "Comment t'appelles-tu ?"
   - **Vous**: "Jean Kabongo"
3. **Bot**: "Choisis ton village CAN"
   - **Vous**: "1" (ou le nom du village)
4. **Bot**: "Bienvenue à CAN 2025, Jean Kabongo !"
5. **Bot**: Envoie le menu automatiquement

### **Test 4: Commandes utilisateur**

Une fois inscrit, testez:
- Envoyer **MENU** → Affiche le menu
- Envoyer **1** ou **MATCHS** → Liste des matchs
- Envoyer **AIDE** → Affiche le menu

---

## 🛠️ Commandes Artisan

### **Nettoyer les sessions expirées**
```bash
php artisan sessions:clean
```

Supprime les sessions de plus de 24h sans utilisateur enregistré.

**Automatiser avec cron** (production):
```bash
# Ajouter dans app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('sessions:clean')->daily();
}
```

---

## 📊 Base de Données

### **Nouvelle table: conversation_sessions**

Stocke les sessions de conversation WhatsApp:

| Colonne | Type | Description |
|---------|------|-------------|
| phone | string | Numéro WhatsApp (unique) |
| state | string | État de la conversation (idle, awaiting_name, etc.) |
| data | json | Données temporaires (nom, choix, etc.) |
| user_id | foreignId | Lié à l'utilisateur une fois inscrit |
| last_activity | timestamp | Dernière activité (timeout 30min) |

---

## 🔍 Logs et Debugging

### **Logs WhatsApp**

Tous les messages sont loggés dans `storage/logs/laravel.log`:

```bash
# Surveiller les logs en temps réel
tail -f storage/logs/laravel.log
```

Rechercher:
- `WhatsApp message received` - Message entrant
- `WhatsApp message sent` - Message envoyé
- `User registered via WhatsApp` - Inscription réussie

### **Logs Twilio**

Dans le dashboard Twilio → **Monitor** → **Logs** → **WhatsApp**

Vous pouvez voir tous les messages envoyés/reçus avec leur statut.

---

## 🐛 Troubleshooting

### **Problème: Pas de messages reçus**

1. Vérifier que ngrok est actif:
   ```bash
   curl https://votre-ngrok.ngrok.io/api/webhook/whatsapp
   ```

2. Vérifier le webhook dans Twilio:
   - La URL doit être correcte
   - La méthode doit être POST

3. Vérifier les logs Laravel:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### **Problème: Erreur 500 sur webhook**

1. Vérifier les credentials dans `.env`:
   ```bash
   php artisan config:clear
   ```

2. Vérifier que Twilio SDK est installé:
   ```bash
   composer show twilio/sdk
   ```

3. Vérifier les logs d'erreur

### **Problème: Messages non envoyés**

1. Vérifier les credentials Twilio
2. Vérifier le solde du compte (mode sandbox = gratuit)
3. Vérifier que le numéro destinataire a rejoint le sandbox

---

## 📱 Passer en Production

### **WhatsApp Business API**

Pour la production, vous devez:

1. **Créer un compte Meta Business** (Facebook)
2. **Demander WhatsApp Business API** via Twilio
3. **Vérifier votre entreprise**
4. **Obtenir un numéro WhatsApp dédié**

Documentation: https://www.twilio.com/docs/whatsapp/getting-started

### **Coûts**

- **Sandbox (dev)**: Gratuit
- **Production**:
  - Conversations initiées par l'entreprise: ~$0.005-0.01 par message
  - Conversations initiées par l'utilisateur: Gratuit (24h)

### **Limites**

- Sandbox: 5 numéros maximum
- Production: Illimité

---

## 🔐 Sécurité

### **Valider les webhooks Twilio**

Pour empêcher les requêtes frauduleuses, vous pouvez valider la signature Twilio:

```php
// Dans le contrôleur webhook (optionnel)
use Twilio\Security\RequestValidator;

public function receiveMessage(Request $request)
{
    $validator = new RequestValidator(config('services.twilio.auth_token'));
    $signature = $request->header('X-Twilio-Signature');
    $url = $request->fullUrl();
    $params = $request->all();

    if (!$validator->validate($signature, $url, $params)) {
        abort(403, 'Invalid Twilio signature');
    }

    // ... reste du code
}
```

---

## 📈 Prochaines Étapes

Le système d'inscription est **complet** ! Voici ce qui reste à implémenter:

1. ✅ ~~Inscription utilisateurs~~ (FAIT !)
2. ⚠️ **Système de pronostics** (en cours)
3. ⚠️ **Campagnes WhatsApp** (notification matchs, résultats)
4. ⚠️ **Calcul automatique des gagnants**
5. ⚠️ **Dashboard avec stats réelles**

---

## 📞 Support

Pour toute question ou problème:

1. Consulter la documentation Twilio: https://www.twilio.com/docs/whatsapp
2. Vérifier les logs Laravel: `storage/logs/laravel.log`
3. Vérifier les logs Twilio dans le dashboard

---

## 🎉 Résumé des Fichiers Créés

```
app/
├── Services/
│   └── WhatsAppService.php              ✅ Service Twilio
├── Http/Controllers/Api/
│   └── WhatsAppWebhookController.php    ✅ Webhook handler
├── Models/
│   └── ConversationSession.php          ✅ Gestion sessions
└── Console/Commands/
    └── CleanExpiredSessions.php         ✅ Nettoyage sessions

database/migrations/
└── 2025_11_27_225535_create_conversation_sessions_table.php  ✅ Migration

routes/
└── api.php                               ✅ Routes webhook

config/
└── services.php                          ✅ Config Twilio

.env.example                              ✅ Variables d'environnement
```

Le système est **prêt à être utilisé** ! 🚀
