/**
 * SPEED MODIF ACF - API CLIENT SIMPLE
 * Version sans déconnexion automatique
 */

// Configuration API
const API_CONFIG = {
  baseURL: window.location.origin,
  endpoints: {
    auth: "/api/auth.php",
    fields: "/api/fields.php",
  },
};

/**
 * Classe API Client simplifiée
 */
class APIClient {
  constructor() {
    this.token = localStorage.getItem("authToken");
    this.baseURL = API_CONFIG.baseURL;
  }

  /**
   * Méthode générique pour les appels HTTP
   */
  async request(endpoint, options = {}) {
    const url = `${this.baseURL}${endpoint}`;
    const config = {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        ...options.headers,
      },
      ...options,
    };

    // Ajouter le token JWT si disponible
    if (this.token && !endpoint.includes("/auth.php")) {
      config.headers["Authorization"] = `Bearer ${this.token}`;
    }

    try {
      const response = await fetch(url, config);
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Erreur API:", error);
      throw error;
    }
  }

  /**
   * Authentification
   */
  async login(email, password) {
    const formData = new FormData();
    formData.append("email", email);
    formData.append("password", password);

    return this.request(API_CONFIG.endpoints.auth, {
      method: "POST",
      headers: {}, // Pas de Content-Type pour FormData
      body: formData,
    });
  }

  /**
   * Récupérer tous les champs
   */
  async getAllFields() {
    return this.request(API_CONFIG.endpoints.fields);
  }

  /**
   * Mettre à jour un champ
   */
  async updateField(fieldId, newValue) {
    return this.request(API_CONFIG.endpoints.fields, {
      method: "PUT",
      body: JSON.stringify({
        field_id: fieldId,
        field_value: newValue,
      }),
    });
  }
}

// Instance globale
const apiClient = new APIClient();

/**
 * Utilitaires pour l'UI
 */
const APIUtils = {
  showToast(message, type = "info") {
    console.log(`Toast ${type}: ${message}`);
    alert(message);
  },

  formatDate(dateString) {
    return new Date(dateString).toLocaleDateString("fr-FR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  },


  showLoading(element, loading = true) {
    if (loading) {
      element.disabled = true;
      element.textContent = "Sauvegarde...";
      element.classList.add("loading");
    } else {
      element.disabled = false;
      element.textContent = "Sauvegarder";
      element.classList.remove("loading");
    }
  },
};
