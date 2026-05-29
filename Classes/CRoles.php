<?php
    require_once __DIR__. '/CRole.php';
    require_once __DIR__. '/Cdao.php';

    class CRoles{

        private array $collRoles = [];
        private Cdao $Odao;

        private static ?CRoles $instance = null;

        private function __construct(){
            try {
                $this->Odao = new Cdao();
                $rows = $this->Odao->execute("SELECT * FROM role");
                foreach($rows as $row){
                    $ORole = new CRole($row['idRole'], $row['nomRole']);
                    $this->collRoles[] = $ORole;
                }
            } catch (PDOException $e) {
                die("Erreur de connexion à la base : " . $e->getMessage());
            }

        }

        public static function getInstance(): CRoles{
            if (self::$instance === null) {
                self::$instance = new CRoles();
            }
            return self::$instance;
        }
        public function getRole(): array {
            return $this->collRoles;
        }
    }
?>