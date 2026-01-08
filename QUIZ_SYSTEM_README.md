# Système de Quiz CAN 2025 - Documentation

## Vue d'ensemble

Le système de quiz est un module complet ajouté à la plateforme Bracongo CAN 2025 qui permet aux utilisateurs de répondre à des questions via WhatsApp et de gagner des points.

## Fonctionnalités Implémentées ✅

### 1. Base de données (Complété)
- ✅ Table `quiz_questions` : stocke les questions avec options et réponses correctes
- ✅ Table `quiz_answers` : stocke les réponses des utilisateurs avec scores
- ✅ Ajout de `quiz_score` et `quiz_answers_count` dans la table `users`

### 2. Modèles Eloquent (Complété)
- ✅ **QuizQuestion** : modèle pour les questions avec scopes et statistiques
- ✅ **QuizAnswer** : modèle pour les réponses avec calcul automatique des points
- ✅ Relations ajoutées dans le modèle **User**

### 3. API WhatsApp/Twilio (Complété)
- ✅ `POST /api/can/quiz/check-user` - Vérifier si l'utilisateur existe
- ✅ `GET /api/can/quiz/questions` - Récupérer les questions actives
- ✅ `GET /api/can/quiz/questions/formatted` - Question formatée pour WhatsApp
- ✅ `POST /api/can/quiz/check-answer` - Vérifier si déjà répondu
- ✅ `POST /api/can/quiz/answer` - Enregistrer une réponse
- ✅ `POST /api/can/quiz/history` - Historique des réponses
- ✅ `GET /api/can/quiz/leaderboard` - Classement des joueurs

### 4. Flow Twilio Studio (Complété)
- ✅ Fichier JSON complet : `twilio_quiz_flow.json`
- ✅ Vérification de l'utilisateur avant de jouer
- ✅ Affichage de la question avec options A, B, C, D
- ✅ Validation de la réponse
- ✅ Blocage des réponses multiples (une seule réponse par question)
- ✅ Affichage du résultat immédiat (correct/incorrect)
- ✅ Option pour continuer ou voir l'historique
- ✅ Affichage de l'historique complet

## Fonctionnalités à Compléter 📝

### 1. Panel Admin (À développer)
Les contrôleurs ont été créés mais les vues doivent être développées :

#### A. Gestion des Questions (`/admin/quiz/questions`)
**Routes nécessaires :**
```php
Route::resource('quiz/questions', QuizQuestionController::class);
```

**Pages à créer :**
- `resources/views/admin/quiz/questions/index.blade.php` - Liste des questions
- `resources/views/admin/quiz/questions/create.blade.php` - Créer une question
- `resources/views/admin/quiz/questions/edit.blade.php` - Modifier une question

**Fonctionnalités :**
- [ ] Ajouter/modifier/supprimer des questions
- [ ] Définir les 3-4 options de réponse (A, B, C, D optionnel)
- [ ] Sélectionner la bonne réponse
- [ ] Définir les points (par défaut 10)
- [ ] Activer/désactiver une question
- [ ] Définir l'ordre d'affichage
- [ ] Voir les statistiques par question

#### B. Gestion des Réponses (`/admin/quiz/answers`)
**Routes nécessaires :**
```php
Route::get('quiz/answers', [QuizAnswerController::class, 'index'])->name('admin.quiz.answers.index');
Route::get('quiz/answers/{question}', [QuizAnswerController::class, 'show'])->name('admin.quiz.answers.show');
Route::get('quiz/leaderboard', [QuizAnswerController::class, 'leaderboard'])->name('admin.quiz.leaderboard');
Route::get('quiz/export', [QuizAnswerController::class, 'export'])->name('admin.quiz.export');
```

**Pages à créer :**
- `resources/views/admin/quiz/answers/index.blade.php` - Liste des réponses
- `resources/views/admin/quiz/answers/show.blade.php` - Détails par question
- `resources/views/admin/quiz/leaderboard.blade.php` - Classement des joueurs

**Fonctionnalités :**
- [ ] Voir toutes les réponses des utilisateurs
- [ ] Filtrer par question/utilisateur
- [ ] Voir le classement des joueurs par score
- [ ] Exporter les résultats en CSV
- [ ] Statistiques globales du quiz

### 2. Navigation (À mettre à jour)
Dans `resources/views/components/admin-nav.blade.php`, ajouter :

```html
<li>
    <a href="{{ route('admin.quiz.questions.index') }}"
       class="flex items-center px-4 py-2 {{ request()->is('admin/quiz/questions*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
        <svg class="w-5 h-5 mr-3" ...>...</svg>
        Questions Quiz
    </a>
</li>
<li>
    <a href="{{ route('admin.quiz.answers.index') }}"
       class="flex items-center px-4 py-2 {{ request()->is('admin/quiz/answers*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
        <svg class="w-5 h-5 mr-3" ...>...</svg>
        Réponses Quiz
    </a>
</li>
<li>
    <a href="{{ route('admin.quiz.leaderboard') }}"
       class="flex items-center px-4 py-2 {{ request()->is('admin/quiz/leaderboard') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
        <svg class="w-5 h-5 mr-3" ...>...</svg>
        Classement Quiz
    </a>
</li>
```

## Configuration Twilio Studio

### Étapes pour importer le flow :

1. **Accéder à Twilio Studio** :
   - Connecte-toi à https://console.twilio.com
   - Va dans "Studio" > "Flows"

2. **Créer un nouveau Flow** :
   - Clique sur "Create new Flow"
   - Nom : "CAN 2025 - Quiz"
   - Clique sur "Next"

3. **Importer le JSON** :
   - En bas à gauche, clique sur "Show Flow JSON"
   - Supprime tout le contenu existant
   - Copie tout le contenu du fichier `twilio_quiz_flow.json`
   - Colle-le dans l'éditeur JSON
   - Clique sur "Apply" (ou "Valider")

4. **Configurer les URLs** :
   - Vérifie que toutes les URLs pointent vers ton domaine :
   - `https://can-wabracongo.ywcdigital.com/api/can/quiz/*`
   - Si ton domaine est différent, utilise "Find & Replace" pour remplacer l'URL

5. **Publier le Flow** :
   - Clique sur "Publish" en haut à droite
   - Confirme la publication

6. **Connecter à WhatsApp** :
   - Va dans "Messaging" > "Try it out" > "Send a WhatsApp message"
   - Configure ton numéro WhatsApp Business
   - Dans les paramètres du numéro, définis le Flow comme le webhook pour les messages entrants

## Utilisation du Système

### Flow Utilisateur WhatsApp

1. **Démarrage** :
   - L'utilisateur envoie n'importe quel message au bot quiz
   - Le système vérifie si l'utilisateur existe dans la base de données

2. **Si l'utilisateur n'existe pas** :
   - Message : "Tu dois d'abord t'inscrire"
   - L'utilisateur doit d'abord utiliser le flow d'inscription

3. **Si l'utilisateur existe** :
   - Le système récupère la première question non répondue
   - Affiche la question avec les options A, B, C, (D)

4. **Réponse** :
   - L'utilisateur répond par A, B, C ou D
   - Le système valide et enregistre la réponse
   - Affiche immédiatement si c'est correct ou incorrect
   - Affiche le score mis à jour

5. **Continuer ou Arrêter** :
   - Le système demande si l'utilisateur veut continuer
   - OUI = nouvelle question
   - NON = affichage de l'historique complet

6. **Si toutes les questions sont répondues** :
   - Message : "Bravo ! Tu as répondu à toutes les questions"
   - Affichage du score final et de l'historique

### Logique de Points

- **10 points** par bonne réponse (configurable par question)
- **0 point** pour une mauvaise réponse
- Les points sont ajoutés immédiatement au score total de l'utilisateur
- Le compteur `quiz_answers_count` est incrémenté à chaque réponse

### Contraintes

- ✅ Un utilisateur ne peut répondre qu'**une seule fois** par question
- ✅ Si l'utilisateur essaie de répondre à nouveau, il voit sa réponse précédente
- ✅ Les questions inactives ne sont pas proposées
- ✅ Les questions sont affichées dans l'ordre défini (champ `order`)

## Exemple de Questions à Créer

Pour tester le système, voici des exemples de questions :

### Question 1
- **Question** : "Quel pays a remporté la première Coupe d'Afrique des Nations ?"
- **Option A** : Égypte
- **Option B** : Ghana
- **Option C** : Cameroun
- **Option D** : Nigeria
- **Réponse correcte** : A
- **Points** : 10

### Question 2
- **Question** : "En quelle année a été créée la CAN ?"
- **Option A** : 1955
- **Option B** : 1957
- **Option C** : 1960
- **Option D** : 1963
- **Réponse correcte** : B
- **Points** : 10

### Question 3
- **Question** : "Quel pays a remporté le plus de fois la CAN ?"
- **Option A** : Ghana
- **Option B** : Cameroun
- **Option C** : Égypte
- **Option D** : (vide)
- **Réponse correcte** : C
- **Points** : 10

## Tests et Debugging

### Tester l'API directement

```bash
# Vérifier un utilisateur
curl -X POST https://can-wabracongo.ywcdigital.com/api/can/quiz/check-user \
  -H "Content-Type: application/json" \
  -d '{"phone":"+243812345678"}'

# Récupérer une question
curl "https://can-wabracongo.ywcdigital.com/api/can/quiz/questions/formatted?phone=%2B243812345678"

# Enregistrer une réponse
curl -X POST https://can-wabracongo.ywcdigital.com/api/can/quiz/answer \
  -H "Content-Type: application/json" \
  -d '{"phone":"+243812345678","question_id":1,"answer":"A"}'

# Voir l'historique
curl -X POST https://can-wabracongo.ywcdigital.com/api/can/quiz/history \
  -H "Content-Type: application/json" \
  -d '{"phone":"+243812345678"}'

# Voir le classement
curl "https://can-wabracongo.ywcdigital.com/api/can/quiz/leaderboard"
```

### Ajouter des questions manuellement (via Tinker)

```php
php artisan tinker

// Créer une question
QuizQuestion::create([
    'question' => 'Quel pays a remporté la première CAN ?',
    'option_a' => 'Égypte',
    'option_b' => 'Ghana',
    'option_c' => 'Cameroun',
    'option_d' => 'Nigeria',
    'correct_answer' => 'A',
    'points' => 10,
    'is_active' => true,
    'order' => 1,
]);

// Voir toutes les questions actives
QuizQuestion::active()->ordered()->get();

// Voir le score d'un utilisateur
User::where('phone', '+243812345678')->first()->quiz_score;

// Voir les réponses d'un utilisateur
User::where('phone', '+243812345678')->first()->quizAnswers()->with('question')->get();
```

## Améliorations Futures Possibles

- [ ] Ajouter un système de badges (Bronze, Argent, Or)
- [ ] Permettre des questions avec images
- [ ] Ajouter un timer par question (temps limité)
- [ ] Créer des catégories de questions (Football, Culture, Histoire, etc.)
- [ ] Permettre des quiz à thème ou des défis quotidiens
- [ ] Ajouter des récompenses pour les meilleurs scores
- [ ] Intégrer le quiz score au classement général (avec les pronostics)
- [ ] Notification WhatsApp quand de nouvelles questions sont ajoutées

## Support et Contact

Pour toute question ou problème :
- Consultez les logs Laravel : `storage/logs/laravel.log`
- Vérifiez les logs Twilio dans la console Twilio
- Utilisez `php artisan tinker` pour inspecter les données

---

✅ **Système Core Complété** : Base de données, Modèles, API, Flow Twilio
📝 **À faire** : Panel Admin (vues uniquement, la logique est prête)

**Bonne chance avec ton quiz CAN 2025 ! ⚽🎯**
