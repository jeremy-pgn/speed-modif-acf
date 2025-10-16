/**
 * js/app.js
 * Application SMA - Speed Modif ACF
 * Interface principale pour la gestion des champs ACF avec synchronisation WordPress
 * Respecte 100% le design et la structure originale
 */

// ========================================
// VARIABLES GLOBALES
// ========================================

// Structure de données principale pour stocker les champs ACF organisés par section
let acfData = {}; 

// Section actuellement affichée dans l'interface
let currentSection = 'identite';

// Liste complète de tous les champs pour la fonction de recherche
let allFields = [];

// ========================================
// INITIALISATION DE L'APPLICATION
// ========================================

/**
 * Point d'entrée principal - Se déclenche quand le DOM est complètement chargé
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 App.js chargé');
    
    // Routage basé sur la page actuelle
    if (window.location.pathname.includes('dashboard.html')) {
        initDashboard();
    } else {
        initLogin();
    }
});

// ========================================
// MODULE LOGIN
// ========================================

/**
 * Initialisation de la page de connexion
 * Gère l'authentification et la validation du formulaire
 */
function initLogin() {
    const form = document.getElementById('loginForm');
    if (!form) return;
    
    // Focus automatique sur le champ email pour une meilleure UX
    const emailField = document.getElementById('email');
    if (emailField) emailField.focus();
    
    // Gestionnaire de soumission du formulaire de connexion
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Récupération et validation des données
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        
        if (!email || !password) {
            showMessage('Veuillez remplir tous les champs', 'error');
            return;
        }
        
        // Éléments d'interface pour les états de chargement
        const btn = document.getElementById('loginButton');
        const spinner = document.getElementById('loginSpinner');
        const buttonText = document.getElementById('buttonText');
        
        try {
            // Mise à jour de l'interface pour indiquer le chargement
            btn.disabled = true;
            buttonText.textContent = 'Connexion...';
            spinner.classList.remove('d-none');
            hideError();
            
            // Appel à l'API d'authentification
            const result = await API.login(email, password);
            
            if (result.success) {
                showMessage('Connexion réussie ! Redirection...', 'success');
                // Redirection après délai pour permettre à l'utilisateur de voir le message
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1500);
            } else {
                showMessage(result.message || 'Identifiants incorrects', 'error');
            }
        } catch (error) {
            console.error('Erreur connexion:', error);
            showMessage('Erreur de connexion au serveur', 'error');
        } finally {
            // Restauration de l'état initial du bouton
            btn.disabled = false;
            buttonText.textContent = 'Se connecter';
            spinner.classList.add('d-none');
        }
    });
}

// ========================================
// MODULE DASHBOARD PRINCIPAL
// ========================================

/**
 * Initialisation du dashboard principal
 * Charge les données et configure l'interface
 */
async function initDashboard() {
    // Affichage du nom d'utilisateur (peut être adapté pour récupérer le vrai nom)
    const userNameEl = document.getElementById('userName');
    if (userNameEl) {
        userNameEl.textContent = 'Admin';
    }
    
    // Séquence d'initialisation
    await loadACFData();
    setupEventListeners();
    switchSection('identite');
}

// ========================================
// GESTION DES DONNÉES
// ========================================

/**
 * Chargement des données ACF depuis l'API
 * Organise les données par sections pour l'affichage
 */
async function loadACFData() {
    try {
        const response = await API.getFields();
        if (response.success) {
            // Réinitialisation des structures de données
            acfData = {};
            allFields = [];
            
            // Organisation des champs par sections (exactement comme le code original)
            response.data.forEach(field => {
                const section = field.section || 'autres';
                if (!acfData[section]) acfData[section] = [];
                
                // Formatage des données pour l'affichage
                acfData[section].push({
                    id: field.field_key,
                    title: field.field_label,
                    group: field.field_group,
                    preview: field.field_value || 'Aucune valeur',
                    lastModified: field.last_modified_at || new Date().toISOString(),
                    dbId: field.id // ID de base de données pour les modifications
                });
            });
            
            // Création de la liste globale pour la recherche
            Object.keys(acfData).forEach(section => {
                if (section !== 'historique') {
                    acfData[section].forEach(field => {
                        allFields.push({ ...field, section });
                    });
                }
            });
            
            // Génération de l'historique factice (comme dans le code original)
            generateHistory();
            
            console.log('Données chargées');
        }
    } catch (error) {
        console.error('Erreur chargement:', error);
    }
}

// ========================================
// CONFIGURATION DES ÉVÉNEMENTS
// ========================================

/**
 * Configuration de tous les écouteurs d'événements de l'application
 */
function setupEventListeners() {
    // Navigation entre les sections
    document.querySelectorAll('.section-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const section = e.target.closest('.section-link').dataset.section;
            switchSection(section);
        });
    });

    // Recherche globale en temps réel
    const searchEl = document.getElementById('globalSearch');
    if (searchEl) {
        searchEl.addEventListener('input', handleGlobalSearch);
    }

    // Déconnexion
    const logoutEl = document.getElementById('logoutBtn');
    if (logoutEl) {
        logoutEl.addEventListener('click', handleLogout);
    }
}

// ========================================
// NAVIGATION ET AFFICHAGE
// ========================================

/**
 * Changement de section avec mise à jour de l'interface
 * @param {string} section - La section à afficher
 */
function switchSection(section) {
    // Mise à jour de la navigation active
    document.querySelectorAll('.section-link').forEach(link => {
        link.classList.remove('active');
    });
    const activeLink = document.querySelector(`[data-section="${section}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }

    currentSection = section;

    // Configuration des titres et descriptions (exactement comme le design original)
    const titles = {
        identite: { title: 'Identité Visuelle', desc: 'Gérez les éléments d\'identité visuelle de votre site' },
        header: { title: 'Header', desc: 'Modifiez les éléments du header de votre site' },
        presentation: { title: 'Présentation', desc: 'Gérez la section présentation de votre entreprise' },
        partenaires: { title: 'Partenaires', desc: 'Administrez vos partenaires et leurs informations' },
        services: { title: 'Services', desc: 'Modifiez la présentation de vos services' },
        carrousel: { title: 'Carrousel', desc: 'Gérez les images et contenus du carrousel' },
        contact: { title: 'Contact', desc: 'Modifiez les informations de contact' },
        historique: { title: 'Historique', desc: 'Consultez l\'historique des modifications' }
    };

    // Mise à jour des éléments d'interface
    const titleEl = document.getElementById('sectionTitle');
    const descEl = document.getElementById('sectionDescription');
    
    if (titleEl) titleEl.textContent = titles[section].title;
    if (descEl) descEl.textContent = titles[section].desc;

    // Rendu du contenu approprié
    if (section === 'historique') {
        renderHistory();
    } else {
        renderSection(section);
    }
}

/**
 * Rendu d'une section avec ses champs (.field-card comme design original)
 * @param {string} section - La section à rendre
 */
function renderSection(section) {
    const container = document.getElementById('contentContainer');
    const fields = acfData[section] || [];

    // Affichage si aucun champ
    if (fields.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="text-muted mt-3">Aucun champ dans cette section</h4>
            </div>
        `;
        return;
    }

    // Génération du HTML avec les classes exactes du design original
    container.innerHTML = `
        <div class="row g-3">
            ${fields.map(field => `
                <div class="col-md-6 col-lg-4">
                    <div class="field-card">
                        <div class="field-group">${field.group}</div>
                        <h5 class="field-title">${field.title}</h5>
                        <div class="field-last-modified">
                            <i class="bi bi-clock me-1"></i>
                            Modifié le ${formatDate(field.lastModified)}
                        </div>
                        <div class="field-preview">${field.preview}</div>
                        <button class="btn btn-edit w-100" data-field-id="${field.id}" data-db-id="${field.dbId}">
                            <i class="bi bi-pencil me-1"></i>
                            Modifier
                        </button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

    // Ajout des gestionnaires d'événements pour les boutons de modification
    container.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const fieldId = e.target.closest('.btn-edit').dataset.fieldId;
            const dbId = e.target.closest('.btn-edit').dataset.dbId;
            handleEdit(fieldId, dbId);
        });
    });
}

/**
 * Rendu de l'historique des modifications
 */
function renderHistory() {
    const container = document.getElementById('contentContainer');
    const history = acfData.historique;

    container.innerHTML = `
        <div class="history-list">
            ${history.map(item => `
                <div class="history-item">
                    <div class="history-icon">
                        <i class="bi ${getHistoryIcon(item.action)}"></i>
                    </div>
                    <div class="history-content">
                        <div class="history-action">${item.action} : ${item.field}</div>
                        <div class="history-time">${formatDate(item.timestamp)} par ${item.user}</div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

// ========================================
// RECHERCHE GLOBALE
// ========================================

/**
 * Gestionnaire de recherche globale
 * @param {Event} e - L'événement de saisie
 */
function handleGlobalSearch(e) {
    const searchTerm = e.target.value.toLowerCase().trim();
    
    // Si pas de recherche, retour à la section courante
    if (searchTerm === '') {
        switchSection(currentSection);
        return;
    }

    // Recherche dans tous les champs
    const results = allFields.filter(field => 
        field.title.toLowerCase().includes(searchTerm) ||
        field.group.toLowerCase().includes(searchTerm) ||
        field.preview.toLowerCase().includes(searchTerm)
    );

    renderSearchResults(results, searchTerm);
}

/**
 * Rendu des résultats de recherche avec surlignage
 * @param {Array} results - Les résultats de recherche
 * @param {string} searchTerm - Le terme recherché
 */
function renderSearchResults(results, searchTerm) {
    const container = document.getElementById('contentContainer');
    
    // Mise à jour des titres
    const titleEl = document.getElementById('sectionTitle');
    const descEl = document.getElementById('sectionDescription');
    
    if (titleEl) titleEl.textContent = `Résultats de recherche`;
    if (descEl) descEl.textContent = `${results.length} résultat(s) pour "${searchTerm}"`;

    // Affichage si aucun résultat
    if (results.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-search display-1 text-muted"></i>
                <h4 class="text-muted mt-3">Aucun résultat trouvé</h4>
                <p class="text-muted">Essayez avec d'autres mots-clés</p>
            </div>
        `;
        return;
    }

    // Génération des résultats avec surlignage
    container.innerHTML = `
        <div class="row g-3">
            ${results.map(field => `
                <div class="col-md-6 col-lg-4">
                    <div class="field-card">
                        <div class="field-group">${field.section}</div>
                        <h5 class="field-title">${highlightText(field.title, searchTerm)}</h5>
                        <div class="field-last-modified">
                            <i class="bi bi-clock me-1"></i>
                            Modifié le ${formatDate(field.lastModified)}
                        </div>
                        <div class="field-preview">${highlightText(field.preview, searchTerm)}</div>
                        <button class="btn btn-edit w-100" data-field-id="${field.id}" data-db-id="${field.dbId}">
                            <i class="bi bi-pencil me-1"></i>
                            Modifier
                        </button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

    // Ajout des gestionnaires d'événements
    container.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const fieldId = e.target.closest('.btn-edit').dataset.fieldId;
            const dbId = e.target.closest('.btn-edit').dataset.dbId;
            handleEdit(fieldId, dbId);
        });
    });
}

// ========================================
// GESTIONNAIRES D'ÉVÉNEMENTS
// ========================================

/**
 * Gestionnaire de modification de champ avec API
 * @param {string} fieldId - ID du champ
 * @param {number} dbId - ID en base de données
 */
async function handleEdit(fieldId, dbId) {
    const field = allFields.find(f => f.id === fieldId);
    if (!field) return;
    
    // Interface de modification simple (peut être remplacée par un modal)
    const newValue = prompt(`Modifier "${field.title}":`, field.preview);
    
    if (newValue !== null && newValue !== field.preview) {
        try {
            // Appel à l'API de mise à jour
            const result = await API.updateField(dbId, newValue);
            
            if (result.success) {
                // Mise à jour locale immédiate
                field.preview = newValue;
                field.lastModified = new Date().toISOString();
                
                // Rafraîchissement de l'affichage
                switchSection(currentSection);
                
                alert('Champ mis à jour et synchronisé !');
            } else {
                alert('Erreur : ' + result.message);
            }
        } catch (error) {
            alert('Erreur lors de la mise à jour');
        }
    }
}

/**
 * Gestionnaire de déconnexion
 */
function handleLogout() {
    if (confirm('Voulez-vous vraiment vous déconnecter ?')) {
        window.location.href = 'index.html';
    }
}

// ========================================
// FONCTIONS UTILITAIRES
// ========================================

/**
 * Formatage des dates pour l'affichage
 * @param {string} dateString - Date au format ISO
 * @returns {string} Date formatée
 */
function formatDate(dateString) {
    if (!dateString) return 'Jamais';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Surlignage du texte de recherche
 * @param {string} text - Texte à traiter
 * @param {string} searchTerm - Terme à surligner
 * @returns {string} Texte avec surlignage HTML
 */
function highlightText(text, searchTerm) {
    const regex = new RegExp(`(${searchTerm})`, 'gi');
    return text.replace(regex, '<span class="search-highlight">$1</span>');
}

/**
 * Obtention de l'icône pour l'historique
 * @param {string} action - Type d'action
 * @returns {string} Classe CSS de l'icône
 */
function getHistoryIcon(action) {
    const icons = {
        'Modification': 'bi-pencil',
        'Création': 'bi-plus-circle',
        'Suppression': 'bi-trash'
    };
    return icons[action] || 'bi-clock';
}

// ========================================
// GESTION DES MESSAGES ET ERREURS
// ========================================

/**
 * Affichage des messages à l'utilisateur
 * @param {string} message - Le message à afficher
 * @param {string} type - Type de message ('info', 'success', 'error')
 */
function showMessage(message, type = 'info') {
    const errorMessage = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');
    
    if (errorMessage && errorText) {
        if (type === 'success') {
            errorText.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + message;
            errorMessage.className = 'alert alert-success';
        } else {
            errorText.textContent = message;
            errorMessage.className = 'alert alert-danger';
        }
        errorMessage.classList.remove('d-none');
    }
}

/**
 * Masquage des messages d'erreur
 */
function hideError() {
    const errorMessage = document.getElementById('errorMessage');
    if (errorMessage) {
        errorMessage.classList.add('d-none');
    }
}

// ========================================
// CONFIRMATION DE CHARGEMENT
// ========================================

console.log('App.js chargé');
