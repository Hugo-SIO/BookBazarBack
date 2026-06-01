<?php
/**
 * Contrôleur REST – GET /Get/Auteurs.php
 * Retourne la liste de tous les auteurs au format JSON.
 *
 * Géré par React via fetch() dans le composant Auteurs.jsx.
 */

// Origines autorisées (CORS) : localhost en dev, domaine de prod
$allowed_origins = [
    "http://localhost:5173",
    "https://bookbazar.hugoal.fr"
];

// On n'autorise que les origines connues pour sécuriser l'API
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json"); // Réponse en JSON

// Gestion du preflight CORS (requête OPTIONS envoyée par le navigateur)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../../Classes/CAuteurs.php";

// Récupère l'instance unique (Singleton) puis la liste des auteurs
$auteurs = CAuteurs::getInstance()->getAuteurs();

// json_encode appelle jsonSerialize() sur chaque CAuteur
echo json_encode($auteurs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>