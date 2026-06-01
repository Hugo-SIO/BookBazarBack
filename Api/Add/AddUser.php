<?php
/**
 * Contrôleur REST – POST /Add/AddUser.php
 * Crée un nouvel utilisateur après vérification de doublon.
 * Route publique : pas de JWT requis (inscription libre).
 * Ouvre aussi une session PHP après la création.
 */

session_start(); // Démarre la session pour pouvoir stocker $_SESSION['user']

// Origines autorisées (CORS)
$allowed_origins = [
    "http://localhost:5173",
    "https://bookbazar.hugoal.fr"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../Classes/CUtilisateurs.php';

/**
 * Lecture du corps JSON envoyé par React :
 * { nom, prenom, adresseMail, nomUtilisateur, motDePasse, idRole }
 * php://input est nécessaire car le Content-Type est application/json.
 */
$data           = json_decode(file_get_contents("php://input"), true);
$nom            = $data['nom'];
$prenom         = $data['prenom'];
$adresseMail    = $data['adresseMail'];
$nomUtilisateur = $data['nomUtilisateur'];
$motDePasse     = $data['motDePasse'];  // Mot de passe en clair, sera haché dans creerUtilisateur()
$idRole         = $data['idRole'];
$solde          = 0; // Solde initialisé à 0 pour tout nouvel utilisateur

/**
 * Vérifie si un utilisateur avec ce pseudo OU cet email existe déjà.
 * La méthode utilise un SELECT COUNT(*) avec OR → retourne un booléen.
 */
$utilisateurPresent = CUtilisateurs::getInstance()->utilisateurPresent($nomUtilisateur, $adresseMail);

if (!$utilisateurPresent) {
    // Utilisateur inexistant → création en BDD (le hash du mdp se fait dans creerUtilisateur)
    CUtilisateurs::getInstance()->creerUtilisateur($nom, $prenom, $nomUtilisateur, $adresseMail, $motDePasse, $idRole, $solde);

    // Stockage des infos de base en session PHP (utile si l'app utilise aussi les sessions)
    $_SESSION['user'] = [
        'nomUtilisateur' => $nomUtilisateur,
        'nom'            => $nom,
        'prenom'         => $prenom,
        'role'           => $idRole,
    ];

    // 201 Created : code HTTP standard pour une ressource nouvellement créée
    http_response_code(201);
    echo json_encode([
        "message" => "Utilisateur créé avec succès",
        "user"    => $_SESSION['user'],
    ]);
} else {
    // Doublon détecté sur le pseudo ou l'email → refus
    http_response_code(401);
    echo json_encode(["message" => "Utilisateur déjà inscrit"]);
}
?>