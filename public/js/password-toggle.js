/**
 * Gestionnaire d'affichage/masquage des mots de passe
 * Permet d'ajouter un bouton œil sur les champs de mot de passe
 */

class PasswordToggle {
    constructor(passwordFieldId, toggleButtonId) {
        this.passwordField = document.getElementById(passwordFieldId);
        this.toggleButton = document.getElementById(toggleButtonId);
        this.eyeIcon = this.toggleButton ? this.toggleButton.querySelector('i.fas, i.fa') : null;
        
        if (this.passwordField && this.toggleButton && this.eyeIcon) {
            this.init();
        }
    }

    init() {
        // Event listener pour le clic sur le bouton
        this.toggleButton.addEventListener('click', () => this.toggle());
        
        // Animations au focus/blur
        this.passwordField.addEventListener('focus', () => this.onFocus());
        this.passwordField.addEventListener('blur', () => this.onBlur());
        
        // Initialisation du style
        this.setInitialState();
    }

    toggle() {
        const isPassword = this.passwordField.type === 'password';
        
        // Basculer le type d'input
        this.passwordField.type = isPassword ? 'text' : 'password';
        
        // Changer l'icône Font Awesome
        const iconClass = isPassword ? 'fa-eye-slash' : 'fa-eye';
        const color = isPassword ? '#007acc' : '#666';
        
        // Supprimer l'ancienne classe et ajouter la nouvelle
        this.eyeIcon.classList.remove('fa-eye', 'fa-eye-slash');
        this.eyeIcon.classList.add(iconClass);
        this.eyeIcon.style.color = color;
        
        // Mise à jour du titre
        const title = isPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe';
        this.toggleButton.setAttribute('title', title);
        
        // Animation subtile
        this.toggleButton.style.transform = 'translateY(-50%) scale(1.1)';
        setTimeout(() => {
            this.toggleButton.style.transform = 'translateY(-50%) scale(1)';
        }, 150);
    }

    onFocus() {
        this.toggleButton.style.opacity = '1';
    }

    onBlur() {
        if (this.passwordField.value === '') {
            this.toggleButton.style.opacity = '0.6';
        }
    }

    setInitialState() {
        this.toggleButton.style.transition = 'all 0.3s ease';
        this.toggleButton.style.opacity = '0.6';
    }
}

// Fonction utilitaire pour initialiser rapidement un toggle
function initPasswordToggle(passwordFieldId, toggleButtonId) {
    return new PasswordToggle(passwordFieldId, toggleButtonId);
}

// Auto-initialisation pour les éléments standards
document.addEventListener('DOMContentLoaded', function() {
    // Recherche automatique des champs avec les IDs standards
    const standardConfigs = [
        { field: 'password', button: 'togglePassword' },
        { field: 'current_password', button: 'toggleCurrentPassword' },
        { field: 'new_password', button: 'toggleNewPassword' },
        { field: 'confirm_password', button: 'toggleConfirmPassword' }
    ];

    standardConfigs.forEach(config => {
        if (document.getElementById(config.field) && document.getElementById(config.button)) {
            new PasswordToggle(config.field, config.button);
        }
    });
});

// Export pour utilisation externe
window.PasswordToggle = PasswordToggle;
window.initPasswordToggle = initPasswordToggle;