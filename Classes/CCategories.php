<?php
require_once __DIR__ . '/CCategorie.php';
require_once __DIR__ . '/Cdao.php';

/**
 * CCategories — Classe collection gérant l'ensemble des catégories.
 *
 * Utilise le pattern Singleton : une seule instance est créée
 * durant l'exécution PHP. Cela garantit qu'on n'ouvre qu'une
 * seule connexion BDD et qu'on ne charge les données qu'une fois.
 */
class CCategories {

    /** Tableau d'objets CCategorie (la collection en mémoire) */
    private array $collCategories = [];

    /** Objet d'accès à la base de données (couche DAO) */
    private Cdao $Odao;

    /** Instance unique de la classe (Singleton) */
    private static ?CCategories $instance = null;

    /**
     * Constructeur privé : interdit le "new CCategories()" direct.
     * On passe obligatoirement par getInstance().
     * Au chargement, on lit toute la table "categorie" et on
     * instancie un objet CCategorie par ligne retournée.
     */
    private function __construct() {
        try {
            $this->Odao = new Cdao();
            $rows = $this->Odao->execute("SELECT * FROM categorie");
            foreach ($rows as $row) {
                // Chaque ligne SQL devient un objet métier CCategorie
                $this->collCategories[] = new CCategorie($row['idCategorie'], $row['nomCategorie']);
            }
        } catch (PDOException $e) {
            die("Erreur de connexion à la base : " . $e->getMessage());
        }
    }

    /**
     * Point d'accès unique au Singleton.
     * Si l'instance n'a pas encore été créée, on la crée.
     * Sinon, on retourne l'instance existante.
     */
    public static function getInstance(): CCategories {
        if (self::$instance === null) {
            self::$instance = new CCategories();
        }
        return self::$instance;
    }

    /** Retourne le tableau complet des objets CCategorie */
    public function getCategories(): array {
        return $this->collCategories;
    }

    /**
     * Insère une nouvelle catégorie en BDD.
     * Utilise une requête préparée avec paramètre nommé
     * pour éviter les injections SQL.
     * Retourne l'id généré (lastInsertId via executeInsert).
     */
    public function ajouterCategorie(string $nomCategorie): int {
        try {
            $sql    = "INSERT INTO categorie (nomCategorie) VALUES (:nomCategorie)";
            $params = [":nomCategorie" => $nomCategorie];
            return $this->Odao->executeInsert($sql, $params);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    /**
     * Récupère l'id d'une catégorie par son nom.
     * Utilisé pour retrouver une catégorie lors de l'association
     * avec un livre, par exemple.
     */
    public function getIdByNom(string $nomCategorie): int {
        $sql     = "SELECT idCategorie FROM categorie WHERE nomCategorie = :nomCategorie LIMIT 1";
        $results = $this->Odao->execute($sql, [':nomCategorie' => $nomCategorie]);
        return (int) $results[0]['idCategorie'];
    }

    /**
     * Met à jour le nom d'une catégorie existante.
     * Requête préparée avec paramètres nommés → protection injection SQL.
     */
    public function setCategorie(string $nomCategorie, int $idCategorie): void {
        $sql = "UPDATE categorie SET nomCategorie = :nomCategorie WHERE idCategorie = :idCategorie";
        $params = [
            ":nomCategorie" => $nomCategorie,
            ":idCategorie"  => $idCategorie,
        ];
        $this->Odao->execute($sql, $params);
    }

    /**
     * Supprime une catégorie de la BDD par son id.
     * Retourne true si l'opération s'est bien déroulée.
     */
    public function deleteCategorie(int $idCategorie): bool {
        $this->Odao->execute(
            "DELETE FROM categorie WHERE idCategorie = :idCategorie",
            [":idCategorie" => $idCategorie]
        );
        return true;
    }

    /**
     * Vérifie si une catégorie avec ce nom existe déjà en BDD.
     * Utilisé avant un ajout pour éviter les doublons.
     * Retourne true si le nombre de résultats est supérieur à 0.
     */
    public function categoriePresent(string $nomCategorie): bool {
        $sql = "SELECT count(*) as nb FROM categorie WHERE nomCategorie = :nomCategorie";
        $params = [':nomCategorie' => $nomCategorie];
        $results = $this->Odao->execute($sql, $params);
        return $results[0]['nb'] > 0;
    }
}
?>