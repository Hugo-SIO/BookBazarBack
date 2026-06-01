<?php
ob_start();

// 🔥 DEBUG (à enlever en prod)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';
require_once '../../Classes/CUtilisateurs.php';

use Firebase\JWT\JWT;

/* ==============================
   CONFIGURATION JWT
============================== */

$secret_key = "9f3c7a8e4b1c2d5f9a0e6b7c8d4f2a1e9c3b5f7d8e6a1c2b4f9e0d7c8a3b6e1f2";
$issuer = "bookbazar";
$audience = "bookbazar_users";
$issued_at = time();
$expiration_time = $issued_at + (60 * 60); // 1 heure

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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {

    /* ==============================
       RÉCUPÉRATION DONNÉES
    ============================== */

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['nomUtilisateur']) || !isset($data['motDePasse'])) {
        throw new Exception("Données manquantes ou JSON invalide");
    }

    $nomUtilisateur = strtolower(trim($data['nomUtilisateur']));
    $motDePasse = $data['motDePasse'];

    /* ==============================
       VÉRIFICATION UTILISATEUR
    ============================== */

    $utilisateurs = CUtilisateurs::getInstance()->getUtilisateur();

    if (!$utilisateurs) {
        throw new Exception("Erreur récupération utilisateurs (BDD ?)");
    }

    foreach ($utilisateurs as $utilisateur) {

        if (strtolower($utilisateur->getNomUtilisateur()) === $nomUtilisateur) {

            if (password_verify($motDePasse, $utilisateur->getMotDePasseHash())) {

                // Payload JWT
                $payload = [
                    "iss" => $issuer,
                    "aud" => $audience,
                    "iat" => $issued_at,
                    "exp" => $expiration_time,
                    "data" => [
                        "id" => $utilisateur->getIdUtilisateur(),
                        "nomUtilisateur" => $utilisateur->getNomUtilisateur(),
                        "nom" => $utilisateur->getNom(),
                        "prenom" => $utilisateur->getPrenom(),
                        "role" => $utilisateur->getRole()
                    ]
                ];

                $jwt = JWT::encode($payload, $secret_key, 'HS256');

                ob_clean();
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "message" => "Connecté avec succès",
                    "token" => $jwt,
                    "user" => $payload["data"]
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
        "error" => $e->getMessage()
    ]);
    exit;
}

