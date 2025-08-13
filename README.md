# KocinaSpeed 🍽️

[![Symfony](https://img.shields.io/badge/Symfony-7.1-black.svg)](https://symfony.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1.svg)](https://mysql.com)

**KocinaSpeed** est une plateforme moderne de recettes de cuisine rapides développée avec Symfony 7.1. Elle permet aux utilisateurs de découvrir des recettes simples et savoureuses, avec un système complet d'avis et de notation.

## 📋 Table des matières

- [Fonctionnalités](#-fonctionnalités)
- [Architecture](#-architecture)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Technologies utilisées](#-technologies-utilisées)
- [Structure du projet](#-structure-du-projet)
- [API et Endpoints](#-api-et-endpoints)
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
- **Système de slugs** : URLs SEO-friendly
- **Pagination intelligente** : Avec Pagerfanta
- **Emails automatiques** : Notifications pour les nouveaux avis
- **Reset de mot de passe** : Système sécurisé de récupération

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

| Route | Contrôleur | Description |
|-------|------------|-------------|
| `/` | `HomeController::index` | Page d'accueil |
| `/recettes/{category}` | `RecipeController::recipeList` | Liste des recettes |
| `/recette/{slug}` | `RecipeController::show` | Détail d'une recette |
| `/recherche` | `RecipeController::search` | Recherche de recettes |
| `/admin` | EasyAdmin | Interface d'administration |

## 🔧 API et Endpoints

### Recherche
- **GET** `/recherche?query={term}` - Recherche de recettes par nom

### Administration (EasyAdmin)
- **GET** `/admin` - Dashboard administrateur
- **CRUD** `/admin/recipe` - Gestion des recettes
- **CRUD** `/admin/review` - Modération des avis
- **CRUD** `/admin/user` - Gestion des utilisateurs

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

## 📊 Fonctionnalités à venir

- [ ] API REST complète
- [ ] Système de favoris
- [ ] Export PDF des recettes
- [ ] Notifications push
- [ ] Mode sombre
- [ ] Suggestions de recettes par IA
- [ ] Application mobile

## 🐛 Support et problèmes

Si vous rencontrez des problèmes :
1. Vérifiez les logs dans `var/log/`
2. Consultez la documentation Symfony
3. Ouvrez une issue sur GitHub

## 📄 Licence

Ce projet est sous licence propriétaire. Tous droits réservés.

## 👥 Contributeurs

- **Christophe Mostefaoui** - *Développeur principal* - [GitHub](https://github.com/krismos64)

## 🙏 Remerciements

- Framework Symfony et sa communauté
- UIkit pour l'interface utilisateur
- Toute la communauté PHP

---

**KocinaSpeed** - *Cuisinez vite, cuisinez bien !* 🍽️✨
