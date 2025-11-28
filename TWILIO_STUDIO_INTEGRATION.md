# 🎯 Intégration Twilio Studio - CAN 2025 Kinshasa

## ✅ Endpoints API Créés

Tous les endpoints requis par votre flow Twilio Studio ont été implémentés avec succès !

**URL de base:** `https://wabracongo.ywcdigital.com`

### 📋 Liste des Endpoints

| Endpoint | Méthode | Description | Statut |
|----------|---------|-------------|--------|
| `/api/can/scan` | POST | Log du scan QR code initial | ✅ Implémenté |
| `/api/can/optin` | POST | Log de l'opt-in (réponse OUI) | ✅ Implémenté |
| `/api/can/inscription` | POST | Inscription finale avec nom | ✅ Implémenté |
| `/api/can/refus` | POST | Refus de l'opt-in (NON) | ✅ Implémenté |
| `/api/can/stop` | POST | Désinscription (STOP) | ✅ Implémenté |
| `/api/can/abandon` | POST | Abandon du processus | ✅ Implémenté |
| `/api/can/timeout` | POST | Timeout dans le flow | ✅ Implémenté |
| `/api/can/error` | POST | Erreur de livraison | ✅ Implémenté |

---

## 🔧 Configuration Twilio Studio

### **Étape 1: Remplacer les URLs dans votre Flow**

Dans votre fichier JSON Twilio Studio, recherchez et remplacez :

**Avant:**
```
https://VOTRE-SERVEUR.com/api/can/...
```

**Après:**
```
https://wabracongo.ywcdigital.com/api/can/...
```

### **Étape 2: URLs à configurer dans chaque widget**

#### Widget `http_log_scan`
```json
{
  "url": "https://wabracongo.ywcdigital.com/api/can/scan"
}
```

#### Widget `http_log_scan_direct`
```json
{
  "url": "https://wabracongo.ywcdigital.com/api/can/scan"
}
```

#### Widget `http_log_optin`
```json
{
  "url": "https://wabracongo.ywcdigital.com/api/can/optin"
}
```

#### Widget `http_log_inscription`
```json
{
  "url": "https://wabracongo.ywcdigital.com/api/can/inscription"
}
```

#### Widget `http_log_refus`
```json
{
  "url": "https://wabracongo.ywcdigital.com/api/can/refus"
}
```

#### Widget `http_log_stop`
```json
{
  "url": "https://wabracongo.ywcdigital.com/api/can/stop"
}
```

#### Widget `http_log_abandon`
```json
{
  "url": "https://wabracongo.ywcdigital.com/api/can/abandon"
}
```

#### Widgets `timeout_*`
```json
{
  "url": "https://wabracongo.ywcdigital.com/api/can/timeout"
}
```

#### Widget `delivery_failed`
```json
{
  "url": "https://wabracongo.ywcdigital.com/api/can/error"
}
```

---

## 📊 Données envoyées par Twilio Studio

### **1. POST /api/can/scan**

```json
{
  "phone": "whatsapp:+243XXXXXXXXX",
  "source_type": "AFFICHE",
  "source_detail": "GOMBE",
  "timestamp": "2025-11-28 12:00:00",
  "status": "SCAN"
}
```

**Réponse:**
```json
{
  "success": true,
  "message": "Scan logged successfully",
  "session_id": 123
}
```

---

### **2. POST /api/can/optin**

```json
{
  "phone": "whatsapp:+243XXXXXXXXX",
  "status": "OPT_IN",
  "timestamp": "2025-11-28 12:01:00"
}
```

**Réponse:**
```json
{
  "success": true,
  "message": "Opt-in logged successfully"
}
```

---

### **3. POST /api/can/inscription**

```json
{
  "phone": "whatsapp:+243XXXXXXXXX",
  "name": "Jean Kabongo",
  "source_type": "AFFICHE",
  "source_detail": "GOMBE",
  "status": "INSCRIT",
  "timestamp": "2025-11-28 12:02:00"
}
```

**Réponse:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "user_id": 456,
  "name": "Jean Kabongo"
}
```

---

### **4. POST /api/can/refus**

```json
{
  "phone": "whatsapp:+243XXXXXXXXX",
  "status": "REFUS",
  "timestamp": "2025-11-28 12:01:30"
}
```

---

### **5. POST /api/can/stop**

```json
{
  "phone": "whatsapp:+243XXXXXXXXX",
  "status": "STOP",
  "timestamp": "2025-11-28 12:03:00"
}
```

---

### **6. POST /api/can/abandon**

```json
{
  "phone": "whatsapp:+243XXXXXXXXX",
  "status": "ABANDON_OPTIN",
  "timestamp": "2025-11-28 12:01:45"
}
```

---

### **7. POST /api/can/timeout**

```json
{
  "phone": "whatsapp:+243XXXXXXXXX",
  "status": "TIMEOUT_ACCUEIL",
  "timestamp": "2025-11-28 13:00:00"
}
```

---

### **8. POST /api/can/error**

```json
{
  "phone": "whatsapp:+243XXXXXXXXX",
  "status": "DELIVERY_FAILED",
  "timestamp": "2025-11-28 12:00:30"
}
```

---

## 🗄️ Base de Données - Nouveaux Champs

La table `users` a été mise à jour avec les champs de tracking :

| Champ | Type | Description |
|-------|------|-------------|
| `source_type` | string | AFFICHE, PDV_PARTENAIRE, DIGITAL, FLYER, DIRECT |
| `source_detail` | string | GOMBE, BRACONGO, FB, UNI, SANS_QR, etc. |
| `scan_timestamp` | timestamp | Date/heure du premier scan |
| `registration_status` | string | PENDING, SCAN, OPT_IN, INSCRIT, REFUS, STOP |

---

## 🧪 Tester les Endpoints

### **Test 1: Scan QR Code**

```bash
curl -X POST https://wabracongo.ywcdigital.com/api/can/scan \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "whatsapp:+243812345678",
    "source_type": "AFFICHE",
    "source_detail": "GOMBE",
    "timestamp": "2025-11-28 12:00:00",
    "status": "SCAN"
  }'
```

**Résultat attendu:**
```json
{
  "success": true,
  "message": "Scan logged successfully",
  "session_id": 1
}
```

---

### **Test 2: Inscription Complète**

```bash
curl -X POST https://wabracongo.ywcdigital.com/api/can/inscription \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "whatsapp:+243812345678",
    "name": "Test User",
    "source_type": "AFFICHE",
    "source_detail": "GOMBE",
    "status": "INSCRIT",
    "timestamp": "2025-11-28 12:02:00"
  }'
```

**Résultat attendu:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "user_id": 1,
  "name": "Test User"
}
```

---

## 📊 Monitoring & Logs

### **Vérifier les logs Laravel**

```bash
# Voir les logs en temps réel
tail -f storage/logs/laravel.log

# Rechercher les logs Twilio Studio
grep "Twilio Studio" storage/logs/laravel.log

# Compter les scans par source
grep "Scan logged" storage/logs/laravel.log | wc -l
```

### **Logs attendus dans Laravel**

```
[2025-11-28 12:00:00] local.INFO: Twilio Studio - Scan logged {"phone":"+243812345678","source":"AFFICHE / GOMBE"}
[2025-11-28 12:01:00] local.INFO: Twilio Studio - Opt-in confirmed {"phone":"+243812345678"}
[2025-11-28 12:02:00] local.INFO: Twilio Studio - New user registered {"user_id":1,"phone":"+243812345678","source":"AFFICHE / GOMBE"}
```

---

## 🎯 Sources de Tracking Supportées

### **1. AFFICHE (Affiches dans les villages)**
- `START_AFF_GOMBE` → source_detail: `GOMBE`
- `START_AFF_MASINA` → source_detail: `MASINA`
- `START_AFF_LEMBA` → source_detail: `LEMBA`
- `START_AFF_BANDA` → source_detail: `BANDA`
- `START_AFF_NGALI` → source_detail: `NGALI`

### **2. PDV_PARTENAIRE (Points de vente)**
- `START_PDV_BRACONGO` → source_detail: `BRACONGO`
- `START_PDV_VODACOM` → source_detail: `VODACOM`
- `START_PDV_ORANGE` → source_detail: `ORANGE`
- `START_PDV_AIRTEL` → source_detail: `AIRTEL`

### **3. DIGITAL (Réseaux sociaux)**
- `START_FB` → source_detail: `FB`
- `START_IG` → source_detail: `IG`
- `START_TIKTOK` → source_detail: `TIKTOK`
- `START_WA_STATUS` → source_detail: `WA_STATUS`

### **4. FLYER (Flyers distribués)**
- `START_FLYER_UNI` → source_detail: `UNI`
- `START_FLYER_RUE` → source_detail: `RUE`
- `START_FLYER_EVENT` → source_detail: `EVENT`

### **5. DIRECT (Contact direct sans QR)**
- Pas de code START → source_detail: `SANS_QR`

---

## 📈 Statistiques disponibles

Depuis le backoffice admin, vous pourrez voir :

1. **Total inscriptions par source**
   - Combien via Affiches GOMBE ?
   - Combien via PDV Bracongo ?
   - Combien via Facebook ?

2. **Taux de conversion**
   - Scans → Opt-in (%)
   - Opt-in → Inscriptions (%)

3. **Abandons**
   - Timeout accueil
   - Timeout nom
   - Refus opt-in

4. **Village par défaut**
   - Les utilisateurs sont assignés au premier village actif
   - Ils pourront choisir leur village dans la Phase 2

---

## ⚠️ Points Importants

1. **Village par défaut**: Les utilisateurs inscrits via Twilio Studio reçoivent automatiquement le **premier village actif** en base. Assurez-vous qu'au moins 1 village est actif.

2. **Format téléphone**: Le système accepte `whatsapp:+243...` et le convertit automatiquement en `+243...`

3. **Gestion des doublons**: Si un utilisateur scanne plusieurs QR codes, seul le premier scan est enregistré (phone unique).

4. **Logs complets**: Tous les événements (scan, optin, abandon, timeout) sont loggés pour analytics.

---

## ✅ Checklist de Déploiement

- [ ] Déployer l'application sur `https://wabracongo.ywcdigital.com`
- [ ] Exécuter `php artisan migrate` en production
- [ ] Créer au moins 1 village actif dans `/admin/villages`
- [ ] Mettre à jour les URLs dans le flow Twilio Studio
- [ ] Importer le flow mis à jour dans Twilio Studio
- [ ] Tester avec `curl` les 8 endpoints
- [ ] Publier le flow Twilio Studio
- [ ] Tester le flow complet avec un vrai numéro WhatsApp
- [ ] Vérifier les logs Laravel (`storage/logs/laravel.log`)
- [ ] Vérifier les utilisateurs créés dans `/admin/users`

---

## 🆘 Troubleshooting

### Erreur: "No active village available"

**Cause:** Aucun village actif en base de données

**Solution:**
```bash
# Créer un village via l'admin
https://wabracongo.ywcdigital.com/admin/villages/create

# Ou via tinker
php artisan tinker
>>> \App\Models\Village::create(['name' => 'GOMBE', 'is_active' => true]);
```

---

### Les requêtes n'arrivent pas

**Checklist:**
1. URL correcte dans Twilio Studio ?
2. HTTPS actif sur le serveur ?
3. Firewall autorise Twilio ?
4. Logs Laravel affichent quelque chose ?

---

## 🎉 Prochaines Étapes

Après l'intégration Twilio Studio :

1. **Phase 2**: Choix du village par l'utilisateur
2. **Pronostics**: Intégration des pronostics matchs
3. **Campagnes**: Envoi automatique de messages
4. **Prix**: Distribution des gains

---

**Tout est prêt pour l'intégration ! 🚀**
