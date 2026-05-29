<?php

    class CUtilisateur implements JsonSerializable{
        private int $idUtilisateur;
        private string $nom;
        private string $prenom;
        private string $nomUtilisateur;
        private string $adresseMail;
        private string $motDePasseHash;
        private string $Role;
        private int $idRole;
        private int $Solde;

        public function __construct(int $idUtilisateur, string $nom, string $prenom, string $nomUtilisateur, string $adresseMail, string $motDePasseHash, string $Role, int $idRole, int $Solde){
            $this->idUtilisateur = $idUtilisateur;
            $this->nom = $nom;
            $this->prenom = $prenom;
            $this->nomUtilisateur = $nomUtilisateur;
            $this->adresseMail = $adresseMail;
            $this->motDePasseHash = $motDePasseHash;
            $this->Role = $Role;
            $this->idRole = $idRole;
            $this->Solde = $Solde;
        }
        
    
        public function getIdUtilisateur(): int {
            return $this->idUtilisateur;
        }

        public function getNom(): string {
            return $this->nom;
        }

        public function getPrenom(): string {
            return $this->prenom;
        }

        public function getNomUtilisateur(): string {
            return $this->nomUtilisateur;
        }

        public function getAdresseMail(): string {
            return $this->adresseMail;
        }

        public function getMotDePasseHash(): string {
            return $this->motDePasseHash;
        }

        public function getIdRole(): int{
            return $this->idRole;
        }
        public function getRole(): string {
            return $this->Role;
        }

        public function getSolde(): int {
            return $this->Solde;
        }

        public function jsonSerialize(): mixed {
            return [
                'idUtilisateur' => $this->idUtilisateur,
                'nom'  => $this->nom,
                'prenom' => $this->prenom,
                'nomUtilisateur' => $this->nomUtilisateur,
                'adresseMail' => $this->adresseMail,
                'motDePasseHash' => $this->motDePasseHash,
                'Role' => $this->Role,
                'idRole' => $this->idRole,
                'Solde' => $this->Solde
            ];
        }
    }


?>