<?php

    class CLivre implements JsonSerializable{
        private int $idLivre;
        private string $nomLivre;
        private int $anneeParution;
        private string $auteur;
        private string $categorie;
        private string $vendeur;
        private string $idVendeur;
        private DateTime $dateAjout;
        private int $disponible;
        private string $description;
        private float $prix;
        private string $acheteur;
        private ?DateTime $dateAchat;
        private string $image;


        public function __construct(int $idLivre, string $nomLivre, int $anneeParution, string $categorie, string $auteur, string $vendeur, int $idVendeur, DateTime $dateAjout, int $disponible, float $prix, string $description, string $acheteur, string $image, ?DateTime $dateAchat=null){
            $this->idLivre = $idLivre;
            $this->nomLivre = $nomLivre;
            $this->anneeParution = $anneeParution;
            $this->categorie = $categorie;
            $this->auteur = $auteur;
            $this->vendeur = $vendeur;
            $this->idVendeur = $idVendeur;
            $this->dateAjout = $dateAjout;
            $this->disponible = $disponible;
            $this->description = $description;
            $this->prix = $prix;
            $this->acheteur = $acheteur;
            $this->dateAchat = $dateAchat;
            $this->image = $image;
        }
        

        private function getIdLivre(): int {
            return $this->idLivre;
        }

        public function getNomLivre(): string {
            return $this->nomLivre;
        }

        public function getAnneeParution(): int {
            return $this->anneeParution;
        }

        public function getCategorie(): int {
            return $this->categorie;
        }

        public function getAuteur(): int {
            return $this->auteur;
        }

        public function getVendeur(): string {
            return $this->vendeur;
        }

        public function getIdVendeur(): int{
            return $this->idVendeur;
        }

        public function getDateAjout(): DateTime{
            return $this->DateAjout;
        }

        public function getDisponible(): int{
            return $this->disponible;
        }

        public function getDescription(): string {
            return $this->description;
        }

        public function getPrix(): float{
            return $this->prix;
        }

        public function getAcheteur(): string{
            return $this->acheteur;
        }

        public function getDateAchat(): DateTime{
            return $this->dateAchat;
        }

        public function getImage(): string{
            return $this->image;
        }

        public function jsonSerialize(): mixed {
            return [
                'idLivre' => $this->idLivre,
                'nomLivre'  => $this->nomLivre,
                'anneeParution' => $this->anneeParution,
                'categorie' => $this->categorie,
                'auteur' => $this->auteur,
                'vendeur' => $this->vendeur,
                'idVendeur' => $this->idVendeur,
                'dateAjout' => $this->dateAjout,
                'disponible' => $this->disponible,
                'desc' => $this->description,
                'prix' => $this->prix,
                'acheteur' => $this->acheteur,
                'dateAchat' => $this->dateAchat,
                'image' => $this->image,
            ];
        }
    }
?>