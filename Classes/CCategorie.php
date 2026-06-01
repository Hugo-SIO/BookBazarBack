<?php
/**
 * CCategorie — Classe entité représentant une catégorie de livre.
 * Implémente JsonSerializable pour être convertie automatiquement
 * en JSON lors d'un json_encode() (ex: dans les contrôleurs PHP).
 * Ne contient aucune logique métier ni accès BDD : elle stocke
 * uniquement les données d'une ligne de la table "categorie".
 */
class CCategorie implements JsonSerializable {

    private int $idCategorie;
    private string $nomCategorie;

    /**
     * Constructeur appelé par CCategories lors de la
     * récupération des lignes depuis la base de données.
     */
    public function __construct(int $idCategorie, string $nomCategorie) {
        $this->idCategorie  = $idCategorie;
        $this->nomCategorie = $nomCategorie;
    }

    // Getter privé sur l'id (non accessible depuis l'extérieur)
    private function getIdCategorie(): int {
        return $this->idCategorie;
    }

    public function getNomCategorie(): string {
        return $this->nomCategorie;
    }

    /**
     * Méthode imposée par l'interface JsonSerializable.
     * Définit exactement ce que json_encode() retournera
     * pour chaque objet CCategorie envoyé au frontend React.
     * → { "idCategorie": 1, "nomCategorie": "Roman" }
     */
    public function jsonSerialize(): mixed {
        return [
            'idCategorie'  => $this->idCategorie,
            'nomCategorie' => $this->nomCategorie,
        ];
    }
}
?>