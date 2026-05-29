<?php

    class CBibliotheque implements JsonSerializable{
        private int $idBibliotheque;
        private int $idUtilisateur;
        private array $livres;

        public function __construct(int $idBibliotheque, int $idUtilisateur, array $livres){
            $this->idBibliotheque = $idBibliotheque;
            $this->idUtilisateur = $idUtilisateur;
            $this->livres = $livres;
        }
        

        private function getIdBibliotheque(): int {
            return $this->idBibliotheque;
        }

        public function getIdUtilisateur(): string {
            return $this->idUtilisateur;
        }

        public function getLivres() : array{
            return $this->livres;
        }

        public function jsonSerialize(): mixed {
            return [
                'idBibliotheque' => $this->idBibliotheque,
                'idUtilisateur'  => $this->idUtilisateur,
                'livres' => $this->livres
            ];
        }
    }
?>