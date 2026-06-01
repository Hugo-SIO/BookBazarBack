<?php
/**
 * Contrôleur REST – POST /Set/SetUtilisateur.php
 * Met à jour un ou plusieurs champs d'un utilisateur existant.
 * Route protégée : nécessite un token JWT valide.
 *
 * Particularité : le SQL est construit dynamiquement côté PHP
 * (méthode setUtilisateur) pour ne mettre à jour que les champs
 * réellement modifiés côté React.
 */

// Origines autorisées (CORS)
$allowedOrigins = [
    "http://localhost:5173",
    "https://bookbazar.hugoal.fr"
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../../Classes/CUtilisateurs.php";
require_once "../auth.php";

/**
 * Vérification du JWT.
 * Si invalide ou absent → auth.php renvoie 401 et stoppe l'exécution.
 */
getAuthUser();

// Récupération de l'id depuis le corps FormData (envoyé par React)
$idUser = $_POST['idUser'] ?? null;

if (!$idUser) {
    echo json_encode(["success" => false, "message" => "idUser manquant"]);
    exit;
}

/**
 * Construction dynamique du tableau $fields.
 * On n'inclut dans la requête que les champs présents dans $_POST,
 * c'est-à-dire ceux que React a détecté comme modifiés.
 * La correspondance $_POST → colonne SQL est faite ici.
 * Ex: $_POST['pseudo'] → colonne "nomUtilisateur" en BDD.
 */
$fields = [];
if (isset($_POST['nom']))     $fields['nom']            = $_POST['nom'];
if (isset($_POST['prenom']))  $fields['prenom']         = $_POST['prenom'];
if (isset($_POST['pseudo']))  $fields['nomUtilisateur'] = $_POST['pseudo'];  // renommage pseudo → nomUtilisateur
if (isset($_POST['mail']))    $fields['adresseMail']    = $_POST['mail'];
if (isset($_POST['roleId']))  $fields['idRole']         = $_POST['roleId'];
if (isset($_POST['solde']))   $fields['solde']          = $_POST['solde'];

// Sécurité : on refuse si aucun champ n'a été envoyé
if (empty($fields)) {
    echo json_encode(["success" => false, "message" => "Aucun champ à mettre à jour"]);
    exit;
}

/**
 * Appel de la méthode métier via le Singleton.
 * setUtilisateur() construit le SQL dynamiquement avec implode()
 * et utilise des paramètres nommés PDO pour chaque champ.
 * → protection contre les injections SQL même avec un SQL dynamique.
 */
CUtilisateurs::getInstance()->setUtilisateur($fields, (int)$idUser);

echo json_encode(["success" => true, "message" => "Utilisateur mis à jour avec succès"]);
?>