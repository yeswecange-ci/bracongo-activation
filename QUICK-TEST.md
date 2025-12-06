# 🧪 Tests Rapides - CAN 2025 V2

## ✅ Checklist de Vérification

### 1. Vérifier les Routes API

```bash
# Dans le terminal, depuis la racine du projet
php artisan route:list --path=api/can
```

**Résultat attendu :**
```
POST   api/can/check-user
GET    api/can/villages
GET    api/can/matches/today
POST   api/can/pronostic
POST   api/can/unsubscribe
GET    api/can/partners
GET    api/can/prizes
```

---

### 2. Préparer des Données de Test

```bash
php artisan tinker
```

```php
// 1. Créer 2 villages
\App\Models\Village::create(['name' => 'Gombe', 'address' => 'Avenue du Port', 'capacity' => 500, 'is_active' => true]);
\App\Models\Village::create(['name' => 'Masina', 'address' => 'Boulevard Lumumba', 'capacity' => 600, 'is_active' => true]);

// 2. Créer 2 matchs AUJOURD'HUI
\App\Models\FootballMatch::create([
    'team_a' => 'RDC',
    'team_b' => 'Cameroun',
    'match_date' => now()->addHours(2),
    'status' => 'scheduled',
    'pronostic_enabled' => true
]);

\App\Models\FootballMatch::create([
    'team_a' => 'Sénégal',
    'team_b' => 'Nigeria',
    'match_date' => now()->addHours(5),
    'status' => 'scheduled',
    'pronostic_enabled' => true
]);

// 3. Vérifier
\App\Models\Village::where('is_active', true)->count(); // Doit retourner 2
\App\Models\FootballMatch::whereDate('match_date', today())->count(); // Doit retourner 2
```

---

### 3. Tester les API Localement

**a) API Check User (utilisateur inexistant)**
```bash
curl -X POST http://localhost/api/can/check-user \
  -H "Content-Type: application/json" \
  -d '{"phone": "+243999999999"}'
```

**Résultat attendu :**
```json
{
  "success": true,
  "user_exists": false
}
```

---

**b) API Villages**
```bash
curl http://localhost/api/can/villages
```

**Résultat attendu :**
```json
{
  "success": true,
  "has_villages": true,
  "count": 2,
  "villages": [
    {
      "id": 1,
      "number": 1,
      "name": "Gombe",
      "address": "Avenue du Port",
      "capacity": 500,
      "members_count": 0
    },
    {
      "id": 2,
      "number": 2,
      "name": "Masina",
      "address": "Boulevard Lumumba",
      "capacity": 600,
      "members_count": 0
    }
  ]
}
```

---

**c) API Matchs du Jour**
```bash
curl http://localhost/api/can/matches/today
```

**Résultat attendu :**
```json
{
  "success": true,
  "has_matches": true,
  "count": 2,
  "matches": [
    {
      "id": 1,
      "number": 1,
      "team_a": "RDC",
      "team_b": "Cameroun",
      "match_time": "XX:XX",
      "status": "scheduled"
    },
    {
      "id": 2,
      "number": 2,
      "team_a": "Sénégal",
      "team_b": "Nigeria",
      "match_time": "XX:XX",
      "status": "scheduled"
    }
  ]
}
```

---

**d) API Inscription (pour créer un utilisateur de test)**
```bash
curl -X POST http://localhost/api/can/inscription \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+243999999999",
    "name": "Test User",
    "source_type": "DIRECT",
    "source_detail": "SANS_QR"
  }'
```

**Résultat attendu :**
```json
{
  "success": true,
  "message": "User registered successfully",
  "user_id": 1,
  "name": "Test User"
}
```

---

**e) API Check User (utilisateur existant)**
```bash
curl -X POST http://localhost/api/can/check-user \
  -H "Content-Type: application/json" \
  -d '{"phone": "+243999999999"}'
```

**Résultat attendu :**
```json
{
  "success": true,
  "user_exists": true,
  "user": {
    "id": 1,
    "name": "Test User",
    "phone": "+243999999999",
    "village_id": 1,
    "village_name": "Gombe"
  }
}
```

---

**f) API Enregistrer Pronostic**
```bash
curl -X POST http://localhost/api/can/pronostic \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+243999999999",
    "match_id": 1,
    "score_a": 2,
    "score_b": 1
  }'
```

**Résultat attendu :**
```json
{
  "success": true,
  "message": "Pronostic enregistré avec succès !",
  "pronostic": {
    "id": 1,
    "match": "RDC vs Cameroun",
    "prediction": "2 - 1"
  }
}
```

---

**g) Vérifier le Pronostic dans la Base**
```bash
php artisan tinker
```

```php
$pronostic = \App\Models\Pronostic::first();
echo "User: " . $pronostic->user->name . "\n";
echo "Match: " . $pronostic->match->team_a . " vs " . $pronostic->match->team_b . "\n";
echo "Prediction: " . $pronostic->predicted_score_a . "-" . $pronostic->predicted_score_b . "\n";
echo "Is Winner: " . ($pronostic->is_winner ? 'YES' : 'NO') . "\n";
```

---

### 4. Tester le Calcul Automatique des Gagnants

```bash
php artisan tinker
```

```php
// 1. Créer plusieurs pronostics avec des scores différents
$match = \App\Models\FootballMatch::first();
$user1 = \App\Models\User::first();

// Créer 5 pronostics différents
\App\Models\Pronostic::create(['user_id' => $user1->id, 'match_id' => $match->id, 'predicted_score_a' => 2, 'predicted_score_b' => 1]);
\App\Models\Pronostic::create(['user_id' => $user1->id, 'match_id' => $match->id, 'predicted_score_a' => 1, 'predicted_score_b' => 0]);
\App\Models\Pronostic::create(['user_id' => $user1->id, 'match_id' => $match->id, 'predicted_score_a' => 2, 'predicted_score_b' => 1]);
\App\Models\Pronostic::create(['user_id' => $user1->id, 'match_id' => $match->id, 'predicted_score_a' => 0, 'predicted_score_b' => 0]);
\App\Models\Pronostic::create(['user_id' => $user1->id, 'match_id' => $match->id, 'predicted_score_a' => 3, 'predicted_score_b' => 2]);

// 2. Passer en ligne de commande ou via l'admin web
// Mais pour tester rapidement :
$match->update(['score_a' => 2, 'score_b' => 1, 'status' => 'finished']);

// 3. Vérifier les gagnants
echo "Total pronostics: " . $match->pronostics()->count() . "\n";
echo "Gagnants: " . $match->pronostics()->where('is_winner', true)->count() . "\n";
echo "Winners calculated: " . ($match->winners_calculated ? 'YES' : 'NO') . "\n";

// 4. Afficher les détails
foreach ($match->pronostics as $p) {
    echo sprintf(
        "%s - Prediction: %d-%d | Winner: %s\n",
        $p->user->name,
        $p->predicted_score_a,
        $p->predicted_score_b,
        $p->is_winner ? 'YES' : 'NO'
    );
}
```

**Résultat attendu :**
```
Total pronostics: 5
Gagnants: 2
Winners calculated: YES
Test User - Prediction: 2-1 | Winner: YES
Test User - Prediction: 1-0 | Winner: NO
Test User - Prediction: 2-1 | Winner: YES
Test User - Prediction: 0-0 | Winner: NO
Test User - Prediction: 3-2 | Winner: NO
```

---

### 5. Vérifier le Calcul via l'Admin Web

1. Aller sur : `http://localhost/admin/matches`
2. Cliquer sur **Edit** d'un match
3. Remplir :
   - Score A : `2`
   - Score B : `1`
   - Status : `finished`
4. Cliquer **Mettre à jour**
5. **Vérifier le message :** "Match mis à jour et gagnants calculés automatiquement !"
6. Aller sur **Pronostics** pour voir les gagnants marqués

---

### 6. Importer et Tester le Flow Twilio

#### A. Import du Flow

1. **Twilio Console** : https://console.twilio.com
2. **Studio > Flows**
3. Cliquer sur le flow existant ou créer un nouveau
4. **Menu (...)** > **Import from JSON**
5. Copier le contenu de `twilio-flow-v2-interactive.json`
6. **Import**
7. **Publish**

#### B. Configurer le Webhook

1. Dans le flow, vérifier que tous les URLs pointent vers :
   ```
   https://wabracongo.ywcdigital.com/api/can/...
   ```
2. Si vous testez localement, utiliser **ngrok** :
   ```bash
   ngrok http 80
   ```
   Puis remplacer l'URL par : `https://xxxx.ngrok.io/api/can/...`

#### C. Tester via Twilio Console

1. Dans le flow, cliquer sur **Test** (en haut à droite)
2. **Simulate incoming message**
3. From : `+243999999999`
4. Body : `START_AFF_GOMBE`
5. **Send**

**Flow attendu :**
```
[1] check_source → Détecte AFFICHE
[2] set_source_affiche → Variables définies
[3] http_log_scan → Appel API /scan
[4] http_check_user → Appel API /check-user
[5] split_user_exists → Vérifie si user_exists = true
    ├─ Si false → msg_accueil_nouveau (inscription)
    └─ Si true → msg_menu_principal (menu)
```

---

### 7. Test WhatsApp Complet (Production)

**Scénario 1 : Inscription Complète**

```
📱 User → Bot
──────────────────────────────────────
START_AFF_GOMBE

🦁 BIENVENUE !
La CAN arrive à Kinshasa !
...
👉 Tape OUI pour t'inscrire

OUI

Super ! 🙌
C'est quoi ton nom ou pseudo ?

Jean

✅ C'est fait Jean !
Tu es inscrit(e) pour la CAN ! 🎉
...
Reviens nous parler pour accéder au menu ! 🦁
```

**Scénario 2 : Menu Principal**

```
📱 User → Bot
──────────────────────────────────────
Bonjour

👋 Salut Jean !
Que veux-tu faire aujourd'hui ?
1️⃣ Voir les Villages CAN
2️⃣ Voir les matchs & placer un pronostic
3️⃣ Me désinscrire
Tape 1, 2 ou 3

1

🏘️ VILLAGES CAN disponibles :
1. Gombe
   📍 Avenue du Port
   👥 1 membres
2. Masina
   📍 Boulevard Lumumba
   👥 0 membres
✨ Participe et représente ton village !
```

**Scénario 3 : Pronostic**

```
📱 User → Bot
──────────────────────────────────────
Bonjour

👋 Salut Jean !
[Menu]

2

⚽ MATCHS D'AUJOURD'HUI :
1. RDC vs Cameroun
   🕐 15:00
2. Sénégal vs Nigeria
   🕐 18:00
Sur quel match veux-tu placer ton pronostic ?
Tape le numéro du match (1, 2, 3...)

1

🎯 Parfait !
Quel est ton pronostic ?
Formate ta réponse comme ça :
SCORE_A-SCORE_B
Exemple : 2-1 ou 0-0

2-1

✅ Pronostic enregistré !
Ton pronostic : 2-1
🎁 Si tu gagnes, tu recevras un cadeau !
Bonne chance ! 🦁
```

---

## 🐛 Débogage

### Logs Laravel
```bash
tail -f storage/logs/laravel.log
```

### Vérifier la Base de Données
```bash
php artisan tinker
```

```php
// Compter les utilisateurs
\App\Models\User::count();

// Compter les pronostics
\App\Models\Pronostic::count();

// Matchs du jour
\App\Models\FootballMatch::whereDate('match_date', today())->get();

// Villages actifs
\App\Models\Village::where('is_active', true)->get();
```

### Réinitialiser les Tests
```bash
php artisan tinker
```

```php
// Supprimer tous les pronostics de test
\App\Models\Pronostic::truncate();

// Supprimer tous les utilisateurs de test
\App\Models\User::where('source_type', 'DIRECT')->delete();

// Réinitialiser winners_calculated
\App\Models\FootballMatch::query()->update(['winners_calculated' => false]);
```

---

## ✅ Checklist Finale

- [ ] Routes API créées (7 nouvelles)
- [ ] Villages créés dans la base
- [ ] Matchs du jour créés
- [ ] API testées localement (curl)
- [ ] Utilisateur de test créé
- [ ] Pronostic enregistré via API
- [ ] Calcul automatique des gagnants testé
- [ ] Flow Twilio importé
- [ ] Flow Twilio publié
- [ ] Test WhatsApp complet effectué
- [ ] Logs vérifiés (pas d'erreurs)

---

## 📞 Checklist de Production

Avant de mettre en production :

1. **Vérifier l'URL de l'API dans le flow Twilio**
   - Doit être : `https://wabracongo.ywcdigital.com/api/can/...`
   - PAS : `http://localhost/...` ou `ngrok`

2. **Vérifier les villages**
   - Au moins 2-3 villages actifs créés

3. **Vérifier les matchs**
   - Des matchs du jour avec `pronostic_enabled = true`

4. **Tester avec un vrai numéro WhatsApp**
   - Inscription complète
   - Menu principal
   - Affichage villages
   - Placer un pronostic

5. **Vérifier les logs**
   - Pas d'erreurs 500
   - Tous les événements sont loggés

6. **Backup de la base de données**
   ```bash
   php artisan backup:run
   ```

---

**Version de Test :** 2.0
**Status :** ✅ Prêt pour les tests
