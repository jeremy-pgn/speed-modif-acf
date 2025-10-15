/**
 * Module API - Client JavaScript pour l'application Speed Modif ACF
 * Fournit une interface pour l'authentification et la gestion des champs ACF
 * Utilise l'API Fetch pour les communications avec le serveur
 */

const API = {
    // ========================================
    // AUTHENTIFICATION
    // ========================================
    
    /**
     * Authentifie un utilisateur avec ses identifiants
     * @param {string} email - L'adresse email de l'utilisateur
     * @param {string} password - Le mot de passe de l'utilisateur
     * @returns {Promise<Object>} Réponse JSON du serveur avec le statut d'authentification
     */
    async login(email, password) {
        // Préparation des données de formulaire pour l'authentification
        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);
        
        // Envoi de la requête d'authentification via POST
        const response = await fetch('api/auth.php', {
            method: 'POST',
            body: formData
        });
        
        return await response.json();
    },
    
    // ========================================
    // RÉCUPÉRATION DES DONNÉES
    // ========================================
    
    /**
     * Récupère tous les champs ACF depuis le serveur
     * @returns {Promise<Object>} Réponse JSON contenant la liste des champs et leur statut
     */
    async getFields() {
        // Requête GET simple pour récupérer tous les champs
        const response = await fetch('api/fields.php');
        return await response.json();
    },
    
    // ========================================
    // MODIFICATION DES DONNÉES
    // ========================================
    
    /**
     * Met à jour la valeur d'un champ ACF spécifique
     * Utilise la méthode PUT validée qui fonctionne avec la synchronisation WordPress
     * @param {number} fieldId - L'ID unique du champ à modifier
     * @param {string} value - La nouvelle valeur à assigner au champ
     * @returns {Promise<Object>} Réponse JSON avec le résultat de la mise à jour et de la synchronisation
     */
    async updateField(fieldId, value) {
        // Envoi de la requête PUT avec les données JSON
        const response = await fetch('api/fields.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                field_id: fieldId,
                field_value: value
            })
        });
        
        return await response.json();
    }
};

// ========================================
// INITIALISATION
// ========================================

// Indication que le module API est correctement chargé
console.log('🔌 API.js chargé');
