# KocinaSpeed 🍽️

[![Symfony](https://img.shields.io/badge/Symfony-7.1-black.svg)](https://symfony.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1.svg)](https://mysql.com)
[![SEO](https://img.shields.io/badge/SEO-Optimized-green.svg)](https://kocinaspeed.fr)

**KocinaSpeed** est une plateforme moderne de recettes de cuisine française rapides et délicieuses. Développée avec Symfony 7.1, elle offre une expérience utilisateur exceptionnelle avec un design moderne, des fonctionnalités avancées et une optimisation SEO complète pour Google et les IA.

## 📋 Table des matières

- [Fonctionnalités](#-fonctionnalités)
- [Architecture](#-architecture)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Technologies utilisées](#-technologies-utilisées)
- [Optimisations SEO](#-optimisations-seo)
- [Structure du projet](#-structure-du-projet)
- [Commandes personnalisées](#-commandes-personnalisées)
- [API et Endpoints](#-api-et-endpoints)
- [Support](#-support)
- [Contributeurs](#-contributeurs)

## ✨ Fonctionnalités

### 👥 **Côté utilisateur**

- **Navigation par catégories** : Desserts, Plats, Apéritifs
- **Recherche avancée** : Recherche par nom de recette en temps réel
- **Système de notation** : Notes de 1 à 5 étoiles avec commentaires
- **Upload d'images** : Les utilisateurs peuvent ajouter des photos de leurs réalisations
- **Interface responsive** : Design moderne avec UIkit et dégradés subtils

### 🛠️ **Administration (EasyAdmin)**

- **Gestion complète des recettes** : CRUD avec gestion des images multiples
- **Modération des avis** : Système d'approbation des commentaires
- **Gestion des utilisateurs** : Rôles et permissions
- **Messages de contact** : Interface de gestion des demandes utilisateurs
- **Dashboard moderne** : Interface intuitive et statistiques

### 🎥 **Fonctionnalités avancées**

- **Intégration YouTube** : Vidéos de démonstration pour les recettes
- **Podcast intégré** : Présentation audio de KocinaSpeed
- **Système de slugs** : URLs SEO-friendly optimisées
- **Pagination intelligente** : Avec Pagerfanta
- **Emails automatiques** : Notifications pour les nouveaux avis
- **Reset de mot de passe** : Système sécurisé de récupération
- **Optimisation images** : Compression et lazy loading automatiques
- **PWA Ready** : Installation possible comme application native

## 🏗️ Architecture

```
src/
├── Controller/           # Contrôleurs (Home, Recipe, Review, Contact, etc.)
│   └── Admin/           # Contrôleurs d'administration EasyAdmin
├── Entity/              # Entités Doctrine (Recipe, User, Review, etc.)
├── Form/                # Types de formulaires Symfony
├── Repository/          # Repositories Doctrine
└── Command/             # Commandes console personnalisées

templates/
├── base.html.twig       # Template de base
├── home/                # Templates de la page d'accueil
├── recipe/              # Templates des recettes
├── admin/               # Templates d'administration
└── emails/              # Templates d'emails

public/
├── uploads/             # Images uploadées
│   ├── recipes/         # Images des recettes
│   └── reviews/         # Images des avis
└── img/                 # Assets statiques
```

## 🚀 Installation

### Pré-requis

- **PHP 8.2+** avec extensions : `ctype`, `iconv`
- **Composer** (gestionnaire de dépendances PHP)
- **MySQL 5.7+** ou **MariaDB 10.2+**
- **Node.js & npm** (pour les assets front-end)
- **Symfony CLI** (optionnel mais recommandé)

### Étapes d'installation

1. **Cloner le projet**

```bash
git clone https://github.com/krismos64/kocinaspeed.git
cd kocinaspeed
```

2. **Installer les dépendances PHP**

```bash
composer install
```

3. **Configuration de l'environnement**

```bash
cp .env .env.local
# Éditer .env.local avec vos paramètres
```

4. **Configuration de la base de données**

```env
DATABASE_URL="mysql://username:password@127.0.0.1:3306/kocinaspeed?serverVersion=8.0"
```

5. **Créer la base de données**

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

6. **Créer un utilisateur administrateur**

```bash
php bin/console app:create-admin-user
```

7. **Installer les assets**

```bash
php bin/console importmap:install
```

8. **Démarrer le serveur de développement**

```bash
symfony server:start
# ou
php -S localhost:8000 -t public
```

9. **Accéder à l'application**

- **Site public** : http://localhost:8000
- **Administration** : http://localhost:8000/admin

## ⚙️ Configuration

### Variables d'environnement (.env.local)

```env
# Base de données
DATABASE_URL="mysql://username:password@127.0.0.1:3306/kocinaspeed?serverVersion=8.0"

# Configuration email
MAILER_DSN=smtp://support@kocinaspeed.fr:password@smtp.server.com:465

# Environnement
APP_ENV=dev
APP_SECRET=your-secret-key

# Répertoires d'upload (configurés dans services.yaml)
RECIPE_IMAGES_DIR=%kernel.project_dir%/public/uploads/recipes
REVIEW_IMAGES_DIR=%kernel.project_dir%/public/uploads/reviews
```

### Configuration des permissions

```bash
# Permissions pour les répertoires d'upload
sudo chown -R www-data:www-data public/uploads/
sudo chmod -R 755 public/uploads/

# Cache et logs
sudo chown -R www-data:www-data var/
sudo chmod -R 755 var/
```

### Messagerie asynchrone

Le projet utilise **Symfony Messenger** pour l'envoi d'emails asynchrones. Configuration dans `config/packages/messenger.yaml`.

## 🛠️ Technologies utilisées

### Backend

- **Symfony 7.1** - Framework PHP moderne
- **PHP 8.2+** - Langage backend avec les dernières fonctionnalités
- **Doctrine ORM** - Mapping objet-relationnel
- **MySQL/MariaDB** - Base de données relationnelle
- **EasyAdmin 4** - Interface d'administration
- **Symfony Messenger** - Gestion des tâches asynchrones

### Frontend

- **UIkit 3** - Framework CSS moderne et responsive
- **Twig** - Moteur de templates
- **Stimulus** - Framework JavaScript léger
- **AssetMapper** - Gestion des assets Symfony

### Fonctionnalités

- **Symfony Security** - Authentification et autorisation
- **Symfony Mailer** - Envoi d'emails avec support SMTP
- **Pagerfanta** - Pagination avancée
- **Symfony Form** - Gestion des formulaires
- **Doctrine Migrations** - Gestion des évolutions de BDD

## 🚀 Optimisations SEO

### Référencement Google & IA

- **Métadonnées complètes** : Title, description, keywords optimisés
- **Open Graph & Twitter Cards** : Partage social optimisé
- **Données structurées JSON-LD** : Schema.org Recipe, Organization, FAQPage
- **Sitemap XML dynamique** : URLs, images et vidéos indexées
- **Robots.txt intelligent** : Optimisé pour IA (GPTBot, Claude, ChatGPT)
- **Canonical URLs** : Éviter le contenu dupliqué
- **Hreflang** : Support international (fr, fr-FR)
- **Core Web Vitals** : Performance et UX optimisées

### Spécificités IA/LLM

- **Balises méta IA** : content-type, schema-context
- **Descriptions enrichies** : Contexte complet pour compréhension IA
- **Alt text automatique** : Images avec descriptions contextuelles
- **Service SEO** : Génération automatique de métadonnées

## 📁 Structure du projet

### Entités principales

```php
Recipe {
    id, name, slug, description,
    ingredients (JSON), cookingTime,
    category, rating, video (YouTube),
    images (OneToMany), reviews (OneToMany)
}

User {
    id, email, password, name, roles,
    resetToken, resetTokenExpiry,
    reviews (OneToMany)
}

Review {
    id, rating (1-5), comment, approved,
    visitorName, visitorEmail,
    recipe (ManyToOne), user (ManyToOne),
    images (OneToMany)
}
```

### Routes principales

| Route                  | Contrôleur                           | Description                   |
| ---------------------- | ------------------------------------ | ----------------------------- |
| `/`                    | `RecipeController::index`            | Page d'accueil                |
| `/recettes/{category}` | `RecipeController::recipeList`       | Liste des recettes            |
| `/recette/{slug}`      | `RecipeController::show`             | Détail d'une recette          |
| `/recherche`           | `RecipeController::search`           | Recherche de recettes         |
| `/contact`             | `ContactController::contact`         | Formulaire de contact moderne |
| `/mentions-legales`    | `DefaultController::mentionsLegales` | Mentions légales              |
| `/admin`               | EasyAdmin                            | Interface d'administration    |
| `/sitemap.xml`         | `SitemapController::index`           | Sitemap XML dynamique         |
| `/robots.txt`          | `SitemapController::robots`          | Robots.txt optimisé           |

## 🔧 Commandes personnalisées

### Commandes d'administration

```bash
# Créer un utilisateur administrateur
php bin/console app:create-admin-user

# Optimiser toutes les images du projet
php bin/console app:optimize-images

# Réchauffer le cache avec les données critiques
php bin/console app:cache:warmup
```

### Script de configuration

```bash
# Configuration complète de l'environnement de développement
./scripts/setup-dev-environment.sh
```

## 🔧 API et Endpoints

### Recherche

- **GET** `/recherche?query={term}` - Recherche de recettes par nom

### Administration (EasyAdmin)

- **GET** `/admin` - Dashboard administrateur
- **CRUD** `/admin/recipe` - Gestion des recettes
- **CRUD** `/admin/review` - Modération des avis
- **CRUD** `/admin/user` - Gestion des utilisateurs
- **CRUD** `/admin/contact` - Messages de contact

### SEO et Outils

- **GET** `/sitemap.xml` - Plan du site XML automatique
- **GET** `/sitemap-images.xml` - Sitemap des images
- **GET** `/robots.txt` - Directives pour robots et IA
- **GET** `/manifest.json` - Manifest PWA

## 🧪 Tests et développement

### Commandes utiles

```bash
# Cache
php bin/console cache:clear

# Base de données
php bin/console doctrine:schema:update --dump-sql
php bin/console doctrine:fixtures:load

# Assets
php bin/console importmap:update

# Tests (si configurés)
php bin/phpunit
```

### Développement

Pour contribuer au projet :

1. Forkez le repository
2. Créez une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Committez vos changements (`git commit -am 'Ajout nouvelle fonctionnalité'`)
4. Pushez sur la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créez une Pull Request

### Design et UX

- **Design moderne** : Dégradés subtils et animations fluides
- **Interface responsive** : Optimisée mobile-first avec UIkit
- **Formulaire de contact** : Design glassmorphism avec effets visuels
- **Chatbot stylisé** : Interface néon avec animations interactives
- **Lazy loading** : Optimisation des performances images

## 🐛 Support

### Résolution de problèmes

1. Vérifiez les logs dans `var/log/dev.log` ou `var/log/prod.log`
2. Consultez la documentation Symfony officielle
3. Utilisez les commandes de débogage Symfony
4. Vérifiez les permissions des répertoires `var/` et `public/uploads/`

### Contact

- **Email** : support@kocinaspeed.fr
- **GitHub Issues** : Pour les bugs et améliorations

## 👥 Contributeurs

- **Christophe Mostefaoui** - _Développeur_ - [GitHub](https://github.com/krismos64)

## 🙏 Remerciements

- Framework Symfony et sa communauté
- UIkit pour l'interface utilisateur
- Toute la communauté PHP

---

**KocinaSpeed** - _Cuisinez vite, cuisinez bien !_ 🍽️✨
