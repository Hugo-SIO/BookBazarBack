<?php
require_once __DIR__ . '/CUtilisateur.php';
require_once __DIR__ . '/Cdao.php';

/**
 * CUtilisateurs — Classe collection gérant l'ensemble des utilisateurs.
 *
 * Pattern Singleton : une seule instance est créée durant l'exécution.
 * Cela garantit qu'on n'ouvre qu'une seule connexion BDD et qu'on
 * ne charge les données qu'une fois par requête PHP.
 */
class CUtilisateurs {

    /** Tableau d'objets CUtilisateur (la collection en mémoire) */
    private array $collUtilisateurs = [];

    /** Objet d'accès à la base de données */
    private Cdao $Odao;

    /** Instance unique de la classe (Singleton) */
    private static ?CUtilisateurs $instance = null;

    /**
     * Constructeur privé : interdit le "new CUtilisateurs()" direct.
     * Charge tous les utilisateurs depuis la BDD avec un JOIN sur
     * la table role pour récupérer le nom du rôle en une seule requête.
     */
    private function __construct() {
        try {
            $this->Odao = new Cdao();

            // JOIN entre utilisateur et role pour avoir nomRole directement
            $rows = $this->Odao->execute(
                "SELECT idUtilisateur, nom, prenom, nomUtilisateur, adresseMail,
                        motDePasseHash, nomRole as role, r.idRole as idRole, solde
                 FROM utilisateur u
                 JOIN role r ON u.idRole = r.idRole"
            );

            foreach ($rows as $row) {
                // Chaque ligne SQL devient un objet métier CUtilisateur
                $this->collUtilisateurs[] = new CUtilisateur(
                    $row['idUtilisateur'],
                    $row['nom'],
                    $row['prenom'],
                    $row['nomUtilisateur'],
                    $row['adresseMail'],
                    $row['motDePasseHash'],
                    $row['role'],
                    $row['idRole'],
                    $row['solde']
                );
            }
        } catch (PDOException $e) {
            die("Erreur de connexion à la base : " . $e->getMessage());
        }
    }

    /**
     * Point d'accès unique au Singleton.
     * Crée l'instance si elle n'existe pas encore, sinon la retourne.
     */
    public static function getInstance(): CUtilisateurs {
        if (self::$instance === null) {
            self::$instance = new CUtilisateurs();
        }
        return self::$instance;
    }

    /** Retourne le tableau complet des objets CUtilisateur */
    public function getUtilisateur(): array {
        return $this->collUtilisateurs;
    }

    /**
     * Crée un nouvel utilisateur en BDD.
     * Le mot de passe est haché avec password_hash() (bcrypt par défaut)
     * avant insertion → jamais de mot de passe en clair en base.
     * Crée aussi automatiquement une bibliothèque vide pour cet utilisateur.
     */
    public function creerUtilisateur(
        string $nom,
        string $prenom,
        string $nomUtilisateur,
        string $adresseMail,
        string $motDePasse,
        int    $idRole,
        int    $solde
    ): bool {
        // Hachage du mot de passe (PASSWORD_DEFAULT = bcrypt)
        $motDePasseHash = password_hash($motDePasse, PASSWORD_DEFAULT);

        $sql = "INSERT INTO utilisateur
                    (nom, prenom, nomUtilisateur, adresseMail, motDePasseHash, idRole, solde)
                VALUES (:nom, :prenom, :nomUtilisateur, :adresseMail, :motDePasseHash, :idRole, :solde)";

        $params = [
            ':nom'            => $nom,
            ':prenom'         => $prenom,
            ':nomUtilisateur' => $nomUtilisateur,
            ':adresseMail'    => $adresseMail,
            ':motDePasseHash' => $motDePasseHash,
            ':idRole'         => $idRole,
            ':solde'          => $solde,
        ];

        // executeInsert retourne l'id généré (lastInsertId)
        $idUtilisateur = $this->Odao->executeInsert($sql, $params);

        // Création automatique d'une bibliothèque vide liée à cet utilisateur
        $this->Odao->execute(
            "INSERT INTO bibliothèque (idUtilisateur) VALUES (:idUtilisateur)",
            [":idUtilisateur" => $idUtilisateur]
        );

        return true;
    }

    /**
     * Vérifie si un utilisateur existe déjà avec ce pseudo ou cet email.
     * Utilisé avant la création pour éviter les doublons.
     * Le OR dans le WHERE vérifie les deux champs en une seule requête.
     */
    public function utilisateurPresent(string $nomUtilisateur, string $adresseMail): bool {
        $sql = "SELECT count(*) as nb
                FROM utilisateur
                WHERE adresseMail = :adresseMail OR nomUtilisateur = :nomUtilisateur";

        $params = [
            ':adresseMail'    => $adresseMail,
            ':nomUtilisateur' => $nomUtilisateur,
        ];

        $results = $this->Odao->execute($sql, $params);
        return $results[0]['nb'] > 0;
    }

    /**
     * Supprime un utilisateur de la BDD par son id.
     * Les données liées (bibliothèque, achats...) doivent être
     * gérées par les contraintes FK ou supprimées en cascade en BDD.
     */
    public function deleteUtilisateur(int $idUtilisateur): bool {
        $this->Odao->execute(
            "DELETE FROM utilisateur WHERE idUtilisateur = :idUtilisateur",
            [":idUtilisateur" => $idUtilisateur]
        );
        return true;
    }

    /**
     * Met à jour dynamiquement les champs d'un utilisateur.
     * Contrairement aux autres setters, le SQL est construit
     * dynamiquement à partir du tableau $fields passé en paramètre.
     * Seuls les champs présents dans $fields sont mis à jour.
     * → Évite d'écraser des données non modifiées.
     *
     * Exemple : $fields = ['nom' => 'Martin', 'solde' => 50]
     * → UPDATE utilisateur SET nom = :nom, solde = :solde WHERE idUtilisateur = :idUtilisateur
     */
    public function setUtilisateur(array $fields, int $idUtilisateur): void {
        $setParts = [];
        $params   = [":idUtilisateur" => $idUtilisateur];

        foreach ($fields as $col => $value) {
            $placeholder         = ":" . $col;
            $setParts[]          = "$col = $placeholder"; // ex: "nom = :nom"
            $params[$placeholder] = $value;
        }

        // implode() assemble les parties : "nom = :nom, solde = :solde"
        $sql = "UPDATE utilisateur SET " . implode(", ", $setParts) . " WHERE idUtilisateur = :idUtilisateur";

        $this->Odao->execute($sql, $params);
    }

    /**
     * Récupère uniquement le solde d'un utilisateur par son id.
     * Retourne null si l'utilisateur n'est pas trouvé.
     * Utilisé pour vérifier si l'utilisateur a assez de crédit
     * avant un achat, par exemple.
     */
    public function getSoldeById(int $idUtilisateur): ?array {
        $sql = "SELECT solde FROM utilisateur WHERE idUtilisateur = :idUtilisateur";
        $result = $this->Odao->execute($sql, [":idUtilisateur" => $idUtilisateur]);
        return $result[0] ?? null; // ?? null : retourne null si le tableau est vide
    }
}
?>