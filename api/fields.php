<?php
/**
 * API REST pour la gestion des champs ACF
 * Prend en charge la récupération (GET) et la modification (PUT) des champs
 * avec synchronisation automatique vers WordPress
 */

// Inclusion du fichier de configuration et démarrage de session
require_once 'config.php';
session_start();

// Définition de l'en-tête JSON pour toutes les réponses
header('Content-Type: application/json');

// ========================================
// FONCTION D'AUTHENTIFICATION
// ========================================

/**
 * Vérifie si l'utilisateur est authentifié via la session
 * @return int L'ID de l'utilisateur connecté
 * @throws exit Termine le script avec une erreur 401 si non authentifié
 */
function checkAuth() {
    if (!($_SESSION['logged'] ?? false)) {
        http_response_code(401);
        echo json_encode(['error' => 'Non autorisé']);
        exit;
    }
    return $_SESSION['user_id'];
}

// ========================================
// FONCTION DE SYNCHRONISATION WORDPRESS
// ========================================

/**
 * Synchronise un champ vers WordPress en utilisant la même logique validée
 * @param string $fieldKey La clé du champ à synchroniser
 * @param string $newValue La nouvelle valeur à enregistrer
 * @return array Résultat de la synchronisation avec statut et informations
 */
function syncToWordPress($fieldKey, $newValue) {
    try {
        global $wp_pdo;
        
        // Initialisation de la connexion WordPress si nécessaire
        if (!isset($wp_pdo)) {
            $wp_pdo = new PDO("mysql:host=" . WP_HOST . ";dbname=" . WP_DATABASE . ";charset=utf8mb4", WP_USER, WP_PASSWORD);
            $wp_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        
        // Vérification de l'existence du champ dans WordPress
        $stmt = $wp_pdo->prepare("SELECT meta_id, meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = ?");
        $stmt->execute([WP_POST_ID, $fieldKey]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Mise à jour d'un champ existant
            $stmt = $wp_pdo->prepare("UPDATE wp_postmeta SET meta_value = ? WHERE meta_id = ?");
            $result = $stmt->execute([$newValue, $existing['meta_id']]);
            $action = "updated";
        } else {
            // Création d'un nouveau champ
            $stmt = $wp_pdo->prepare("INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES (?, ?, ?)");
            $result = $stmt->execute([WP_POST_ID, $fieldKey, $newValue]);
            $action = "created";
        }
        
        return [
            'success' => true,
            'action' => $action,
            'field_key' => $fieldKey,
            'post_id' => WP_POST_ID
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// ========================================
// ROUTAGE PRINCIPAL
// ========================================

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // ========================================
        // RÉCUPÉRATION DES CHAMPS (GET)
        // ========================================
        
        // Récupération de tous les champs actifs avec informations utilisateur
        $stmt = $pdo->prepare("
            SELECT f.*, u.name as last_modified_by_name
            FROM sma_acf_fields f
            LEFT JOIN sma_users u ON f.last_modified_by = u.id
            WHERE f.is_active = 1
            ORDER BY f.section, f.field_group, f.field_name
        ");
        $stmt->execute();
        $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Retour des données au format JSON
        echo json_encode([
            'success' => true,
            'data' => $fields,
            'count' => count($fields)
        ]);
        
    } elseif ($method === 'PUT') {
        // ========================================
        // MODIFICATION D'UN CHAMP (PUT)
        // ========================================
        
        // Vérification de l'authentification pour les modifications
        $user_id = checkAuth();
        
        // Récupération et validation des données JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['field_id']) || !isset($input['field_value'])) {
            throw new Exception('field_id et field_value requis');
        }
        
        $fieldId = (int)$input['field_id'];
        $newValue = $input['field_value'];
        
        // ========================================
        // TRANSACTION DE MISE À JOUR
        // ========================================
        
        $pdo->beginTransaction();
        
        // Récupération des informations actuelles du champ
        $stmt = $pdo->prepare("SELECT field_key, field_value FROM sma_acf_fields WHERE id = ?");
        $stmt->execute([$fieldId]);
        $field = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$field) {
            throw new Exception('Champ non trouvé');
        }
        
        // Mise à jour dans la base SMA
        $stmt = $pdo->prepare("UPDATE sma_acf_fields SET field_value = ?, last_modified_by = ?, last_modified_at = NOW() WHERE id = ?");
        $stmt->execute([$newValue, $user_id, $fieldId]);
        
        // Enregistrement dans l'historique des modifications
        $stmt = $pdo->prepare("INSERT INTO sma_field_history (field_id, user_id, action, old_value, new_value, ip_address) VALUES (?, ?, 'update', ?, ?, ?)");
        $stmt->execute([$fieldId, $user_id, $field['field_value'], $newValue, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
        
        // Validation de la transaction
        $pdo->commit();
        
        // ========================================
        // SYNCHRONISATION WORDPRESS
        // ========================================
        
        // Synchronisation avec WordPress en utilisant la logique validée
        $syncResult = syncToWordPress($field['field_key'], $newValue);
        
        // Retour du résultat complet
        echo json_encode([
            'success' => true,
            'message' => 'Champ mis à jour et synchronisé',
            'sma_update' => [
                'field_id' => $fieldId,
                'field_key' => $field['field_key'],
                'old_value' => $field['field_value'],
                'new_value' => $newValue
            ],
            'wordpress_sync' => $syncResult
        ]);
        
    } else {
        // Méthode HTTP non supportée
        throw new Exception('Méthode non autorisée');
    }
    
} catch (Exception $e) {
    // ========================================
    // GESTION DES ERREURS
    // ========================================
    
    // Annulation de la transaction en cas d'erreur
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Retour d'erreur avec code HTTP approprié
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
