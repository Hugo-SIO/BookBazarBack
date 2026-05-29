<?php

    class CRole implements JsonSerializable{
        private int $idRole;
        private string $nomRole;


        public function __construct(int $idRole, string $nomRole){
            $this->idRole = $idRole;
            $this->nomRole = $nomRole;

        }
        
        private function getIdRole(): int {
            return $this->idRole;
        }

        public function getNomRole(): string {
            return $this->nomRole;
        }

        public function jsonSerialize(): mixed {
            return [
                'idRole' => $this->idRole,
                'nomRole'  => $this->nomRole,
            ];
        }
    }


?>