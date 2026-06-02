<?php
/**
 * auth.php
 *
 * Fichier d'authentification JWT.
 * Permet de vérifier qu'un utilisateur possède un token valide
 * avant d'accéder aux routes protégées de l'API.
 */

require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Vérifie l'authentification de l'utilisateur.
 *
 * Le token JWT doit être transmis dans l'en-tête HTTP :
 * Authorization: Bearer <token>
 *
 * Si le token est valide :
 * - les informations de l'utilisateur sont retournées.
 *
 * Si le token est absent ou invalide :
 * - une erreur HTTP 401 est renvoyée,
 * - l'exécution du script est interrompue.
 */
function getAuthUser() {

    /**
     * Clé secrète utilisée pour signer et vérifier les JWT.
     * Cette clé doit être identique à celle utilisée lors
     * de la génération du token pendant la connexion.
     */
    $secret_key = "9f3c7a8e4b1c2d5f9a0e6b7c8d4f2a1e9c3b5f7d8e6a1c2b4f9e0d7c8a3b6e1f2";

    /**
     * Récupération de tous les en-têtes HTTP de la requête.
     */
    $headers = getallheaders();

    /**
     * Lecture de l'en-tête Authorization.
     * Si celui-ci n'existe pas, une chaîne vide est utilisée.
     */
    $authHeader = $headers['Authorization'] ?? '';

    /**
     * Vérifie que l'en-tête commence bien par "Bearer ".
     * Format attendu :
     * Authorization: Bearer <token>
     *
     * Si le format est incorrect ou absent,
     * l'accès est refusé.
     */
    if (!str_starts_with($authHeader, 'Bearer ')) {
        http_response_code(401);
        echo json_encode([
            "error" => "Token manquant"
        ]);
        exit;
    }

    /**
     * Extraction du JWT.
     * On retire les 7 premiers caractères correspondant à "Bearer ".
     */
    $token = substr($authHeader, 7);

    try {

        /**
         * Vérification et décodage du token JWT.
         *
         * La bibliothèque Firebase JWT :
         * - vérifie la signature du token,
         * - contrôle sa date d'expiration,
         * - récupère les données qu'il contient.
         */
        $decoded = JWT::decode(
            $token,
            new Key($secret_key, 'HS256')
        );

        /**
         * Retourne les informations de l'utilisateur
         * stockées dans la propriété "data" du JWT.
         *
         * Conversion en tableau PHP pour faciliter
         * l'exploitation des données.
         */
        return (array) $decoded->data;

    } catch (Exception $e) {

        /**
         * Une exception est déclenchée si :
         * - le token est expiré,
         * - la signature est invalide,
         * - le token a été modifié,
         * - le format est incorrect.
         *
         * Dans ce cas, l'accès est refusé.
         */
        http_response_code(401);
        echo json_encode([
            "error" => "Token invalide ou expiré"
        ]);
        exit;
    }
}