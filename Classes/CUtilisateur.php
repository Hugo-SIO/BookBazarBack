<?php
/**
 * CUtilisateur — Classe entité représentant un utilisateur.
 * Implémente JsonSerializable pour être convertie automatiquement
 * en JSON via json_encode() dans les contrôleurs PHP.
 * Ne contient aucun accès BDD : elle stocke uniquement les données
 * d'une ligne de la table "utilisateur" jointe avec "role".
 */
class CUtilisateur implements JsonSerializable {

    private int    $idUtilisateur;
    private string $nom;
    private string $prenom;
    private string $nomUtilisateur;
    private string $adresseMail;
    private string $motDePasseHash; // Mot de passe déjà haché (jamais le mot de passe en clair)
    private string $Role;           // Nom du rôle (ex: "Admin", "User") récupéré via JOIN
    private int    $idRole;         // Id du rôle pour les listes déroulantes React
    private int    $Solde;          // Solde en euros de l'utilisateur

    /**
     * Constructeur appelé par CUtilisateurs lors du chargement
     * des utilisateurs depuis la base de données.
     * Les données viennent d'un SELECT avec JOIN sur la table role.
     */
    public function __construct(
        int    $idUtilisateur,
        string $nom,
        string $prenom,
        string $nomUtilisateur,
        string $adresseMail,
        string $motDePasseHash,
        string $Role,
        int    $idRole,
        int    $Solde
    ) {
        $this->idUtilisateur  = $idUtilisateur;
        $this->nom            = $nom;
        $this->prenom         = $prenom;
        $this->nomUtilisateur = $nomUtilisateur;
        $this->adresseMail    = $adresseMail;
        $this->motDePasseHash = $motDePasseHash;
        $this->Role           = $Role;
        $this->idRole         = $idRole;
        $this->Solde          = $Solde;
    }

    // --- Getters publics ---
    public function getIdUtilisateur(): int    { return $this->idUtilisateur; }
    public function getNom(): string           { return $this->nom; }
    public function getPrenom(): string        { return $this->prenom; }
    public function getNomUtilisateur(): string{ return $this->nomUtilisateur; }
    public function getAdresseMail(): string   { return $this->adresseMail; }
    public function getMotDePasseHash(): string{ return $this->motDePasseHash; }
    public function getIdRole(): int           { return $this->idRole; }
    public function getRole(): string          { return $this->Role; }
    public function getSolde(): int            { return $this->Solde; }

    /**
     * Méthode imposée par l'interface JsonSerializable.
     * Définit la structure JSON retournée au frontend React
     * pour chaque objet CUtilisateur.
     * Note : motDePasseHash est inclus ici mais ne devrait idéalement
     * pas être exposé côté client — à améliorer en prod.
     */
    public function jsonSerialize(): mixed {
        return [
            'idUtilisateur'  => $this->idUtilisateur,
            'nom'            => $this->nom,
            'prenom'         => $this->prenom,
            'nomUtilisateur' => $this->nomUtilisateur,
            'adresseMail'    => $this->adresseMail,
            'motDePasseHash' => $this->motDePasseHash,
            'Role'           => $this->Role,
            'idRole'         => $this->idRole,
            'Solde'          => $this->Solde,
        ];
    }
}
?>