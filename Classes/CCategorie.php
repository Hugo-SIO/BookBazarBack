<?php

    class CCategorie implements JsonSerializable{
        private int $idCategorie;
        private string $nomCategorie;
        

        public function __construct(int $idCategorie, string $nomCategorie){
            $this->idCategorie = $idCategorie;
            $this->nomCategorie = $nomCategorie;

        }
        
        private function getIdCategorie(): int {
            return $this->idCategorie;
        }

        public function getNomCategorie(): string {
            return $this->nomCategorie;
        }

        public function jsonSerialize(): mixed {
            return [
                'idCategorie' => $this->idCategorie,
                'nomCategorie'  => $this->nomCategorie,
            ];
        }
    }


?>