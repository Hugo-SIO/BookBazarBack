<?php
    require_once __DIR__. '/CAuteur.php';
    require_once __DIR__. '/Cdao.php';

    class CAuteurs{

        private array $collAuteurs = [];
        private Cdao $Odao;

        private static ?CAuteurs $instance = null;

        private function __construct(){
            try {
                $this->Odao = new Cdao();
                $rows = $this->Odao->execute("SELECT * FROM auteur");
                foreach($rows as $row){
                    $OType = new CAuteur($row['idAuteur'], $row['nomAuteur']);
                    $this->collAuteurs[] = $OType;
                }
            } catch (PDOException $e) {
                die("Erreur de connexion à la base : " . $e->getMessage());
            }

        }

        public static function getInstance(): CAuteurs{
            if (self::$instance === null) {
                self::$instance = new CAuteurs();
            }
            return self::$instance;
        }
        public function getAuteurs(): array {
            return $this->collAuteurs;
        }

        public function ajouterAuteur($nomAuteur): int {
            try {
                $sql = "INSERT INTO Auteur (nomAuteur) VALUES (:nomAuteur)";
                $params = [":nomAuteur" => $nomAuteur];
                return $this->Odao->executeInsert($sql, $params); // ← pareil ici
            } catch (PDOException $e) {
                die("Erreur : " . $e->getMessage());
            }
        }

        public function getIdByNom(string $nomAuteur): int {
            $sql = "SELECT idAuteur FROM auteur WHERE nomAuteur = :nomAuteur LIMIT 1";
            $results = $this->Odao->execute($sql, [':nomAuteur' => $nomAuteur]);
            return (int) $results[0]['idAuteur'];
        }

        public function setAuteur($nomAuteur, $idAuteur): void
        {
    
            $sql = "UPDATE auteur SET nomAuteur = :nomAuteur WHERE idAuteur = :idAuteur";

            $params = [
                    ":nomAuteur" => $nomAuteur,
                    ":idAuteur" => $idAuteur
            ];

            $this->Odao->execute($sql, $params);
        }

        public function deleteAuteur($idAuteur): bool
        {
                // 1. Récupérer le chemin de l'image avant suppression
                $row = $this->Odao->execute(
                    "DELETE FROM auteur WHERE idAuteur = :idAuteur",
                    [":idAuteur" => $idAuteur]
                );

                return true;
        }

        public function auteurPresent(
            string $nomAuteur,
        ): bool{
            $sql = "SELECT count(*) as nb 
                    FROM auteur
                    WHERE nomAuteur = :nomAuteur";

            $params = [
                ':nomAuteur' => $nomAuteur
            ];

           $results =  $this->Odao->execute($sql, $params);
             
            return $results[0]['nb'] > 0;
        }
    }
?>