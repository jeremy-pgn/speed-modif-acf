<?php
/**
 * api/fields.php
 * API REST pour la gestion des champs ACF
 * Prend en charge la récupération (GET) et la modification (PUT) des champs
 * avec synchronisation automatique vers WordPress
 */

// 1) Toujours envoyer l'entête JSON en premier
header('Content-Type: application/json; charset=utf-8');

// 2) Charger la config qui démarre la session UNE SEULE FOIS
require_once __DIR__ . '/config.php';

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
    return (int)$_SESSION['user_id'];
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
            $wp_pdo = new PDO(
                "mysql:host=" . WP_HOST . ";dbname=" . WP_DATABASE . ";charset=utf8mb4",
                WP_USER,
                WP_PASSWORD
            );
            $wp_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        // Vérification de l'existence du champ dans WordPress
        $stmt = $wp_pdo->prepare("SELECT meta_id, meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = ?");
        $stmt->execute([WP_POST_ID, $fieldKey]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Mise à jour d'un champ existant
            $stmt = $wp_pdo->prepare("UPDATE wp_postmeta SET meta_value = ? WHERE meta_id = ?");
            $stmt->execute([$newValue, $existing['meta_id']]);
            $action = "updated";
        } else {
            // Création d'un nouveau champ
            $stmt = $wp_pdo->prepare("INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES (?, ?, ?)");
            $stmt->execute([WP_POST_ID, $fieldKey, $newValue]);
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
try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // 3) CSRF: vérifier UNIQUEMENT pour les méthodes d'écriture
    if (in_array($method, ['POST','PUT'], true)) {
        // Lire le header X-CSRF-Token depuis $_SERVER ou via getallheaders()
        $hdr = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$hdr) {
            $all = function_exists('getallheaders') ? getallheaders() : [];
            $hdr = $all['X-CSRF-Token'] ?? '';
        }
        if (!hash_equals($_SESSION['csrf'] ?? '', $hdr)) {
            http_response_code(403);
            echo json_encode(['success'=>false,'message'=>'Invalid CSRF token']);
            exit;
        }
    }

    // 4) Historique (lecture, pas de CSRF)
    if (isset($_GET['history'])) {
        $stmt = $pdo->prepare("
            SELECT h.action, h.timestamp, h.old_value, h.new_value,
                   f.field_label, f.field_name, u.name as user_name
            FROM sma_field_history h
            LEFT JOIN sma_acf_fields f ON h.field_id = f.id  
            LEFT JOIN sma_users u ON h.user_id = u.id
            ORDER BY h.timestamp DESC LIMIT 20
        ");
        $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($method === 'GET') {
        // 5) RÉCUPÉRATION DES CHAMPS (lecture, pas de CSRF)
        $stmt = $pdo->prepare("
            SELECT f.*, u.name as last_modified_by_name
            FROM sma_acf_fields f
            LEFT JOIN sma_users u ON f.last_modified_by = u.id
            WHERE f.is_active = 1
            ORDER BY f.section, f.field_group, f.field_name
        ");
        $stmt->execute();
        $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $fields,
            'count' => count($fields)
        ]);
        exit;
    }

    if ($method === 'PUT') {
        // 6) MODIFICATION D'UN CHAMP (PUT)
        // Auth obligatoire
        $user_id = checkAuth();

        // Lire le JSON du corps (Content-Type: application/json côté client)
        $raw   = file_get_contents('php://input');
        $input = json_decode($raw, true);
        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            exit;
        }

        if (!isset($input['field_id']) || !isset($input['field_value'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'field_id et field_value requis']);
            exit;
        }

        $fieldId  = (int)$input['field_id'];
        $newValue = $input['field_value'];

        // Récupération du champ actuel
        $stmt = $pdo->prepare("SELECT field_key, field_value FROM sma_acf_fields WHERE id = ?");
        $stmt->execute([$fieldId]);
        $field = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$field) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Champ non trouvé']);
            exit;
        }

        // Transaction update + historique
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE sma_acf_fields
            SET field_value = ?, last_modified_by = ?, last_modified_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$newValue, $user_id, $fieldId]);

        $stmt = $pdo->prepare("
            INSERT INTO sma_field_history (field_id, user_id, action, old_value, new_value, ip_address)
            VALUES (?, ?, 'update', ?, ?, ?)
        ");
        $stmt->execute([$fieldId, $user_id, $field['field_value'], $newValue, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

        $pdo->commit();

        // Synchronisation WordPress
        $syncResult = syncToWordPress($field['field_key'], $newValue);

        echo json_encode([
            'success' => true,
            'message' => 'Champ mis à jour et synchronisé',
            'sma_update' => [
                'field_id'  => $fieldId,
                'field_key' => $field['field_key'],
                'old_value' => $field['field_value'],
                'new_value' => $newValue
            ],
            'wordpress_sync' => $syncResult
        ]);
        exit;
    }

    // Méthode non supportée
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('fields error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur interne']);
}
