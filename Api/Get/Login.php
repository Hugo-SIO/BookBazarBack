<?php
/**
 * Contrôleur REST – POST /Auth/Login.php
 * Authentifie un utilisateur et retourne un token JWT
 * si les identifiants sont corrects.
 *
 * Flux :
 *   1. React envoie { nomUtilisateur, motDePasse } en JSON
 *   2. PHP vérifie le mot de passe avec password_verify() (bcrypt)
 *   3. Si OK → génère un JWT signé avec la clé secrète
 *   4. React stocke le JWT dans localStorage et l'envoie
 *      dans le header Authorization de chaque requête protégée
 */

ob_start(); // Tampon de sortie : évite que des espaces/erreurs
            // parasites ne corrompent le JSON retourné

// Activation des erreurs PHP (à désactiver en production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php'; // Autoloader Composer (firebase/php-jwt)
require_once '../../Classes/CUtilisateurs.php';

use Firebase\JWT\JWT;

/* ==============================
   CONFIGURATION JWT
============================== */

$secret_key       = "9f3c7a8e4b1c2d5f9a0e..."; // Clé secrète de signature HS256
                                                  // ⚠️ En prod, stocker dans un .env
$issuer           = "bookbazar";                  // Émetteur du token (iss)
$audience         = "bookbazar_users";            // Destinataire prévu (aud)
$issued_at        = time();                       // Timestamp de création (iat)
$expiration_time  = $issued_at + (60 * 60);       // Expiration : 1 heure (exp)

/* ==============================
   CORS
============================== */

$allowed_origins = [
    "http://localhost:5173",
    "https://bookbazar.hugoal.fr"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {

    /* ==============================
       RÉCUPÉRATION DES DONNÉES
    ============================== */

    // Lecture du corps JSON : { "nomUtilisateur": "hugo", "motDePasse": "secret" }
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['nomUtilisateur']) || !isset($data['motDePasse'])) {
        throw new Exception("Données manquantes ou JSON invalide");
    }

    // strtolower + trim : normalise le nom pour une comparaison insensible à la casse
    $nomUtilisateur = strtolower(trim($data['nomUtilisateur']));
    $motDePasse     = $data['motDePasse'];

    /* ==============================
       VÉRIFICATION DE L'UTILISATEUR
    ============================== */

    // Récupère tous les utilisateurs depuis le Singleton
    $utilisateurs = CUtilisateurs::getInstance()->getUtilisateur();

    if (!$utilisateurs) {
        throw new Exception("Erreur récupération utilisateurs (BDD ?)");
    }

    foreach ($utilisateurs as $utilisateur) {

        // Comparaison insensible à la casse sur le nom d'utilisateur
        if (strtolower($utilisateur->getNomUtilisateur()) === $nomUtilisateur) {

            /**
             * password_verify() compare le mot de passe en clair
             * avec le hash bcrypt stocké en BDD.
             * On ne stocke JAMAIS le mot de passe en clair → sécurité.
             */
            if (password_verify($motDePasse, $utilisateur->getMotDePasseHash())) {

                /**
                 * Payload JWT : données encodées dans le token.
                 * Accessible côté React après décodage base64
                 * (mais non modifiable sans invalider la signature).
                 */
                $payload = [
                    "iss"  => $issuer,           // Émetteur
                    "aud"  => $audience,          // Destinataire
                    "iat"  => $issued_at,         // Date de création
                    "exp"  => $expiration_time,   // Date d'expiration (1h)
                    "data" => [
                        "id"             => $utilisateur->getIdUtilisateur(),
                        "nomUtilisateur" => $utilisateur->getNomUtilisateur(),
                        "nom"            => $utilisateur->getNom(),
                        "prenom"         => $utilisateur->getPrenom(),
                        "role"           => $utilisateur->getRole()
                    ]
                ];

                // Génère le JWT signé en HS256 avec la clé secrète
                $jwt = JWT::encode($payload, $secret_key, 'HS256');

                ob_clean(); // Vide le tampon avant d'écrire la réponse finale
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "message" => "Connecté avec succès",
                    "token"   => $jwt,  // Token à stocker dans localStorage côté React
                    "user"    => $payload["data"]
                ]);
                exit;
            }

            throw new Exception("Mot de passe incorrect");
        }
    }

    throw new Exception("Utilisateur inconnu");

} catch (Exception $e) {

    ob_clean();
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => $e->getMessage()
    ]);
    exit;
}
?>