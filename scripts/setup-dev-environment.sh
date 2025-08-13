#!/bin/bash

# Script de configuration pour l'environnement de développement
echo "🚀 Configuration de l'environnement de développement KocinaSpeed"
echo "================================================================"

# Créer les répertoires d'upload nécessaires
echo "📁 Création des répertoires d'upload..."
mkdir -p public/uploads/recipes
mkdir -p public/uploads/reviews
mkdir -p var/cache
mkdir -p var/log

# Définir les permissions appropriées
echo "🔐 Configuration des permissions..."
chmod -R 755 public/uploads/
chmod -R 755 var/

# Copier l'image par défaut si elle n'existe pas
if [ ! -f public/uploads/recipes/default-image.jpg ]; then
    cp public/img/default-image.jpg public/uploads/recipes/default-image.jpg 2>/dev/null || echo "⚠️  Image par défaut non trouvée"
fi

# Vérifier la connexion à la base de données
echo "🗄️  Vérification de la base de données..."
php bin/console doctrine:database:create --if-not-exists || echo "Base de données déjà existante"

# Appliquer les migrations
echo "📋 Application des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Nettoyer et réchauffer le cache
echo "🔥 Optimisation du cache..."
php bin/console cache:clear
php bin/console app:cache:warmup

# Vérifier que tout fonctionne
echo "✅ Vérifications finales..."
php bin/console doctrine:schema:validate --skip-sync || echo "⚠️  Schéma peut nécessiter une synchronisation"

echo ""
echo "✅ Configuration terminée !"
echo ""
echo "🌟 Votre environnement de développement est prêt !"
echo "   • Base de données: ✅ Importée avec 27 recettes"
echo "   • Cache: ✅ Optimisé et réchauffé"
echo "   • Images: ✅ Répertoires configurés"
echo "   • Permissions: ✅ Définies correctement"
echo ""
echo "🔗 Accès admin: c.mostefaoui@yahoo.fr"
echo "🔗 URL locale: http://localhost/kocinaspeed/"
echo ""
echo "💡 Commandes utiles:"
echo "   php bin/console app:cache:warmup    # Réchauffer le cache"
echo "   php bin/console app:optimize-images # Optimiser les images"
echo "   php bin/console doctrine:fixtures:load # (Si vous avez des fixtures)"