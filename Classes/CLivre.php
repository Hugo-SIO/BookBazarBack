<?php
/**
 * CLivre — Classe entité représentant un livre (annonce).
 * Implémente JsonSerializable pour être convertie automatiquement
 * en JSON via json_encode() dans les contrôleurs PHP.
 * Ne contient aucune logique BDD : elle stocke uniquement les données
 * d'une ligne de la table "livre" avec ses jointures.
 */
class CLivre implements JsonSerializable {

    private int      $idLivre;
    private string   $nomLivre;
    private int      $anneeParution;
    private string   $auteur;       // Nom de l'auteur (via JOIN sur table auteur)
    private string   $categorie;    // Nom de la catégorie (via JOIN sur table categorie)
    private string   $vendeur;      // Nom complet du vendeur (CONCAT nom + prénom via JOIN)
    private string   $idVendeur;
    private DateTime $dateAjout;
    private int      $disponible;   // 1 = disponible à l'achat, 0 = déjà vendu
    private string   $description;
    private float    $prix;
    private string   $acheteur;     // Nom de l'acheteur si le livre a été vendu (peut être vide)
    private ?DateTime $dateAchat;   // Nullable : null si le livre n'a pas encore été acheté
    private string   $image;        // Chemin relatif vers l'image (ex: "uploads/livres/xxx.jpg")

    /**
     * Constructeur appelé par CLivres lors du chargement depuis la BDD.
     * Le paramètre $dateAchat est nullable (= null par défaut) car
     * un livre disponible n'a pas encore de date d'achat.
     */
    public function __construct(
        int      $idLivre,
        string   $nomLivre,
        int      $anneeParution,
        string   $categorie,
        string   $auteur,
        string   $vendeur,
        int      $idVendeur,
        DateTime $dateAjout,
        int      $disponible,
        float    $prix,
        string   $description,
        string   $acheteur,
        string   $image,
        ?DateTime $dateAchat = null  // Valeur par défaut null → pas d'achat
    ) {
        $this->idLivre       = $idLivre;
        $this->nomLivre      = $nomLivre;
        $this->anneeParution = $anneeParution;
        $this->categorie     = $categorie;
        $this->auteur        = $auteur;
        $this->vendeur       = $vendeur;
        $this->idVendeur     = $idVendeur;
        $this->dateAjout     = $dateAjout;
        $this->disponible    = $disponible;
        $this->description   = $description;
        $this->prix          = $prix;
        $this->acheteur      = $acheteur;
        $this->dateAchat     = $dateAchat;
        $this->image         = $image;
    }

    // --- Getters publics ---
    // Note : getIdLivre() était private dans le code original → accès impossible depuis l'extérieur.
    // À corriger en prod (passer en public si nécessaire).
    private function getIdLivre(): int      { return $this->idLivre; }
    public function getNomLivre(): string   { return $this->nomLivre; }
    public function getAnneeParution(): int { return $this->anneeParution; }
    public function getCategorie(): int     { return $this->categorie; }
    public function getAuteur(): int        { return $this->auteur; }
    public function getVendeur(): string    { return $this->vendeur; }
    public function getIdVendeur(): int     { return $this->idVendeur; }
    public function getDateAjout(): DateTime { return $this->dateAjout; }
    public function getDisponible(): int    { return $this->disponible; }
    public function getDescription(): string { return $this->description; }
    public function getPrix(): float        { return $this->prix; }
    public function getAcheteur(): string   { return $this->acheteur; }
    public function getDateAchat(): ?DateTime { return $this->dateAchat; }
    public function getImage(): string      { return $this->image; }

    /**
     * Méthode imposée par JsonSerializable.
     * Définit la structure JSON retournée au frontend React
     * pour chaque objet CLivre (utilisé dans les réponses API).
     * dateAjout et dateAchat sont des objets DateTime → ils seront
     * sérialisés automatiquement par json_encode() sous forme d'objet.
     * En prod, il vaudrait mieux les formater manuellement avec ->format('Y-m-d').
     */
    public function jsonSerialize(): mixed {
        return [
            'idLivre'       => $this->idLivre,
            'nomLivre'      => $this->nomLivre,
            'anneeParution' => $this->anneeParution,
            'categorie'     => $this->categorie,
            'auteur'        => $this->auteur,
            'vendeur'       => $this->vendeur,
            'idVendeur'     => $this->idVendeur,
            'dateAjout'     => $this->dateAjout,   // DateTime → sérialisé en objet JSON
            'disponible'    => $this->disponible,
            'desc'          => $this->description,
            'prix'          => $this->prix,
            'acheteur'      => $this->acheteur,
            'dateAchat'     => $this->dateAchat,   // Nullable → null si pas encore acheté
            'image'         => $this->image,
        ];
    }
}
?>