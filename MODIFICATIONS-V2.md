# 🚀 Modifications V2 - Flow Interactif CAN 2025

## 📋 Résumé des Modifications

Ce document décrit toutes les modifications apportées pour transformer le flow d'inscription simple en un **système interactif complet** avec menu, gestion des villages, matchs et pronostics.

---

## ✨ Nouvelles Fonctionnalités

### 1. **Vérification Automatique de l'Utilisateur**
- À l'arrivée dans le bot, vérification si l'utilisateur est déjà inscrit
- **Utilisateur existant** → Menu principal
- **Nouvel utilisateur** → Processus d'inscription

### 2. **Menu Principal Interactif** (Utilisateurs Inscrits)
Lorsqu'un utilisateur déjà inscrit revient, il voit :
```
👋 Salut {Nom} !

Que veux-tu faire aujourd'hui ?

1️⃣ Voir les Villages CAN
2️⃣ Voir les matchs & placer un pronostic
3️⃣ Me désinscrire

Tape 1, 2 ou 3
```

### 3. **Affichage des Villages CAN**
- Liste dynamique des villages actifs depuis la base de données
- Affiche : Nom, Adresse, Nombre de membres
- Gestion du cas "Aucun village disponible"

### 4. **Système de Pronostics Interactif**
**Flux complet :**
1. Affichage des matchs du jour
2. Sélection du match (par numéro)
3. Saisie du pronostic (format: `2-1`)
4. Enregistrement dans la base de données
5. Confirmation à l'utilisateur

**Validations :**
- Format de score valide (regex: `^[0-9]{1,2}-[0-9]{1,2}$`)
- Match pas encore commencé
- Pronostics activés pour le match

### 5. **Calcul Automatique des Gagnants**
Lorsqu'un admin met à jour le score final d'un match :
- ✅ Calcul automatique des gagnants
- ✅ Mise à jour du champ `is_winner` dans les pronostics
- ✅ Logging détaillé
- ✅ Message de confirmation à l'admin

### 6. **Désinscription avec Confirmation**
- Message de confirmation avant désinscription
- Mise à jour du statut utilisateur
- Message d'adieu personnalisé

---

## 🛠️ Modifications Techniques

### A. Nouvelles Routes API

Fichier modifié : `routes/api.php`

```php
// Nouvelles routes ajoutées
Route::post('/check-user', [TwilioStudioController::class, 'checkUser']);
Route::get('/villages', [TwilioStudioController::class, 'getVillages']);
Route::get('/matches/today', [TwilioStudioController::class, 'getMatchesToday']);
Route::post('/pronostic', [TwilioStudioController::class, 'savePronostic']);
Route::post('/unsubscribe', [TwilioStudioController::class, 'unsubscribe']);
Route::get('/partners', [TwilioStudioController::class, 'getPartners']);
Route::get('/prizes', [TwilioStudioController::class, 'getPrizes']);
```

### B. Nouvelles Méthodes API

Fichier modifié : `app/Http/Controllers/Api/TwilioStudioController.php`

#### **1. checkUser()** - POST `/api/can/check-user`
Vérifie si un numéro de téléphone existe dans la base.

**Requête :**
```json
{
  "phone": "+243XXXXXXXXX"
}
```

**Réponse (utilisateur existant) :**
```json
{
  "success": true,
  "user_exists": true,
  "user": {
    "id": 123,
    "name": "John Doe",
    "phone": "+243XXXXXXXXX",
    "village_id": 5,
    "village_name": "Gombe"
  }
}
```

**Réponse (utilisateur inexistant) :**
```json
{
  "success": true,
  "user_exists": false
}
```

#### **2. getVillages()** - GET `/api/can/villages`
Récupère la liste des villages actifs.

**Réponse :**
```json
{
  "success": true,
  "has_villages": true,
  "count": 3,
  "villages": [
    {
      "id": 1,
      "number": 1,
      "name": "Gombe",
      "address": "Avenue du Port",
      "capacity": 500,
      "members_count": 234
    }
  ]
}
```

#### **3. getMatchesToday()** - GET `/api/can/matches/today`
Récupère les matchs du jour disponibles pour pronostic.

**Réponse :**
```json
{
  "success": true,
  "has_matches": true,
  "count": 2,
  "matches": [
    {
      "id": 12,
      "number": 1,
      "team_a": "RDC",
      "team_b": "Cameroun",
      "match_time": "15:00",
      "status": "scheduled"
    }
  ]
}
```

#### **4. savePronostic()** - POST `/api/can/pronostic`
Enregistre un pronostic utilisateur.

**Requête :**
```json
{
  "phone": "+243XXXXXXXXX",
  "match_id": 12,
  "score_a": 2,
  "score_b": 1
}
```

**Réponse (succès) :**
```json
{
  "success": true,
  "message": "Pronostic enregistré avec succès !",
  "pronostic": {
    "id": 456,
    "match": "RDC vs Cameroun",
    "prediction": "2 - 1"
  }
}
```

**Réponse (erreur - match commencé) :**
```json
{
  "success": false,
  "message": "Ce match n'accepte plus de pronostics."
}
```

#### **5. unsubscribe()** - POST `/api/can/unsubscribe`
Désinscrit un utilisateur.

**Requête :**
```json
{
  "phone": "+243XXXXXXXXX"
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Désinscription effectuée avec succès."
}
```

#### **6. getPartners()** - GET `/api/can/partners`
Liste des partenaires actifs.

#### **7. getPrizes()** - GET `/api/can/prizes`
Liste des lots disponibles.

---

### C. Calcul Automatique des Gagnants

Fichier modifié : `app/Http/Controllers/Admin/MatchController.php`

**Méthode ajoutée : `calculateWinners()`**

Lorsqu'un match passe au statut `finished` avec des scores définis :
1. Récupération de tous les pronostics pour ce match
2. Comparaison du score exact
3. Mise à jour `is_winner = true` pour les bons pronostics
4. Marquage `winners_calculated = true` sur le match
5. Logging détaillé

**Log généré :**
```
Match 12 - Gagnants calculés automatiquement
- Match: RDC vs Cameroun
- Score final: 2 - 1
- Total pronostics: 150
- Gagnants: 12
```

---

## 📱 Nouveau Flow Twilio

Fichier créé : `twilio-flow-v2-interactive.json`

### Architecture du Flow

```
┌─────────────────────────────────────────────────────────────┐
│ TRIGGER (Incoming Message)                                  │
└─────────────┬───────────────────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────────────────────┐
│ CHECK SOURCE (QR Code ou Direct)                            │
│ - AFFICHE / PDV / DIGITAL / FLYER / DIRECT                  │
└─────────────┬───────────────────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────────────────────┐
│ HTTP LOG SCAN → API /can/scan                               │
└─────────────┬───────────────────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────────────────────┐
│ HTTP CHECK USER → API /can/check-user                       │
└─────────────┬───────────────────────────────────────────────┘
              │
              ├─── USER EXISTS ────────────────────────┐
              │                                        │
              ▼                                        ▼
┌──────────────────────────┐            ┌──────────────────────────┐
│ MENU PRINCIPAL           │            │ FLOW INSCRIPTION         │
│ 1. Villages              │            │ - Opt-in (OUI/NON)       │
│ 2. Matchs & Pronostics   │            │ - Demande nom            │
│ 3. Désinscription        │            │ - Enregistrement         │
└──────────────────────────┘            └──────────────────────────┘
       │
       ├─── Option 1 ───► GET /villages ───► Affichage Villages
       │
       ├─── Option 2 ───► GET /matches/today ───┐
       │                                         │
       │                  ┌──────────────────────┘
       │                  ▼
       │         Selection Match (1, 2, 3...)
       │                  │
       │                  ▼
       │         Demande Pronostic (2-1)
       │                  │
       │                  ▼
       │         POST /pronostic ───► Confirmation
       │
       └─── Option 3 ───► Confirmation ───► POST /unsubscribe
```

### États du Flow

**Total : 66 états** (vs 35 dans V1)

**Nouveaux états clés :**
- `http_check_user` - Vérification utilisateur
- `msg_menu_principal` - Menu interactif
- `http_get_villages` - Récupération villages
- `http_get_matches` - Récupération matchs
- `msg_show_matches` - Affichage matchs
- `check_match_selection` - Validation sélection
- `msg_ask_pronostic` - Demande pronostic
- `validate_pronostic` - Validation format (regex)
- `parse_pronostic` - Parsing score (split par `-`)
- `http_save_pronostic` - Enregistrement
- `msg_confirm_unsubscribe` - Confirmation désinscription

---

## 🔄 Différences avec V1

| Aspect | V1 (Ancien) | V2 (Nouveau) |
|--------|-------------|--------------|
| **Vérification utilisateur** | ❌ Non | ✅ Oui (API check-user) |
| **Menu pour inscrits** | ❌ Non | ✅ Oui (3 options) |
| **Affichage villages** | ❌ Statique | ✅ Dynamique (API) |
| **Affichage matchs** | ❌ Non | ✅ Oui (matchs du jour) |
| **Pronostics** | ❌ Non | ✅ Oui (flow complet) |
| **Calcul gagnants** | ⚠️ Manuel | ✅ Automatique |
| **Désinscription** | ⚠️ STOP simple | ✅ Avec confirmation |
| **États du flow** | 35 | 66 |
| **Endpoints API** | 8 | 15 |

---

## 🧪 Comment Tester

### 1. **Préparer la Base de Données**

```bash
# Créer des villages
php artisan tinker
```

```php
App\Models\Village::create([
    'name' => 'Gombe',
    'address' => 'Avenue du Port',
    'capacity' => 500,
    'is_active' => true
]);

App\Models\Village::create([
    'name' => 'Masina',
    'address' => 'Boulevard Lumumba',
    'capacity' => 600,
    'is_active' => true
]);
```

### 2. **Créer des Matchs du Jour**

```php
App\Models\FootballMatch::create([
    'team_a' => 'RDC',
    'team_b' => 'Cameroun',
    'match_date' => now()->addHours(2),
    'status' => 'scheduled',
    'pronostic_enabled' => true
]);

App\Models\FootballMatch::create([
    'team_a' => 'Sénégal',
    'team_b' => 'Nigeria',
    'match_date' => now()->addHours(5),
    'status' => 'scheduled',
    'pronostic_enabled' => true
]);
```

### 3. **Tester les API avec cURL**

```bash
# 1. Check user (inexistant)
curl -X POST https://wabracongo.ywcdigital.com/api/can/check-user \
  -H "Content-Type: application/json" \
  -d '{"phone": "+243999999999"}'

# 2. Villages
curl https://wabracongo.ywcdigital.com/api/can/villages

# 3. Matchs du jour
curl https://wabracongo.ywcdigital.com/api/can/matches/today

# 4. Enregistrer un pronostic
curl -X POST https://wabracongo.ywcdigital.com/api/can/pronostic \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+243999999999",
    "match_id": 1,
    "score_a": 2,
    "score_b": 1
  }'
```

### 4. **Importer le Nouveau Flow dans Twilio**

1. Aller dans **Twilio Console > Studio > Flows**
2. Créer un nouveau flow ou modifier l'existant
3. Cliquer sur **"..."** (menu) > **Import from JSON**
4. Copier le contenu de `twilio-flow-v2-interactive.json`
5. Cliquer sur **Import**
6. **Publier** le flow

### 5. **Scénarios de Test WhatsApp**

#### **Scénario 1 : Nouvel Utilisateur**
```
User: START_AFF_GOMBE
Bot: 🦁 BIENVENUE ! [...] Tape OUI pour t'inscrire
User: OUI
Bot: Super ! 🙌 C'est quoi ton nom ?
User: Jean
Bot: ✅ C'est fait Jean ! [...] Tu peux maintenant voir villages, matchs...
```

#### **Scénario 2 : Utilisateur Existant - Villages**
```
User: Bonjour
Bot: 👋 Salut Jean ! Que veux-tu faire ? 1=Villages, 2=Matchs, 3=Désinscription
User: 1
Bot: 🏘️ VILLAGES CAN disponibles :
     1. Gombe
        📍 Avenue du Port
        👥 5 membres
     [...]
```

#### **Scénario 3 : Utilisateur Existant - Pronostics**
```
User: Bonjour
Bot: 👋 Salut Jean ! [Menu]
User: 2
Bot: ⚽ MATCHS D'AUJOURD'HUI :
     1. RDC vs Cameroun 🕐 15:00
     2. Sénégal vs Nigeria 🕐 18:00
     Tape le numéro du match
User: 1
Bot: 🎯 Parfait ! Quel est ton pronostic ? Format: 2-1
User: 2-1
Bot: ✅ Pronostic enregistré ! Ton pronostic : 2-1 [...] Bonne chance ! 🦁
```

#### **Scénario 4 : Désinscription**
```
User: Bonjour
Bot: 👋 Salut Jean ! [Menu]
User: 3
Bot: ⚠️ Es-tu sûr(e) ? Tape OUI ou NON
User: OUI
Bot: ✅ Tu es désinscrit(e). Merci d'avoir participé ! 🦁
```

### 6. **Tester le Calcul Automatique des Gagnants**

1. Créer des pronostics via le bot WhatsApp
2. Dans l'admin, aller sur **Matchs**
3. Éditer un match et mettre :
   - `Score A` = 2
   - `Score B` = 1
   - `Status` = finished
4. **Sauvegarder**
5. Message de confirmation : "Match mis à jour et gagnants calculés automatiquement !"
6. Vérifier dans **Pronostics** → Les bons pronostics ont `is_winner = true`

---

## 📊 Base de Données

Aucune migration nécessaire ! Toutes les colonnes existent déjà :
- ✅ `users.is_active`
- ✅ `matches.winners_calculated`
- ✅ `pronostics.is_winner`
- ✅ `villages.is_active`
- ✅ `prizes.is_active`

---

## 🚨 Points d'Attention

### 1. **Format de Pronostic**
Le flow utilise une regex stricte : `^[0-9]{1,2}-[0-9]{1,2}$`
- ✅ Valide : `2-1`, `0-0`, `10-5`
- ❌ Invalide : `2 - 1`, `2:1`, `a-b`

### 2. **Matchs du Jour**
L'API ne retourne que :
- Matchs entre `00:00` et `23:59` aujourd'hui
- Statut = `scheduled` ou `live`
- `pronostic_enabled = true`

### 3. **Calcul des Gagnants**
Le calcul se fait **automatiquement** seulement si :
- Le match **passe** à `status = finished` (n'était pas finished avant)
- Des scores sont définis (`score_a` et `score_b` non null)
- `winners_calculated = false`

### 4. **Variables Twilio**
Le flow utilise des variables Liquid :
- `{{flow.variables.phone_number}}`
- `{{flow.variables.user_name}}`
- `{{flow.variables.selected_match_number}}`
- `{{widgets.http_get_matches.parsed.matches}}`

---

## 🔐 Sécurité

### API Validations
Toutes les API valident :
- ✅ Format téléphone
- ✅ Existence match (`exists:matches,id`)
- ✅ Scores entre 0 et 20
- ✅ Utilisateur actif
- ✅ Match accepte encore les pronostics

### Logging
Tous les événements sont loggés :
```php
Log::info('Twilio Studio - Pronostic saved', [
    'user_id' => $user->id,
    'match_id' => $match->id,
    'prediction' => "2 - 1"
]);
```

---

## 📈 Métriques & Analytics

Le dashboard admin affiche déjà :
- ✅ Total pronostics
- ✅ Taux de participation
- ✅ Statistiques par village
- ✅ Sources d'acquisition

Avec V2, on peut ajouter :
- Nombre de retours au menu (utilisateurs actifs)
- Matchs les plus pronostiqués
- Taux de conversion : Villages vus → Pronostics placés

---

## 🎯 Prochaines Améliorations Possibles

1. **Choix du Village** lors de l'inscription (au lieu d'attribution automatique)
2. **Notification des gagnants** via campagne WhatsApp automatique
3. **Historique des pronostics** d'un utilisateur
4. **Leaderboard** dans le bot WhatsApp
5. **Rappels** avant les matchs ("N'oublie pas de parier !")
6. **Multi-langues** (Français / Lingala)

---

## 📞 Support

Pour toute question :
- Vérifier les logs : `storage/logs/laravel.log`
- Tester les API avec Postman
- Vérifier le flow Twilio en mode "Debug"

---

**Version :** 2.0
**Date :** 2025-12-06
**Statut :** ✅ Production Ready
