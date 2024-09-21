KocinaSpeed est une plateforme de recettes de cuisine rapides conçue pour permettre aux utilisateurs de découvrir des recettes simples. Le projet comprend un espace administrateur pour la gestion des recettes et des avis, ainsi qu’une intégration avec YouTube pour présenter des vidéos de cuisine.

Table des matières

    •	Fonctionnalités
    •	Installation
    •	Configuration
    •	Technologies utilisées
    •	Contributeurs
    •	Licence

Fonctionnalités

    •	Recettes rapides : Accès à des recettes filtrables par catégorie.
    •	Espace administrateur : Gestion des recettes (CRUD) et des avis avec modération.
    •	Avis utilisateurs : Les utilisateurs peuvent noter les recettes de 1 à 5 étoiles et laisser un commentaire.
    •	YouTube Intégration : Les recettes peuvent être accompagnées de vidéos YouTube.
    •	Page de contact : Formulaire pour envoyer des messages au support avec notification par email.
    •	Espace personnel : Gestion du profil utilisateur et fonctionnalités réservées aux administrateurs.

Installation

Pré-requis

    •	PHP 8.1 ou supérieur
    •	Composer
    •	MySQL
    •	Symfony CLI (facultatif mais recommandé)
    •	Serveur web (Apache, Nginx, etc.)
    •	Node.js & npm (pour la gestion des assets)

Étapes d’installation

    1.	Clonez ce dépôt GitHub :
    git clone https://github.com/votre-utilisateur/kocinaspeed.git

cd kocinaspeed

2. Installez les dépendances PHP avec Composer :
   composer install

3. Configurez la base de données dans le fichier .env :
   DATABASE_URL="mysql://username:password@127.0.0.1:3306/kocinaspeed"

4. Créez la base de données et effectuez les migrations :
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate

5. Installez les dépendances JS et CSS :
   npm install
   npm run build

6. Démarrez le serveur de développement Symfony :
   symfony server:start

7. Accédez à l’application sur http://localhost:8000.

Configuration

    •	Variables d’environnement :
    •	Configurez votre fichier .env.local pour définir les clés API, les accès base de données, et les informations d’envoi d’email via MAILER_DSN.

Exemple :
MAILER_DSN=smtp://support@kocinaspeed.fr:password@smtp.server.com:465?encryption=ssl

    •	Gestion des messages échoués : Utilisez Messenger pour la gestion asynchrone des emails. Pour configurer les messages échoués, consultez les fichiers messenger.yaml.

Technologies utilisées

    •	Framework : Symfony 6
    •	Langages : PHP 8, JavaScript (ES6), HTML5/CSS3
    •	Base de données : MySQL
    •	ORM : Doctrine
    •	Front-end : UIkit, Twig
    •	Système de templates : Twig
    •	Gestion des tâches asynchrones : Symfony Messenger
    •	Emails : Symfony Mailer avec SMTP
    •	Vidéo : Intégration YouTube

Contributeurs

    •	Christophe Mostefaoui - Développeur principal

Si vous souhaitez contribuer, n’hésitez pas à ouvrir une issue ou une pull request. Toute suggestion est la bienvenue !

Licence

Ce projet est sous licence MIT. Consultez le fichier LICENSE pour plus d’informations.

Tu peux bien sûr adapter ce fichier en fonction des spécificités de ton projet ou ajouter des sections supplémentaires comme des captures d’écran ou des informations sur les versions.
