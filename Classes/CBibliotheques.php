<?php
    require_once __DIR__. '/CBibliotheque.php';
    require_once __DIR__. '/Cdao.php';

    class CBibliotheques{

        private array $collBibliotheques = [];
        private Cdao $Odao;

        private static ?CBibliotheques $instance = null;

        private function __construct(){
            try {
                $this->Odao = new Cdao();
                $rows = $this->Odao->execute("SELECT * FROM bibliothèque");
                foreach($rows as $row){
                    $sql = "SELECT c.*, l.nomLivre, a.nomAuteur, l.image, l.prix, l.dateAchat
                            FROM contenir c 
                            JOIN livre l 
                            ON c.idLivre = l.idLivre
                            JOIN auteur a 
                            ON l.idAuteur = a.idAuteur 
                            WHERE idBibliotheque = :idBibliotheque";
                    $param = [
                        ":idBibliotheque" => $row['idBiblioetheque']
                    ];
                    $collLivres = $this->Odao->execute($sql,$param);
                    $OBibliotheque = new CBibliotheque($row['idBiblioetheque'], $row['idUtilisateur'], $collLivres);
                    $this->collBibliotheques[] = $OBibliotheque;
                }
            } catch (PDOException $e) {
                die("Erreur de connexion à la base : " . $e->getMessage());
            }
        }

        public static function getInstance(): CBibliotheques{
            if (self::$instance === null) {
                self::$instance = new CBibliotheques();
            }
            return self::$instance;
        }

        public function getBibliotheque(): array {
            return $this->collBibliotheques;
        }

        
        public function addLivreBibliotheque(int $idUtilisateur, int $soldeUtilisateur, int $idLivre): bool{
            try {
                $sql = "SELECT idVendeur FROM livre where idLivre = :idLivre";
                $params = [
                    ":idLivre" => $idLivre
                ];
                $result = $this->Odao->execute($sql, $params);
                $idVendeur = $result[0]['idVendeur'];

                $sql = "SELECT prix FROM livre WHERE idLivre = :idLivre";
                $params = [
                    ":idLivre" => $idLivre
                ];
                $result = $this->Odao->execute($sql, $params);
                $prixLivre = $result[0]['prix'];
                
                $sql = "SELECT solde FROM utilisateur WHERE idUtilisateur = :idUtilisateur";
                $params = [
                    "idUtilisateur" => $idVendeur
                ];
                $result = $this->Odao->execute($sql, $params);
                $soldeVendeur = $result[0]['solde'];

                $sql = "UPDATE utilisateur set solde = :solde WHERE idUtilisateur = :idUtilisateur";
                $params = [
                    ":solde" => $soldeUtilisateur - $prixLivre,
                    ":idUtilisateur" =>$idUtilisateur
                ];
                $this->Odao->execute($sql, $params);

                $sql = "UPDATE utilisateur set solde = :solde WHERE idUtilisateur = :idUtilisateur";
                $params = [
                    ":solde" => $soldeVendeur + $prixLivre,
                    ":idUtilisateur" =>$idVendeur
                ];
                $this->Odao->execute($sql, $params);
                $sql = "SELECT idBiblioetheque 
                        FROM bibliothèque 
                        WHERE idUtilisateur = :idUtilisateur";
                $params = [
                    ":idUtilisateur" => $idUtilisateur
                ];
                $result = $this->Odao->execute($sql, $params);
                $idBibliotheque = $result[0]['idBiblioetheque'];

                $sql = "INSERT INTO contenir
                        (idBibliotheque, idLivre)
                        VALUES(:idBibliotheque, :idLivre)";
                $params = [
                    ":idBibliotheque" => $idBibliotheque,
                    ":idLivre" => $idLivre
                ];
                $this->Odao->execute($sql, $params);
                
                $sql= "UPDATE livre
                       set disponible = 0
                       where idLivre = :idLivre";
                $params= [
                    ":idLivre" => $idLivre
                ];
                $this->Odao->execute($sql, $params);

                $sql = "UPDATE livre
                        set dateAchat = now()
                        where idLivre = :idLivre";
                $this->Odao->execute($sql, $params);
                
                return true;
            }catch(PDOException $e){
                die("Erreur lors de l'ajout : " . $e->getMessage());
                return false;
            }
        }
    }
?>