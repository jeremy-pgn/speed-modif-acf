/**
 * SPEED MODIF ACF - SCRIPTS PRINCIPAUX
 * Orchestration et fonctionnalités avancées
 */

// Variables globales
let editModal = null;
let currentEditingField = null;

/**
 * Initialisation principale
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialisation de l'application
 */
async function initializeApp() {
    // Vérifier si on est sur la page dashboard
    if (window.location.pathname.includes('dashboard.html')) {
        initializeDashboard();
    }
    
    // Initialisation générale
    setupGlobalEventListeners();
    createEditModal();
}

/**
 * Initialisation spécifique au dashboard
 */
async function initializeDashboard() {
    try {
        // Remplacer les données mockées par les vraies données API
        await loadRealACFData();
        
        // Réinitialiser le dashboard avec les vraies données
        if (typeof switchSection === 'function') {
            switchSection(currentSection || 'identite');
        }
        
    } catch (error) {
        console.error('Erreur initialisation dashboard:', error);
        APIUtils.showToast('Erreur lors du chargement des données', 'danger');
    }
}

/**
 * Charger les vraies données ACF depuis l'API
 */
async function loadRealACFData() {
    try {
        const response = await apiClient.getAllFields();
        
        if (response.success && response.data) {
            // Réorganiser les données par section
            const fieldsBySection = {};
            
            response.data.forEach(field => {
                if (!fieldsBySection[field.section]) {
                    fieldsBySection[field.section] = [];
                }
                
                fieldsBySection[field.section].push({
                    id: field.field_key,
                    title: field.field_label,
                    group: field.field_group,
                    preview: field.field_value || 'Aucune valeur',
                    lastModified: field.last_modified_at,
                    type: field.field_type,
                    dbId: field.id // ID en base pour les updates
                });
            });
            
            // Remplacer les données globales
            if (typeof acfData !== 'undefined') {
                Object.keys(fieldsBySection).forEach(section => {
                    acfData[section] = fieldsBySection[section];
                });
                
                // Recréer la liste complète pour la recherche
                if (typeof allFields !== 'undefined') {
                    allFields = [];
                    Object.keys(acfData).forEach(section => {
                        if (section !== 'historique') {
                            acfData[section].forEach(field => {
                                allFields.push({ ...field, section });
                            });
                        }
                    });
                }
            }
            
            console.log('Données ACF chargées:', fieldsBySection);
        }
        
    } catch (error) {
        console.error('Erreur chargement données ACF:', error);
        throw error;
    }
}

/**
 * Configuration des événements globaux
 */
function setupGlobalEventListeners() {
    // Gestionnaire global pour les boutons "Modifier"
    document.addEventListener('click', function(e) {
        if (e.target.matches('.btn-edit') || e.target.closest('.btn-edit')) {
            e.preventDefault();
            const btn = e.target.matches('.btn-edit') ? e.target : e.target.closest('.btn-edit');
            const fieldId = btn.dataset.fieldId;
            
            if (fieldId) {
                openEditModal(fieldId);
            }
        }
    });
    
    // Gestionnaire pour les raccourcis clavier
    document.addEventListener('keydown', function(e) {
        // Echap pour fermer les modales
        if (e.key === 'Escape' && editModal && editModal.classList.contains('show')) {
            closeEditModal();
        }
        
        // Ctrl+S pour sauvegarder (dans la modale)
        if (e.ctrlKey && e.key === 's' && currentEditingField) {
            e.preventDefault();
            saveFieldChanges();
        }
    });
}

/**
 * Créer la modale d'édition
 */
function createEditModal() {
    const modalHTML = `
        <div class="modal fade" id="editModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier le champ</h5>
                        <button type="button" class="btn-close" onclick="closeEditModal()"></button>
                    </div>
                    <div class="modal-body">
                        <div id="editModalContent">
                            <!-- Contenu dynamique -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Annuler</button>
                        <button type="button" class="btn btn-primary" onclick="saveFieldChanges()">
                            <span class="btn-text">Sauvegarder</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    editModal = document.getElementById('editModal');
}

/**
 * Ouvrir la modale d'édition
 */
async function openEditModal(fieldId) {
    try {
        // Trouver le champ dans les données
        let field = null;
        
        if (typeof allFields !== 'undefined') {
            field = allFields.find(f => f.id === fieldId);
        }
        
        if (!field) {
            APIUtils.showToast('Champ non trouvé', 'danger');
            return;
        }
        
        currentEditingField = field;
        
        // Générer le contenu de la modale
        const modalContent = `
            <div class="mb-3">
                <label class="form-label fw-bold">Nom du champ</label>
                <p class="form-control-plaintext">${field.title}</p>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Groupe</label>
                <p class="form-control-plaintext text-muted">${field.group}</p>
            </div>
            <div class="mb-3">
                <label for="fieldValue" class="form-label fw-bold">Valeur actuelle</label>
                ${generateFieldInput(field)}
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Dernière modification</label>
                <p class="form-control-plaintext text-muted">${APIUtils.formatDate(field.lastModified)}</p>
            </div>
        `;
        
        document.getElementById('editModalContent').innerHTML = modalContent;
        
        // Afficher la modale (Bootstrap 5)
        const modal = new bootstrap.Modal(editModal);
        modal.show();
        
        // Focus sur le champ d'édition
        setTimeout(() => {
            const input = editModal.querySelector('#fieldValue');
            if (input) input.focus();
        }, 300);
        
    } catch (error) {
        console.error('Erreur ouverture modale:', error);
        APIUtils.showToast('Erreur lors de l\'ouverture', 'danger');
    }
}

/**
 * Générer l'input approprié selon le type de champ
 */
function generateFieldInput(field) {
    const value = field.preview || '';
    
    switch (field.type) {
        case 'textarea':
            return `<textarea id="fieldValue" class="form-control" rows="4">${value}</textarea>`;
        case 'email':
            return `<input type="email" id="fieldValue" class="form-control" value="${value}">`;
        case 'url':
            return `<input type="url" id="fieldValue" class="form-control" value="${value}">`;
        case 'number':
            return `<input type="number" id="fieldValue" class="form-control" value="${value}">`;
        case 'image':
            return `
                <input type="text" id="fieldValue" class="form-control" value="${value}" placeholder="URL de l'image">
                <div class="form-text">Entrez l'URL de l'image ou le nom du fichier</div>
            `;
        default: // text
            return `<input type="text" id="fieldValue" class="form-control" value="${value}">`;
    }
}

/**
 * Sauvegarder les modifications
 */
async function saveFieldChanges() {
    if (!currentEditingField) return;
    
    const saveBtn = editModal.querySelector('.btn-primary');
    const fieldValueInput = editModal.querySelector('#fieldValue');
    
    if (!fieldValueInput) {
        APIUtils.showToast('Erreur: champ de saisie non trouvé', 'danger');
        return;
    }
    
    const newValue = fieldValueInput.value.trim();
    
    try {
        APIUtils.showLoading(saveBtn, true);
        
        // Appel API pour la mise à jour
        const response = await apiClient.updateField(currentEditingField.dbId || currentEditingField.id, newValue);
        
        if (response.success) {
            // Mettre à jour les données locales
            currentEditingField.preview = newValue;
            currentEditingField.lastModified = new Date().toISOString();
            
            // Refermer la modale
            closeEditModal();
            
            // Rafraîchir l'affichage
            if (typeof renderSection === 'function' && typeof currentSection !== 'undefined') {
                renderSection(currentSection);
            }
            
            APIUtils.showToast('Champ mis à jour avec succès', 'success');
        } else {
            throw new Error(response.message || 'Erreur de sauvegarde');
        }
        
    } catch (error) {
        console.error('Erreur sauvegarde:', error);
        APIUtils.showToast(error.message || 'Erreur lors de la sauvegarde', 'danger');
    } finally {
        APIUtils.showLoading(saveBtn, false);
    }
}

/**
 * Fermer la modale d'édition
 */
function closeEditModal() {
    if (editModal) {
        const modal = bootstrap.Modal.getInstance(editModal);
        if (modal) {
            modal.hide();
        }
    }
    currentEditingField = null;
}

/**
 * Remplacement de la fonction handleEdit du dashboard.js
 */
if (typeof window !== 'undefined') {
    window.handleEdit = function(fieldId) {
        openEditModal(fieldId);
    };
}

/**
 * Utilitaire de debug
 */
if (typeof DEBUG_MODE !== 'undefined' && DEBUG_MODE || window.location.hostname === 'localhost') {
    window.SMADebug = {
        apiClient,
        acfData: () => typeof acfData !== 'undefined' ? acfData : null,
        currentField: () => currentEditingField,
        reloadData: loadRealACFData
    };
    
    console.log('🚀 Speed Modif ACF - Mode Debug activé');
    console.log('Utilisez SMADebug.* pour débugger');
}
