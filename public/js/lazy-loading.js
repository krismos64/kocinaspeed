/**
 * Lazy Loading avancé pour KocinaSpeed
 * Utilise Intersection Observer pour des performances optimales
 */

document.addEventListener('DOMContentLoaded', function() {
    // Vérifier le support d'Intersection Observer
    if ('IntersectionObserver' in window) {
        initLazyLoading();
    } else {
        // Fallback pour les anciens navigateurs
        loadAllImages();
    }
});

function initLazyLoading() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if (lazyImages.length === 0) return;

    // Configuration de l'observer
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                loadImage(img);
                observer.unobserve(img);
            }
        });
    }, {
        // Charger l'image 100px avant qu'elle soit visible
        rootMargin: '100px 0px',
        threshold: 0.01
    });

    // Observer toutes les images lazy
    lazyImages.forEach(img => {
        imageObserver.observe(img);
    });
}

function loadImage(img) {
    // Afficher un placeholder pendant le chargement
    img.style.opacity = '0.3';
    img.style.transition = 'opacity 0.3s ease';
    
    // Créer une nouvelle image pour le préchargement
    const imageLoader = new Image();
    
    imageLoader.onload = function() {
        // Une fois chargée, remplacer src et afficher
        img.src = img.dataset.src;
        img.style.opacity = '1';
        img.classList.add('loaded');
        
        // Supprimer data-src pour éviter les rechargements
        delete img.dataset.src;
    };
    
    imageLoader.onerror = function() {
        // En cas d'erreur, utiliser l'image par défaut
        img.src = '/img/default-image.jpg';
        img.style.opacity = '1';
        img.classList.add('error');
        console.warn('Erreur de chargement pour l\'image:', img.dataset.src);
    };
    
    // Démarrer le chargement
    imageLoader.src = img.dataset.src;
}

function loadAllImages() {
    // Fallback : charger toutes les images immédiatement
    const lazyImages = document.querySelectorAll('img[data-src]');
    lazyImages.forEach(img => {
        img.src = img.dataset.src;
        delete img.dataset.src;
    });
}

// Optimisation pour les images des recettes en grille
function optimizeRecipeGrid() {
    const recipeCards = document.querySelectorAll('.uk-card');
    
    recipeCards.forEach(card => {
        const img = card.querySelector('img[data-src]');
        if (img) {
            // Ajouter une animation de fade-in
            img.addEventListener('load', function() {
                this.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 200);
            });
        }
    });
}

// Préchargement intelligent des images critiques
function preloadCriticalImages() {
    // Précharger l'image hero et le logo
    const criticalImages = [
        '/img/backgrounds/background4.jpg',
        '/img/logos/logo3.png'
    ];
    
    criticalImages.forEach(src => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'image';
        link.href = src;
        document.head.appendChild(link);
    });
}

// Initialiser les optimisations
document.addEventListener('DOMContentLoaded', function() {
    optimizeRecipeGrid();
    preloadCriticalImages();
});

// Export pour utilisation externe
window.KocinaSpeedLazyLoading = {
    loadImage,
    initLazyLoading,
    preloadCriticalImages
};