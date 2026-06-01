<?php
/**
 * Contrôleur REST – POST /Auth/Register.php
 * Crée un nouveau compte utilisateur après vérification
 * de l'absence de doublon (nom d'utilisateur ou email déjà pris).
 * Route publique : aucun JWT requis (l'utilisateur n'est pas encore connecté).
 */

ini_set('display_errors', 1); // À désactiver en production
error_reporting(E_ALL);

session_start(); // Démarre la session pour y stocker les infos utilisateur après inscription

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

require_once '../../Classes/CUtilisateurs.php';

// Lecture du corps JSON envoyé par le formulaire React
$data           = json_decode(file_get_contents("php://input"), true);
$nom            = $data['nom'];
$prenom         = $data['prenom'];
$adresseMail    = $data['adresseMail'];
$nomUtilisateur = $data['nomUtilisateur'];
$motDePasse     = $data['motDePasse'];
$idRole         = 2; // Rôle "utilisateur standard" attribué par défaut à l'inscription
                     // (le rôle admin = 1 n'est pas attribuable via ce formulaire)

/**
 * Vérifie si un compte avec ce nom d'utilisateur ou cet email existe déjà.
 * utilisateurPresent() fait un SELECT COUNT(*) sur les deux champs.
 */
$utilisateurPresent = CUtilisateurs::getInstance()->utilisateurPresent($nomUtilisateur, $adresseMail);

if (!$utilisateurPresent) {
    /**
     * Création de l'utilisateur en BDD.
     * La méthode creerUtilisateur() hache le mot de passe
     * avec password_hash() (bcrypt) avant de l'insérer.
     * Le solde initial est 0 (dernier paramètre).
     */
    CUtilisateurs::getInstance()->creerUtilisateur(
        $nom, $prenom, $nomUtilisateur,
        $adresseMail, $motDePasse, $idRole, 0
    );

    // Stocke les infos de base en session (optionnel si on utilise JWT)
    $_SESSION['user'] = [
        'nomUtilisateur' => $nomUtilisateur,
        'nom'            => $nom,
        'prenom'         => $prenom,
        'role'           => $idRole
    ];

    http_response_code(201); // 201 Created : ressource créée avec succès
    echo json_encode([
        "message" => "Utilisateur créé avec succès",
        "user"    => $_SESSION['user']
    ]);
} else {
    // Doublon détecté : nom d'utilisateur ou email déjà utilisé
    http_response_code(401);
    echo json_encode(["message" => "Utilisateur déjà inscrit"]);
}
?>