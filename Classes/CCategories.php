<?php
    require_once __DIR__. '/CCategorie.php';
    require_once __DIR__. '/Cdao.php';

    class CCategories{

        private array $collCategories = [];
        private Cdao $Odao;

        private static ?CCategories $instance = null;

        private function __construct(){
            try {
                $this->Odao = new Cdao();
                $rows = $this->Odao->execute("SELECT * FROM categorie");
                foreach($rows as $row){
                    $OType = new CCategorie($row['idCategorie'], $row['nomCategorie']);
                    $this->collCategories[] = $OType;
                }
            } catch (PDOException $e) {
                die("Erreur de connexion à la base : " . $e->getMessage());
            }

        }

        public static function getInstance(): CCategories{
            if (self::$instance === null) {
                self::$instance = new CCategories();
            }
            return self::$instance;
        }
        public function getCategories(): array {
            return $this->collCategories;
        }

        public function ajouterCategorie($nomCategorie): int {
            try {
                $sql = "INSERT INTO categorie (nomCategorie) VALUES (:nomCategorie)";
                $params = [":nomCategorie" => $nomCategorie];
                return $this->Odao->executeInsert($sql, $params); // ← executeInsert au lieu de execute
            } catch (PDOException $e) {
                die("Erreur : " . $e->getMessage());
            }
        }

        public function getIdByNom(string $nomCategorie): int {
            $sql = "SELECT idCategorie FROM categorie WHERE nomCategorie = :nomCategorie LIMIT 1";
            $results = $this->Odao->execute($sql, [':nomCategorie' => $nomCategorie]);
            return (int) $results[0]['idCategorie'];
        }

        public function setCategorie($nomCategorie, $idCategorie): void
        {
    
            $sql = "UPDATE categorie SET nomCategorie = :nomCategorie WHERE idCategorie = :idCategorie";

            $params = [
                    ":nomCategorie" => $nomCategorie,
                    ":idCategorie" => $idCategorie
            ];

            $this->Odao->execute($sql, $params);
        }

        public function deleteCategorie($idCategorie): bool
        {
                // 1. Récupérer le chemin de l'image avant suppression
                $row = $this->Odao->execute(
                    "DELETE FROM categorie WHERE idCategorie = :idCategorie",
                    [":idCategorie" => $idCategorie]
                );

                return true;
        }

        public function categoriePresent(
            string $nomCategorie,
        ): bool{
            $sql = "SELECT count(*) as nb 
                    FROM categorie
                    WHERE nomCategorie = :nomCategorie";

            $params = [
                ':nomCategorie' => $nomCategorie
            ];

           $results =  $this->Odao->execute($sql, $params);
             
            return $results[0]['nb'] > 0;
        }
        
    }
?>