<?php

/**
 * CAuteur — Classe entité représentant un auteur.
 * Elle implémente JsonSerializable pour pouvoir être
 * directement convertie en JSON via json_encode().
 * C'est le modèle de la couche métier : elle ne fait
 * aucun accès BDD, elle contient uniquement les données.
 */
class CAuteur implements JsonSerializable {

    private int $idAuteur;
    private string $nomAuteur;

    /**
     * Constructeur : appelé par CAuteurs lors de la
     * récupération des lignes en base de données.
     */
    public function __construct(int $idAuteur, string $nomAuteur) {
        $this->idAuteur  = $idAuteur;
        $this->nomAuteur = $nomAuteur;
    }

    // Getter privé sur l'id (non exposé à l'extérieur)
    private function getIdAuteur(): int {
        return $this->idAuteur;
    }

    public function getNomAuteur(): string {
        return $this->nomAuteur;
    }

    /**
     * Méthode imposée par JsonSerializable.
     * Définit la structure JSON retournée au frontend React
     * quand on fait json_encode() sur un CAuteur.
     * → { "idAuteur": 1, "nomAuteur": "Victor Hugo" }
     */
    public function jsonSerialize(): mixed {
        return [
            'idAuteur'  => $this->idAuteur,
            'nomAuteur' => $this->nomAuteur,
        ];
    }
}
?>