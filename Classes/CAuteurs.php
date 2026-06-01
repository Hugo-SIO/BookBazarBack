<?php
require_once __DIR__ . '/CAuteur.php';
require_once __DIR__ . '/Cdao.php';

/**
 * CAuteurs — Classe collection qui gère l'ensemble des auteurs.
 *
 * Pattern Singleton : une seule instance est créée durant
 * l'exécution. Cela évite de multiplier les connexions BDD
 * et les requêtes SQL inutiles.
 */
class CAuteurs {

    /** Tableau d'objets CAuteur (la collection) */
    private array $collAuteurs = [];

    /** Objet d'accès à la base de données */
    private Cdao $Odao;

    /** Instance unique de la classe (Singleton) */
    private static ?CAuteurs $instance = null;

    /**
     * Constructeur privé : empêche l'instanciation directe
     * avec "new CAuteurs()". On passe obligatoirement par getInstance().
     * Au chargement, on récupère tous les auteurs en BDD
     * et on les instancie en objets CAuteur.
     */
    private function __construct() {
        try {
            $this->Odao = new Cdao();
            $rows = $this->Odao->execute("SELECT * FROM auteur");
            foreach ($rows as $row) {
                // On crée un objet CAuteur pour chaque ligne SQL
                $this->collAuteurs[] = new CAuteur($row['idAuteur'], $row['nomAuteur']);
            }
        } catch (PDOException $e) {
            die("Erreur de connexion à la base : " . $e->getMessage());
        }
    }

    /**
     * Point d'accès unique au Singleton.
     * Si l'instance n'existe pas encore, on la crée.
     */
    public static function getInstance(): CAuteurs {
        if (self::$instance === null) {
            self::$instance = new CAuteurs();
        }
        return self::$instance;
    }

    /** Retourne le tableau de tous les objets CAuteur */
    public function getAuteurs(): array {
        return $this->collAuteurs;
    }

    /**
     * Insère un nouvel auteur en BDD.
     * Retourne l'id généré (lastInsertId).
     */
    public function ajouterAuteur(string $nomAuteur): int {
        $sql    = "INSERT INTO Auteur (nomAuteur) VALUES (:nomAuteur)";
        $params = [":nomAuteur" => $nomAuteur];
        return $this->Odao->executeInsert($sql, $params);
    }

    /**
     * Récupère l'id d'un auteur par son nom.
     * Utile pour vérifier l'existence avant d'associer à un livre.
     */
    public function getIdByNom(string $nomAuteur): int {
        $sql     = "SELECT idAuteur FROM auteur WHERE nomAuteur = :nomAuteur LIMIT 1";
        $results = $this->Odao->execute($sql, [':nomAuteur' => $nomAuteur]);
        return (int) $results[0]['idAuteur'];
    }

    /**
     * Met à jour le nom d'un auteur existant.
     * Utilise des paramètres nommés pour éviter les injections SQL.
     */
    public function setAuteur(string $nomAuteur, int $idAuteur): void {
        $sql = "UPDATE auteur SET nomAuteur = :nomAuteur WHERE idAuteur = :idAuteur";
        $params = [
            ":nomAuteur" => $nomAuteur,
            ":idAuteur"  => $idAuteur,
        ];
        $this->Odao->execute($sql, $params);
    }

    /**
     * Supprime un auteur de la BDD par son id.
     * Retourne true si la suppression s'est bien passée.
     */
    public function deleteAuteur(int $idAuteur): bool {
        $this->Odao->execute(
            "DELETE FROM auteur WHERE idAuteur = :idAuteur",
            [":idAuteur" => $idAuteur]
        );
        return true;
    }

    /**
     * Vérifie si un auteur avec ce nom existe déjà.
     * Utilisé avant un ajout pour éviter les doublons.
     */
    public function auteurPresent(string $nomAuteur): bool {
        $sql = "SELECT count(*) as nb FROM auteur WHERE nomAuteur = :nomAuteur";
        $results = $this->Odao->execute($sql, [':nomAuteur' => $nomAuteur]);
        return $results[0]['nb'] > 0;
    }
}
?>