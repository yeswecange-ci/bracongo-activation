# 📧 Guide Complet - Campagnes & Templates WhatsApp

## 🎯 Vue d'ensemble

Le système de campagnes permet d'envoyer des messages WhatsApp en masse aux utilisateurs pour :
- 📅 Notifier les matchs du jour et demander les pronostics
- 🏆 Annoncer les gagnants
- 📢 Envoyer des informations importantes
- 🎁 Alerter sur les prix disponibles

---

## 📋 Système de Templates

### **Pourquoi utiliser des templates ?**

Les templates permettent de :
- ✅ Créer des messages réutilisables
- ✅ Maintenir une cohérence dans la communication
- ✅ Gagner du temps lors de la création de campagnes
- ✅ Utiliser des médias (images, vidéos)
- ✅ Ajouter des boutons interactifs

---

## 🏗️ Créer un Template

### Accès
👉 **Admin → Templates** (dans la sidebar)

### Types de Templates

#### 1. **Texte Simple**
Message texte basique sans media ni boutons.

**Exemple :**
```
Bonjour {nom},

Nouveau match aujourd'hui !
RDC vs Maroc - 15 Janvier à 20h00

Envoie ton pronostic maintenant !
```

#### 2. **Media + Texte**
Message avec une image/vidéo d'en-tête.

**Composants :**
- **Header** : Image ou vidéo (max 5MB)
- **Body** : Texte du message
- **Footer** : Texte court en bas (optionnel)

**Exemple d'utilisation :**
- Header : Photo du stade ou logo CAN 2025
- Body : Détails du match
- Footer : "CAN 2025 - Powered by Bracongo"

#### 3. **Texte + Boutons**
Message avec des boutons cliquables.

**Types de boutons :**
- **Réponse rapide** : Bouton qui envoie un message
- **URL** : Bouton qui ouvre un lien

**Exemple :**
```
Body: Nouveau match ce soir ! Veux-tu recevoir les détails ?

Boutons:
- "Oui, envoie-moi !" (réponse rapide)
- "Voir les matchs" (URL → site web)
```

#### 4. **Interactif (Media + Boutons)**
Combinaison de tout : image + texte + boutons.

---

## 🎨 Variables Dynamiques

Les templates supportent des variables qui seront automatiquement remplacées :

| Variable | Remplacée par | Exemple |
|----------|--------------|---------|
| `{nom}` | Nom complet de l'utilisateur | Jean Dupont |
| `{prenom}` | Prénom de l'utilisateur | Jean |
| `{village}` | Village de l'utilisateur | Gombe |
| `{phone}` | Numéro de téléphone | +243812345678 |
| `{match}` | Nom du match | RDC vs Maroc |
| `{date}` | Date du match | 15 Janvier 2025 |
| `{heure}` | Heure du match | 20h00 |

**Exemple de template :**
```
Bonjour {nom} de {village},

Match de ce soir :
{match} - {date} à {heure}

Envoie ton pronostic maintenant !
```

**Devient pour Jean de Gombe :**
```
Bonjour Jean Dupont de Gombe,

Match de ce soir :
RDC vs Maroc - 15 Janvier 2025 à 20h00

Envoie ton pronostic maintenant !
```

---

## 📝 Créer un Template - Étape par Étape

### 1. Aller sur "Nouveau Template"
`Admin → Templates → Nouveau Template`

### 2. Informations de base

**Nom du template :**
```
Ex: Match du Jour - Notification
```

**Catégorie :**
- `match_notification` : Pour les notifications de matchs
- `prize_alert` : Pour annoncer les gagnants
- `reminder` : Rappels
- `welcome` : Message de bienvenue
- `info` : Informations générales

**Type :**
Choisis selon tes besoins (voir "Types de Templates" ci-dessus)

### 3. En-tête (Header) - *Optionnel*

**Si tu choisis "Media" ou "Interactif" :**

**Type d'en-tête :**
- `Texte` : Titre court (max 60 caractères)
- `Image` : JPG/PNG (max 5MB)
- `Vidéo` : MP4 (max 5MB)
- `Document` : PDF (max 5MB)

**Exemple :**
- Type : Image
- Fichier : Logo CAN 2025 ou photo du stade

### 4. Corps du message (Body) - *Obligatoire*

Rédige ton message (max 1024 caractères).

**Utilise les variables :**
```
Bonjour {nom},

🏆 Nouveau match aujourd'hui !

{match}
📅 {date} à {heure}

Envoie ton pronostic maintenant et gagne des cadeaux !
```

### 5. Pied de page (Footer) - *Optionnel*

Texte court en bas du message (max 60 caractères).

**Exemple :**
```
CAN 2025 Kinshasa - Bracongo
```

### 6. Boutons - *Optionnel*

**Si tu choisis "Boutons" ou "Interactif" :**

Ajoute jusqu'à 3 boutons.

**Exemple :**
```
Bouton 1: "Envoyer pronostic" (réponse rapide)
Bouton 2: "Voir classement" (URL)
```

### 7. Statut

☑️ **Template actif** : Cocher pour que le template soit disponible

### 8. Créer

Clique sur **"Créer le Template"** ✅

---

## 📤 Créer une Campagne

### Accès
👉 **Admin → Campagnes → Nouvelle Campagne**

### Étape par Étape

#### 1. **Nom de la campagne**

```
Ex: Match RDC vs Maroc - 15 Janvier
```

#### 2. **Template (Optionnel)**

Tu peux :
- **Sélectionner un template existant** : Le message sera pré-rempli
- **Laisser vide** : Saisie manuelle

**Si tu sélectionnes un template :**
- Le message apparaît automatiquement
- Tu peux le modifier si nécessaire

#### 3. **Audience cible**

**Choix :**

**a) Tous les utilisateurs**
- Envoie à tous les utilisateurs actifs

**b) Par village**
- Sélectionne un village (ex: Gombe)
- Envoie uniquement aux utilisateurs de ce village

**c) Par statut**
- `REGISTERED` : Tous les inscrits
- `OPT_IN` : Utilisateurs qui ont confirmé l'opt-in
- `ACTIVE` : Utilisateurs qui ont déjà fait des pronostics

**💡 Le nombre de destinataires estimés s'affiche automatiquement**

#### 4. **Message**

Rédige ou modifie ton message.

**Variables disponibles :**
- `{nom}`, `{prenom}`, `{village}`, `{phone}`

**Exemple :**
```
Bonjour {nom},

Nouveau match ce soir !
RDC vs Maroc - 15 Janvier à 20h00

Envoie ton pronostic maintenant !
```

**Le compteur de caractères affiche : X/1600**

#### 5. **Programmation**

**Deux options :**

**a) Envoi immédiat (brouillon)**
- Laisser "Date et heure d'envoi" vide
- La campagne sera en statut "Brouillon"
- Tu pourras l'envoyer manuellement plus tard

**b) Envoi programmé**
- Sélectionne une date et heure
- La campagne sera en statut "Programmé"
- Elle s'enverra automatiquement à l'heure prévue

#### 6. **Créer**

Clique sur **"Créer la Campagne"** ✅

---

## 🚀 Envoyer une Campagne

### 1. Accéder à la campagne

`Admin → Campagnes → Cliquer sur la campagne`

### 2. Vérifier les détails

- ✅ Audience cible
- ✅ Nombre de destinataires
- ✅ Message

### 3. Envoyer

**Cliquer sur "Envoyer la Campagne"**

### 4. Page de confirmation

**Récapitulatif affiché :**
- Nom de la campagne
- Audience
- **Nombre de destinataires**
- Aperçu du message

**Options :**
- ☑️ **Mode test** : Envoyer uniquement à toi-même (pour tester)

### 5. Confirmer

**Cliquer sur "Confirmer et Envoyer"** ✅

⚠️ **Attention** : Cette action ne peut pas être annulée !

---

## 📊 Suivi des Campagnes

### Statuts des campagnes

| Statut | Description |
|--------|-------------|
| 🔘 **Brouillon** | Campagne créée, pas encore envoyée |
| ⏰ **Programmé** | Envoi programmé à une date/heure précise |
| 🔄 **En cours** | Campagne en cours d'envoi |
| ✅ **Envoyé** | Campagne complètement envoyée |

### Statistiques

**Sur la page de détails :**
- **Destinataires** : Nombre total
- **Envoyés** : Nombre de messages envoyés avec succès
- **Échecs** : Nombre d'échecs (si applicable)

---

## 🎯 Cas d'Usage Principal

### **Envoyer les Matchs du Jour**

#### Objectif
Notifier tous les utilisateurs des matchs du jour pour qu'ils envoient leurs pronostics.

#### Étapes

##### 1. **Créer un Template** (une seule fois)

```
Nom: Notification Match du Jour
Catégorie: match_notification
Type: Media + Texte

Header:
- Type: Image
- Fichier: Logo CAN 2025

Body:
Bonjour {nom},

🏆 Match d'aujourd'hui !

{match}
📅 {date} à {heure}

Envoie ton pronostic via WhatsApp pour gagner des cadeaux !

Footer:
CAN 2025 - Powered by Bracongo
```

##### 2. **Créer la Campagne** (chaque jour)

```
Nom: Match RDC vs Maroc - 15 Janvier
Template: Notification Match du Jour
Audience: Tous les utilisateurs
Message: (pré-rempli, personnaliser {match}, {date}, {heure})
Programmation: Laisser vide (envoi immédiat)
```

**Message personnalisé :**
```
Bonjour {nom},

🏆 Match d'aujourd'hui !

RDC vs Maroc
📅 15 Janvier 2025 à 20h00

Envoie ton pronostic via WhatsApp pour gagner des cadeaux !
```

##### 3. **Envoyer**

- Vérifier le message
- Tester avec "Mode test" (optionnel)
- Confirmer et envoyer

##### 4. **Les utilisateurs reçoivent**

```
Bonjour Jean Dupont,

🏆 Match d'aujourd'hui !

RDC vs Maroc
📅 15 Janvier 2025 à 20h00

Envoie ton pronostic via WhatsApp pour gagner des cadeaux !

_______________
CAN 2025 - Powered by Bracongo
```

##### 5. **Ils répondent sur WhatsApp**

Le bot Twilio Studio les guide pour envoyer leur pronostic.

---

## 💡 Bonnes Pratiques

### **Templates**

✅ **DO (À faire) :**
- Créer des templates pour les messages récurrents
- Utiliser des variables pour personnaliser
- Tester le template avant de l'utiliser dans une campagne
- Activer uniquement les templates finalisés
- Utiliser des noms descriptifs

❌ **DON'T (À éviter) :**
- Créer trop de templates similaires
- Oublier de personnaliser les variables
- Utiliser des images trop lourdes (> 5MB)
- Laisser des templates de test actifs

### **Campagnes**

✅ **DO (À faire) :**
- Toujours utiliser le mode test avant un envoi massif
- Vérifier le nombre de destinataires
- Relire le message avant d'envoyer
- Programmer les envois aux heures optimales (10h-20h)
- Suivre les statistiques après envoi

❌ **DON'T (À éviter) :**
- Envoyer trop de messages par jour (max 2-3)
- Envoyer la nuit ou très tôt le matin
- Oublier de personnaliser le message
- Négliger le mode test

### **Variables**

✅ **DO (À faire) :**
- Toujours utiliser `{nom}` pour personnaliser
- Tester avec plusieurs profils utilisateurs
- Vérifier que les variables sont correctes

❌ **DON'T (À éviter) :**
- Utiliser des variables qui n'existent pas
- Oublier les accolades `{}` autour des variables

---

## 🔧 Actions Après Déploiement

### Sur le serveur Coolify

```bash
# 1. Appliquer la nouvelle migration
php artisan migrate

# 2. Vider les caches
php artisan optimize:clear

# 3. Vérifier les routes
php artisan route:list --path=admin/templates
php artisan route:list --path=admin/campaigns
```

### Vérifier les pages

1. **Templates** : https://wabracongo.ywcdigital.com/admin/templates
2. **Campagnes** : https://wabracongo.ywcdigital.com/admin/campaigns

---

## 📱 Navigation

**Sidebar mise à jour :**
```
Dashboard
Villages
Partenaires
Matchs
Joueurs
Lots
Pronostics
Classement
QR Codes
📄 Templates  ← NOUVEAU
📧 Campagnes
📊 Analytics
```

---

## ✅ Checklist de Test

Avant d'utiliser en production :

- [ ] Migration appliquée (`php artisan migrate`)
- [ ] Routes fonctionnelles
- [ ] Créer un template de test
- [ ] Prévisualiser le template
- [ ] Créer une campagne de test
- [ ] Tester en mode test (envoi à soi-même)
- [ ] Vérifier la réception sur WhatsApp
- [ ] Vérifier que les variables sont remplacées
- [ ] Tester avec un template media (image)
- [ ] Vérifier les statistiques après envoi

---

## 🎉 Résultat Final

**Workflow complet :**

1. **Admin crée un template** → Template réutilisable sauvegardé
2. **Admin crée une campagne** → Sélectionne le template
3. **Admin personnalise** → Remplace {match}, {date}, {heure}
4. **Admin envoie** → Message envoyé à tous les utilisateurs
5. **Utilisateurs reçoivent** → Message personnalisé sur WhatsApp
6. **Utilisateurs répondent** → Bot Twilio Studio traite les réponses
7. **Admin suit** → Statistiques d'envoi et taux de réponse

---

**🚀 Le système est maintenant 100% fonctionnel et prêt pour la CAN 2025 !**
