<?php
/**
 * Contrôleur REST – GET /Get/ImageAnnonce.php?idLivre=3
 * Retourne l'image d'un livre directement en binaire
 * (pas en JSON comme les autres routes).
 * Le frontend React utilise cette URL directement dans un <img src="...">.
 * Route publique : aucun JWT requis.
 */

// Origines autorisées (CORS — simplifié car pas de body JSON)
$allowedOrigins = [
    "http://localhost:5173",
    "https://bookbazar.hugoal.fr"
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

require_once "../../Classes/CLivres.php";

// Récupération de l'id livre depuis l'URL (?idLivre=3)
$idLivre = $_GET['idLivre'] ?? null;

if (!$idLivre) {
    http_response_code(400);
    exit;
}

// Récupère les données binaires de l'image stockée en BDD (BLOB)
$imageData = CLivres::getInstance()->getImageAnnonce((int) $idLivre);

if (!$imageData || empty($imageData['image'])) {
    http_response_code(404);
    exit;
}

// Détection automatique du type MIME (jpeg, png, webp...)
// à partir des données binaires elles-mêmes (pas de l'extension)
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer($imageData['image']);

// On envoie l'image brute avec les bons headers
// Content-Type : indique au navigateur comment interpréter les données
// Content-Length : taille en octets de l'image
// Cache-Control : le navigateur peut mettre en cache 24h (86400s)
header("Content-Type: $mimeType");
header("Content-Length: " . strlen($imageData['image']));
header("Cache-Control: public, max-age=86400");

echo $imageData['image']; // Envoi des données binaires brutes
exit;
?>