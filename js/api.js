/**
 * SPEED MODIF ACF - API CLIENT
 * Gestion des appels API côté frontend
 */

// Configuration API
const API_CONFIG = {
    baseURL: window.location.origin,
    endpoints: {
        auth: '/api/auth.php',
        fields: '/api/fields.php'
    },
    timeout: 10000
};

/**
 * Classe principale API Client
 */
class APIClient {
    constructor() {
        this.token = localStorage.getItem('authToken');
        this.baseURL = API_CONFIG.baseURL;
    }

    /**
     * Méthode générique pour les appels HTTP
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        // Ajouter le token JWT si disponible
        if (this.token && !endpoint.includes('/auth.php')) {
            config.headers['Authorization'] = `Bearer ${this.token}`;
        }

        try {
            const response = await fetch(url, config);
            
            // Gestion des erreurs HTTP
            if (!response.ok) {
                if (response.status === 401) {
                    this.handleUnauthorized();
                    throw new Error('Session expirée');
                }
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            // Vérifier le format de réponse API
            if (data.success === false) {
                throw new Error(data.message || 'Erreur API');
            }

            return data;
        } catch (error) {
            console.error('Erreur API:', error);
            throw error;
        }
    }

    /**
     * Gestion session expirée
     */
    handleUnauthorized() {
        localStorage.removeItem('authToken');
        localStorage.removeItem('user');
        if (window.location.pathname !== '/index.html') {
            window.location.href = 'index.html';
        }
    }

    /**
     * Authentification
     */
    async login(email, password) {
        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);

        return this.request(API_CONFIG.endpoints.auth, {
            method: 'POST',
            headers: {}, // Pas de Content-Type pour FormData
            body: formData
        });
    }

    /**
     * Récupérer les champs par section
     */
    async getFieldsBySection(section) {
        const endpoint = `${API_CONFIG.endpoints.fields}?section=${encodeURIComponent(section)}`;
        return this.request(endpoint);
    }

    /**
     * Récupérer tous les champs
     */
    async getAllFields() {
        return this.request(API_CONFIG.endpoints.fields);
    }

    /**
     * Rechercher dans les champs
     */
    async searchFields(query) {
        const endpoint = `${API_CONFIG.endpoints.fields}?search=${encodeURIComponent(query)}`;
        return this.request(endpoint);
    }

    /**
     * Mettre à jour un champ
     */
    async updateField(fieldId, newValue) {
        return this.request(API_CONFIG.endpoints.fields, {
            method: 'PUT',
            body: JSON.stringify({
                field_id: fieldId,
                field_value: newValue
            })
        });
    }

    /**
     * Récupérer l'historique
     */
    async getHistory(limit = 20) {
        const endpoint = `${API_CONFIG.endpoints.fields}?history=1&limit=${limit}`;
        return this.request(endpoint);
    }
}

// Instance globale
const apiClient = new APIClient();

/**
 * Utilitaires pour l'UI
 */
const APIUtils = {
    /**
     * Afficher un toast de notification
     */
    showToast(message, type = 'info') {
        // Réutiliser la fonction showToast de dashboard.js si elle existe
        if (typeof showToast === 'function') {
            showToast(message, type);
            return;
        }

        // Fallback simple
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
        toast.style.zIndex = '9999';
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    },

    /**
     * Afficher un état de chargement
     */
    showLoading(element, loading = true) {
        if (loading) {
            element.disabled = true;
            element.classList.add('loading');
            if (element.querySelector('.spinner-border') === null) {
                const spinner = document.createElement('span');
                spinner.className = 'spinner-border spinner-border-sm me-2';
                element.prepend(spinner);
            }
        } else {
            element.disabled = false;
            element.classList.remove('loading');
            const spinner = element.querySelector('.spinner-border');
            if (spinner) spinner.remove();
        }
    },

    /**
     * Formater une date
     */
    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    /**
     * Debounce pour les recherches
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

/**
 * Gestion des erreurs globales
 */
window.addEventListener('unhandledrejection', function(event) {
    console.error('Erreur non gérée:', event.reason);
    APIUtils.showToast('Une erreur inattendue s\'est produite', 'danger');
});

// Export pour utilisation dans d'autres fichiers
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { APIClient, apiClient, APIUtils };
}
