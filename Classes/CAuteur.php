<?php

    class CAuteur implements JsonSerializable{
        private int $idAuteur;
        private string $nomAuteur;

        public function __construct(int $idAuteur, string $nomAuteur){
            $this->idAuteur = $idAuteur;
            $this->nomAuteur = $nomAuteur;
        }
        

        private function getIdAuteur(): int {
            return $this->idAuteur;
        }

        public function getNomAuteur(): string {
            return $this->nomAuteur;
        }

        public function jsonSerialize(): mixed {
            return [
                'idAuteur' => $this->idAuteur,
                'nomAuteur'  => $this->nomAuteur,
            ];
        }
    }
?>