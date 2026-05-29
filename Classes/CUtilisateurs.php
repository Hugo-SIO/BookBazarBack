<?php
    require_once __DIR__. '/CUtilisateur.php';
    require_once __DIR__. '/Cdao.php';

    class CUtilisateurs{

        private array $collUtilisateurs = [];
        private Cdao $Odao;

        private static ?CUtilisateurs $instance = null;

        private function __construct(){
            try {
                $this->Odao = new Cdao();
                $rows = $this->Odao->execute("SELECT idUtilisateur, nom, prenom, nomUtilisateur, adresseMail, motDePasseHash, nomRole as role, r.idRole as idRole, solde
                                              FROM utilisateur u
                                              JOIN role r
                                              on u.idRole = r.idRole
                                              ");
                foreach($rows as $row){
                    $OUtilisateur = new CUtilisateur($row['idUtilisateur'], $row['nom'], $row['prenom'], $row['nomUtilisateur'], $row['adresseMail'], $row['motDePasseHash'], $row['role'], $row['idRole'], $row['solde']);
                    $this->collUtilisateurs[] = $OUtilisateur;
                }
            } catch (PDOException $e) {
                die("Erreur de connexion à la base : " . $e->getMessage());
            }

        }

        public static function getInstance(): CUtilisateurs{
            if (self::$instance === null) {
                self::$instance = new CUtilisateurs();
            }
            return self::$instance;
        }

        public function getUtilisateur(): array {
            return $this->collUtilisateurs;
        }

        public function creerUtilisateur(
            string $nom,
            string $prenom,
            string $nomUtilisateur,
            string $adresseMail,
            string $motDePasse,
            int $idRole,
            int $solde
        ): bool {

            $motDePasseHash = password_hash($motDePasse, PASSWORD_DEFAULT);

            $sql = "INSERT INTO utilisateur 
                    (nom, prenom, nomUtilisateur, adresseMail, motDePasseHash, idRole, solde)
                    VALUES (:nom, :prenom, :nomUtilisateur, :adresseMail, :motDePasseHash, :idRole, :solde)";

            $params = [
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':nomUtilisateur' => $nomUtilisateur,
                ':adresseMail' => $adresseMail,
                ':motDePasseHash' => $motDePasseHash,
                ':idRole' => $idRole,
                ':solde' => $solde
            ];

            $idUtilisateur = $this->Odao->executeInsert($sql, $params);

            $sql = "INSERT INTO bibliothèque
                    (idUtilisateur)
                    VALUES(:idUtilisateur)";
            $params = [
                ":idUtilisateur" => $idUtilisateur
            ];
            $this->Odao->execute($sql, $params);

            return true;
        }

        public function utilisateurPresent(
            string $nomUtilisateur,
            string $adresseMail
        ): bool{
            $sql = "SELECT count(*) as nb 
                    FROM utilisateur 
                    WHERE adresseMail = :adresseMail OR nomUtilisateur = :nomUtilisateur";

            $params = [
                ':adresseMail' => $adresseMail,
                ':nomUtilisateur' => $nomUtilisateur
            ];

           $results =  $this->Odao->execute($sql, $params);
             
            return $results[0]['nb'] > 0;
        }

        public function deleteUtilisateur(int $idUtilisateur): bool
        {
                // 1. Récupérer le chemin de l'image avant suppression
                $row = $this->Odao->execute(
                    "DELETE FROM utilisateur WHERE idUtilisateur = :idUtilisateur",
                    [":idUtilisateur" => $idUtilisateur]
                );

                return true;
        }

        public function setUtilisateur(array $fields, int $idUtilisateur): void
        {
            $setParts = [];
            $params   = [":idUtilisateur" => $idUtilisateur];

            foreach ($fields as $col => $value) {
                $placeholder   = ":" . $col;
                $setParts[]    = "$col = $placeholder";
                $params[$placeholder] = $value;
            }

            $sql = "UPDATE utilisateur SET " . implode(", ", $setParts) . " WHERE idUtilisateur = :idUtilisateur";

            $this->Odao->execute($sql, $params);
        }

        public function getSoldeById($idUtilisateur): ?array
        {
            $sql = "SELECT 
                        solde
                    FROM utilisateur
                    WHERE idUtilisateur = :idUtilisateur";

            $params = [":idUtilisateur" => $idUtilisateur];

            $result = $this->Odao->execute($sql, $params);

            return $result[0] ?? null;
        }
    }
?>