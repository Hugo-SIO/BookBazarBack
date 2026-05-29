<?php
    require_once __DIR__. '/CLivre.php';
    require_once __DIR__. '/Cdao.php';

    class CLivres{

        private array $collLivres = [];
        private Cdao $Odao;

        private static ?CLivres $instance = null;

        private function __construct(){
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
                                              on l.idVendeur = u.idUtilisateur
                                              LEFT JOIN auteur a 
                                              on l.idAuteur = a.idAuteur 
                                              LEFT JOIN categorie t 
                                              on l.idCategorie = t.idCategorie
                                              LEFT JOIN contenir c
                                              on c.idLivre = l.idLivre
                                              LEFT JOIN bibliothèque b
                                              on b.idBiblioetheque = c.idBibliotheque
                                              LEFT JOIN utilisateur ut
                                              on b.idUtilisateur = ut.idUtilisateur
                                              ");
                foreach($rows as $row){
                    $dateAchat = !empty($row['dateAchat'])
                    ? new DateTime($row['dateAchat'])
                    : null;
                    $OLivre = new CLivre($row['idLivre'], $row['nomLivre'], $row['anneeParution'], $row['categorie'], $row['auteur'], $row['vendeur'], $row['idVendeur'], new DateTime($row['dateAjout']), $row['disponible'], $row['prix'], $row['description'], $row['acheteur'] ?? "", $row['image'], $dateAchat);
                    $this->collLivres[] = $OLivre;
                }
            } catch (PDOException $e) {
                die("Erreur de connexion à la base : " . $e->getMessage());
            }
        }

        public static function getInstance(): CLivres{
            if (self::$instance === null) {
                self::$instance = new CLivres();
            }
            return self::$instance;
        }
        public function getLivres(): array {
            return $this->collLivres;   
        }

        public function getLivresDisponible(): array {
            $result = $this->Odao->execute("SELECT 
                                              l.*,
                                              nomCategorie as categorie, 
                                              nomAuteur as auteur, 
                                              concat(u.nom, ' ', u.prenom) as vendeur,
                                              concat(ut.nom, ' ', ut.prenom) as acheteur
                                              FROM livre l
                                              LEFT JOIN utilisateur u 
                                              on l.idVendeur = u.idUtilisateur
                                              LEFT JOIN auteur a 
                                              on l.idAuteur = a.idAuteur 
                                              LEFT JOIN categorie t 
                                              on l.idCategorie = t.idCategorie
                                              LEFT JOIN contenir c
                                              on c.idLivre = l.idLivre
                                              LEFT JOIN bibliothèque b
                                              on b.idBiblioetheque = c.idBibliotheque
                                              LEFT JOIN utilisateur ut
                                              on b.idUtilisateur = ut.idUtilisateur
                                              WHERE disponible = 1");
            return $result;
        }

        public function ajouterLivre(
            $idVendeur,
            $titreLivre,
            $annéeParution,
            $idCategorie,
            $idAuteur,
            $desc,
            $prix,
            string|null $imagePath  // ← on reçoit directement le chemin, pas le fichier
        ): bool {
            try {
                $sql = "INSERT INTO livre
                    (nomlivre, anneeParution, idAuteur, idVendeur, idCategorie, dateAjout, disponible, prix, description, image)
                    VALUES
                    (:nomLivre, :anneeParution, :idAuteur, :idVendeur, :idCategorie, NOW(), 1, :prix, :desc, :image)";

                $params = [
                    ":nomLivre"      => $titreLivre,
                    ":anneeParution" => $annéeParution,
                    ":idAuteur"      => $idAuteur,
                    ":idVendeur"     => $idVendeur,
                    ":idCategorie"   => $idCategorie,
                    ":prix"          => $prix,
                    ":desc"          => $desc,
                    ":image"         => $imagePath  // ← directement utilisé
                ];

                $this->Odao->execute($sql, $params);
                return true;

            } catch (PDOException $e) {
                die("Erreur BDD : " . $e->getMessage());
            }
        }

        

        public function getAcheteur(int $idAnnonce): ?array
        {
            $sql = "
                SELECT concat(u.nom, ' ', u.prenom) AS acheteur 
                FROM livre l 
                LEFT JOIN contenir c 
                ON l.idLivre = c.idLivre 
                LEFT JOIN bibliothèque b 
                ON c.idBibliotheque = b.idBiblioetheque 
                LEFT JOIN utilisateur u 
                ON b.idUtilisateur = u.idUtilisateur
                WHERE l.idLivre = :idLivre
            ";

            $params = [
                ":idLivre" => $idAnnonce
            ];

            $result = $this->Odao->execute($sql, $params);

            return $result[0] ?? null;
        }

        public function deleteAnnonce(int $idAnnonce): bool
            {
                // 1. Récupérer le chemin de l'image avant suppression
                $row = $this->Odao->execute(
                    "SELECT image FROM livre WHERE idLivre = :idAnnonce",
                    [":idAnnonce" => $idAnnonce]
                );

                // 2. Supprimer le fichier si il existe
                if (!empty($row[0]['image'])) {
                    $imagePath = __DIR__ . '/../' . $row[0]['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }

                // 3. Supprimer en BDD
                $this->Odao->execute(
                    "DELETE FROM livre WHERE idLivre = :idAnnonce",
                    [":idAnnonce" => $idAnnonce]
                );

                return true;
            }
            
        public function getLivreById(int $idAnnonce): ?array
        {
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

            $params = [":idAnnonce" => $idAnnonce];

            $result = $this->Odao->execute($sql, $params);

            return $result[0] ?? null;
        }
    

    public function setLivre(array $fields, int $idAnnonce): void
    {
        // Construire "nomLivre = :nomLivre, prix = :prix, ..." dynamiquement
        $setParts = [];
        $params   = [":idAnnonce" => $idAnnonce];

        foreach ($fields as $col => $value) {
            $placeholder   = ":" . $col;
            $setParts[]    = "$col = $placeholder";
            $params[$placeholder] = $value;
        }

        $sql = "UPDATE livre SET " . implode(", ", $setParts) . " WHERE idLivre = :idAnnonce";

        $this->Odao->execute($sql, $params);
    }
    }
?>