<?php
$allowedOrigins = [
    "http://localhost:5173",
    "https://site.bookbazar.local"
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

require_once "../../Classes/CLivres.php";

$idLivre = $_GET['idLivre'] ?? null;

if (!$idLivre) {
    http_response_code(400);
    exit;
}

$imageData = CLivres::getInstance()->getImageAnnonce((int)$idLivre);

if (!$imageData || empty($imageData['image'])) {
    http_response_code(404);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer($imageData['image']);

header("Content-Type: $mimeType");
header("Content-Length: " . strlen($imageData['image']));
header("Cache-Control: public, max-age=86400");

echo $imageData['image'];
exit;
