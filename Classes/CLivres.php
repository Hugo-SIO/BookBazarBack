<?php
require_once __DIR__ . '/CLivre.php';
require_once __DIR__ . '/Cdao.php';

/**
 * CLivres — Classe collection gérant l'ensemble des livres/annonces.
 *
 * Pattern Singleton : une seule instance est créée durant l'exécution PHP.
 * Garantit qu'on n'ouvre qu'une seule connexion BDD et qu'on ne charge
 * les données qu'une seule fois par requête.
 *
 * C'est la classe la plus complexe du projet car un livre est lié à
 * plusieurs tables (auteur, catégorie, vendeur, acheteur via bibliothèque).
 */
class CLivres {

    /** Tableau d'objets CLivre chargés depuis la BDD */
    private array $collLivres = [];

    /** Objet d'accès à la base de données */
    private Cdao $Odao;

    /** Instance unique du Singleton */
    private static ?CLivres $instance = null;

    /**
     * Constructeur privé : interdit le "new CLivres()" direct.
     *
     * Charge tous les livres avec un SELECT multi-jointures :
     * - JOIN utilisateur u  → récupère le nom du vendeur (CONCAT nom + prénom)
     * - JOIN auteur a       → récupère le nom de l'auteur
     * - JOIN categorie t    → récupère le nom de la catégorie
     * - LEFT JOIN contenir  → table de liaison livre ↔ bibliothèque
     * - LEFT JOIN bibliothèque → liée à l'acheteur
     * - Left JOIN utilisateur ut → récupère le nom de l'acheteur
     *
     * Les JOIN sont LEFT JOIN car un livre peut ne pas encore avoir
     * d'acheteur → on ne veut pas exclure les livres disponibles.
     */
    private function __construct() {
        try {
            $this->Odao = new Cdao();

            $rows = $this->Odao->execute("SELECT 
                                          l.*,
                                          nomCategorie as categorie, 
                                          nomAuteur as auteur, 
                                          concat(u.nom, ' ', u.prenom) as vendeur,
                                          concat(ut.nom, ' ', ut.prenom) as acheteur
                                          FROM livre l
                                          LEFT JOIN utilisateur u 
                                              ON l.idVendeur = u.idUtilisateur
                                          LEFT JOIN auteur a 
                                              ON l.idAuteur = a.idAuteur 
                                          LEFT JOIN categorie t 
                                              ON l.idCategorie = t.idCategorie
                                          LEFT JOIN contenir c
                                              ON c.idLivre = l.idLivre
                                          LEFT JOIN bibliothèque b
                                              ON b.idBiblioetheque = c.idBibliotheque
                                          LEFT JOIN utilisateur ut
                                              ON b.idUtilisateur = ut.idUtilisateur");

            foreach ($rows as $row) {
                /**
                 * dateAchat est nullable : si la colonne est vide en BDD
                 * (livre pas encore acheté), on stocke null plutôt que
                 * de créer un DateTime invalide.
                 * L'opérateur ternaire évite une exception DateTime.
                 */
                $dateAchat = !empty($row['dateAchat'])
                    ? new DateTime($row['dateAchat'])
                    : null;

                // Chaque ligne SQL devient un objet métier CLivre
                $this->collLivres[] = new CLivre(
                    $row['idLivre'],
                    $row['nomLivre'],
                    $row['anneeParution'],
                    $row['categorie'],
                    $row['auteur'],
                    $row['vendeur'],
                    $row['idVendeur'],
                    new DateTime($row['dateAjout']),
                    $row['disponible'],
                    $row['prix'],
                    $row['description'],
                    $row['acheteur'] ?? "", // ?? "" : évite null si pas d'acheteur
                    $row['image'],
                    $dateAchat
                );
            }
        } catch (PDOException $e) {
            die("Erreur de connexion à la base : " . $e->getMessage());
        }
    }

    /**
     * Point d'accès unique au Singleton.
     * Crée l'instance au premier appel, la réutilise ensuite.
     */
    public static function getInstance(): CLivres {
        if (self::$instance === null) {
            self::$instance = new CLivres();
        }
        return self::$instance;
    }

    /**
     * Retourne tous les livres (disponibles ou non).
     * Utilisé par l'admin qui voit toutes les annonces,
     * y compris celles déjà vendues.
     */
    public function getLivres(): array {
        return $this->collLivres;
    }

    /**
     * Retourne uniquement les livres disponibles (disponible = 1).
     * Utilisé pour la page publique des annonces vue par les acheteurs.
     * Contrairement au constructeur, cette méthode fait une nouvelle
     * requête SQL avec WHERE plutôt que de filtrer le tableau en mémoire,
     * ce qui est plus performant si la collection est grande.
     */
    public function getLivresDisponible(): array {
        $result = $this->Odao->execute("SELECT 
                                          l.*,
                                          nomCategorie as categorie, 
                                          nomAuteur as auteur, 
                                          concat(u.nom, ' ', u.prenom) as vendeur,
                                          concat(ut.nom, ' ', ut.prenom) as acheteur
                                          FROM livre l
                                          LEFT JOIN utilisateur u 
                                              ON l.idVendeur = u.idUtilisateur
                                          LEFT JOIN auteur a 
                                              ON l.idAuteur = a.idAuteur 
                                          LEFT JOIN categorie t 
                                              ON l.idCategorie = t.idCategorie
                                          LEFT JOIN contenir c
                                              ON c.idLivre = l.idLivre
                                          LEFT JOIN bibliothèque b
                                              ON b.idBiblioetheque = c.idBibliotheque
                                          LEFT JOIN utilisateur ut
                                              ON b.idUtilisateur = ut.idUtilisateur
                                          WHERE disponible = 1");
        return $result;
    }

    /**
     * Insère un nouveau livre en BDD.
     * L'image a déjà été uploadée sur le disque par le contrôleur (AddLivre.php)
     * avant l'appel de cette méthode → on reçoit ici uniquement le chemin relatif.
     * disponible est forcé à 1 (livre immédiatement visible à la publication).
     * dateAjout est définie côté SQL avec NOW() → pas besoin de la passer en paramètre.
     * Les paramètres nommés PDO (:nomLivre, :prix…) protègent contre les injections SQL.
     */
    public function ajouterLivre(
        $idVendeur,
        $titreLivre,
        $annéeParution,
        $idCategorie,
        $idAuteur,
        $desc,
        $prix,
        string|null $imagePath  // Chemin relatif déjà construit par le contrôleur
    ): bool {
        try {
            $sql = "INSERT INTO livre
                        (nomlivre, anneeParution, idAuteur, idVendeur, idCategorie,
                         dateAjout, disponible, prix, description, image)
                    VALUES
                        (:nomLivre, :anneeParution, :idAuteur, :idVendeur, :idCategorie,
                         NOW(), 1, :prix, :desc, :image)";

            $params = [
                ":nomLivre"      => $titreLivre,
                ":anneeParution" => $annéeParution,
                ":idAuteur"      => $idAuteur,
                ":idVendeur"     => $idVendeur,
                ":idCategorie"   => $idCategorie,
                ":prix"          => $prix,
                ":desc"          => $desc,
                ":image"         => $imagePath,
            ];

            $this->Odao->execute($sql, $params);
            return true;

        } catch (PDOException $e) {
            die("Erreur BDD : " . $e->getMessage());
        }
    }

    /**
     * Récupère le nom de l'acheteur d'un livre donné.
     * Remonte la chaîne : livre → contenir → bibliothèque → utilisateur.
     * Retourne null si le livre n'a pas encore été acheté (LEFT JOIN vide).
     * Utilisé dans la page détail d'une annonce pour afficher l'acheteur.
     */
    public function getAcheteur(int $idAnnonce): ?array {
        $sql = "SELECT concat(u.nom, ' ', u.prenom) AS acheteur 
                FROM livre l 
                LEFT JOIN contenir c 
                    ON l.idLivre = c.idLivre 
                LEFT JOIN bibliothèque b 
                    ON c.idBibliotheque = b.idBiblioetheque 
                LEFT JOIN utilisateur u 
                    ON b.idUtilisateur = u.idUtilisateur
                WHERE l.idLivre = :idLivre";

        $result = $this->Odao->execute($sql, [":idLivre" => $idAnnonce]);

        return $result[0] ?? null; // ?? null : retourne null si tableau vide
    }

    /**
     * Supprime un livre de la BDD ET son image du disque.
     * L'ordre des opérations est critique :
     *   1. SELECT image  → récupère le chemin AVANT de supprimer la ligne
     *   2. unlink()      → supprime le fichier image du serveur
     *   3. DELETE FROM   → supprime la ligne en BDD
     * Si on inversait l'ordre (DELETE d'abord), on perdrait le chemin
     * de l'image et le fichier resterait orphelin sur le disque.
     */
    public function deleteAnnonce(int $idAnnonce): bool {
        // 1. Récupérer le chemin de l'image avant suppression
        $row = $this->Odao->execute(
            "SELECT image FROM livre WHERE idLivre = :idAnnonce",
            [":idAnnonce" => $idAnnonce]
        );

        // 2. Supprimer le fichier image si il existe sur le disque
        if (!empty($row[0]['image'])) {
            $imagePath = __DIR__ . '/../' . $row[0]['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath); // unlink() = suppression de fichier en PHP
            }
        }

        // 3. Supprimer la ligne en BDD (après l'image pour ne pas perdre le chemin)
        $this->Odao->execute(
            "DELETE FROM livre WHERE idLivre = :idAnnonce",
            [":idAnnonce" => $idAnnonce]
        );

        return true;
    }

    /**
     * Récupère un seul livre par son id, avec toutes ses jointures.
     * Utilisé par SetLivre.php pour récupérer l'ancienne image avant
     * de l'écraser, et par la page de détail d'une annonce.
     * Retourne un tableau associatif (pas un objet CLivre) car
     * c'est plus pratique pour accéder directement aux colonnes SQL brutes.
     * ?? null : retourne null si aucun résultat (id inexistant).
     */
    public function getLivreById(int $idAnnonce): ?array {
        $sql = "SELECT 
                    l.*,
                    nomCategorie as categorie, 
                    nomAuteur as auteur, 
                    concat(u.nom, ' ', u.prenom) as vendeur,
                    concat(ut.nom, ' ', ut.prenom) as acheteur
                FROM livre l
                LEFT JOIN utilisateur u 
                    ON l.idVendeur = u.idUtilisateur
                LEFT JOIN auteur a 
                    ON l.idAuteur = a.idAuteur 
                LEFT JOIN categorie t 
                    ON l.idCategorie = t.idCategorie
                LEFT JOIN contenir c
                    ON c.idLivre = l.idLivre
                LEFT JOIN bibliothèque b
                    ON b.idBiblioetheque = c.idBibliotheque
                LEFT JOIN utilisateur ut
                    ON b.idUtilisateur = ut.idUtilisateur
                WHERE l.idLivre = :idAnnonce";

        $result = $this->Odao->execute($sql, [":idAnnonce" => $idAnnonce]);

        return $result[0] ?? null;
    }

    /**
     * Met à jour dynamiquement les champs d'un livre.
     * Même principe que setUtilisateur() dans CUtilisateurs :
     * le SQL est construit dynamiquement avec implode() sur $fields
     * pour ne mettre à jour que les champs réellement modifiés côté React.
     *
     * Exemple : $fields = ['nomLivre' => 'Dune', 'prix' => 8.50]
     * → UPDATE livre SET nomLivre = :nomLivre, prix = :prix WHERE idLivre = :idAnnonce
     *
     * Les paramètres nommés PDO protègent contre les injections SQL
     * même si le SQL est construit dynamiquement.
     */
    public function setLivre(array $fields, int $idAnnonce): void {
        $setParts = [];
        $params   = [":idAnnonce" => $idAnnonce];

        foreach ($fields as $col => $value) {
            $placeholder          = ":" . $col;
            $setParts[]           = "$col = $placeholder"; // ex: "prix = :prix"
            $params[$placeholder] = $value;
        }

        // implode() assemble : "nomLivre = :nomLivre, prix = :prix"
        $sql = "UPDATE livre SET " . implode(", ", $setParts) . " WHERE idLivre = :idAnnonce";

        $this->Odao->execute($sql, $params);
    }
}
?>