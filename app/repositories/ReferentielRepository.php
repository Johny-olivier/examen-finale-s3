<?php
declare(strict_types=1);

namespace app\repositories;

use flight\database\PdoWrapper;

class ReferentielRepository
{
    private PdoWrapper $baseDeDonnees;

    public function __construct(PdoWrapper $baseDeDonnees)
    {
        $this->baseDeDonnees = $baseDeDonnees;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirRegions(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT idRegion, nom
            FROM regions
            ORDER BY nom ASC"
        );
    }

    public function ajouterRegion(string $nom): void
    {
        $this->baseDeDonnees->runQuery(
            "INSERT INTO regions(nom) VALUES (?)",
            [$nom]
        );
    }

    public function modifierRegion(int $idRegion, string $nom): void
    {
        $this->baseDeDonnees->runQuery(
            "UPDATE regions SET nom = ? WHERE idRegion = ?",
            [$nom, $idRegion]
        );
    }

    public function supprimerRegion(int $idRegion): void
    {
        $this->baseDeDonnees->runQuery(
            "DELETE FROM regions WHERE idRegion = ?",
            [$idRegion]
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirVilles(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                v.idVille,
                v.idRegion,
                v.nom AS ville,
                r.nom AS region
            FROM ville v
            JOIN regions r ON r.idRegion = v.idRegion
            ORDER BY r.nom ASC, v.nom ASC"
        );
    }

    public function ajouterVille(int $idRegion, string $nom): void
    {
        $this->baseDeDonnees->runQuery(
            "INSERT INTO ville(idRegion, nom) VALUES (?, ?)",
            [$idRegion, $nom]
        );
    }

    public function modifierVille(int $idVille, int $idRegion, string $nom): void
    {
        $this->baseDeDonnees->runQuery(
            "UPDATE ville SET idRegion = ?, nom = ? WHERE idVille = ?",
            [$idRegion, $nom, $idVille]
        );
    }

    public function supprimerVille(int $idVille): void
    {
        $this->baseDeDonnees->runQuery(
            "DELETE FROM ville WHERE idVille = ?",
            [$idVille]
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirCategories(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT idCategorie, nom
            FROM categories
            ORDER BY nom ASC"
        );
    }

    public function ajouterCategorie(string $nom): void
    {
        $this->baseDeDonnees->runQuery(
            "INSERT INTO categories(nom) VALUES (?)",
            [$nom]
        );
    }

    public function modifierCategorie(int $idCategorie, string $nom): void
    {
        $this->baseDeDonnees->runQuery(
            "UPDATE categories SET nom = ? WHERE idCategorie = ?",
            [$nom, $idCategorie]
        );
    }

    public function supprimerCategorie(int $idCategorie): void
    {
        $this->baseDeDonnees->runQuery(
            "DELETE FROM categories WHERE idCategorie = ?",
            [$idCategorie]
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirProduits(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                p.idProduit,
                p.nom AS produit,
                p.idCategorie,
                c.nom AS categorie,
                p.prixUnitaire
            FROM produit p
            JOIN categories c ON c.idCategorie = p.idCategorie
            ORDER BY p.nom ASC"
        );
    }

    public function ajouterProduit(string $nom, int $idCategorie, float $prixUnitaire): void
    {
        $this->baseDeDonnees->runQuery(
            "INSERT INTO produit(nom, idCategorie, prixUnitaire)
            VALUES (?, ?, ?)",
            [$nom, $idCategorie, $prixUnitaire]
        );
    }

    public function modifierProduit(int $idProduit, string $nom, int $idCategorie, float $prixUnitaire): void
    {
        $this->baseDeDonnees->runQuery(
            "UPDATE produit
            SET nom = ?, idCategorie = ?, prixUnitaire = ?
            WHERE idProduit = ?",
            [$nom, $idCategorie, $prixUnitaire, $idProduit]
        );
    }

    public function supprimerProduit(int $idProduit): void
    {
        $this->baseDeDonnees->runQuery(
            "DELETE FROM produit WHERE idProduit = ?",
            [$idProduit]
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirUnites(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT idUnite, nom
            FROM unite
            ORDER BY nom ASC"
        );
    }

    public function ajouterUnite(string $nom): void
    {
        $this->baseDeDonnees->runQuery(
            "INSERT INTO unite(nom) VALUES (?)",
            [$nom]
        );
    }

    public function modifierUnite(int $idUnite, string $nom): void
    {
        $this->baseDeDonnees->runQuery(
            "UPDATE unite SET nom = ? WHERE idUnite = ?",
            [$nom, $idUnite]
        );
    }

    public function supprimerUnite(int $idUnite): void
    {
        $this->baseDeDonnees->runQuery(
            "DELETE FROM unite WHERE idUnite = ?",
            [$idUnite]
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirTypesParametreAchat(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT idTypeParametreAchat, code, libelle
            FROM typeParametreAchat
            ORDER BY code ASC"
        );
    }

    public function ajouterTypeParametreAchat(string $code, string $libelle): void
    {
        $this->baseDeDonnees->runQuery(
            "INSERT INTO typeParametreAchat(code, libelle)
            VALUES (?, ?)",
            [$code, $libelle]
        );
    }

    public function modifierTypeParametreAchat(int $idTypeParametreAchat, string $code, string $libelle): void
    {
        $this->baseDeDonnees->runQuery(
            "UPDATE typeParametreAchat
            SET code = ?, libelle = ?
            WHERE idTypeParametreAchat = ?",
            [$code, $libelle, $idTypeParametreAchat]
        );
    }

    public function supprimerTypeParametreAchat(int $idTypeParametreAchat): void
    {
        $this->baseDeDonnees->runQuery(
            "DELETE FROM typeParametreAchat
            WHERE idTypeParametreAchat = ?",
            [$idTypeParametreAchat]
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirParametresAchat(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                p.idParametreAchat,
                p.idTypeParametreAchat,
                t.code AS codeType,
                t.libelle AS libelleType,
                p.valeurDecimal,
                p.dateApplication,
                p.actif
            FROM parametreAchat p
            JOIN typeParametreAchat t ON t.idTypeParametreAchat = p.idTypeParametreAchat
            ORDER BY p.dateApplication DESC, p.idParametreAchat DESC"
        );
    }

    public function ajouterParametreAchat(
        int $idTypeParametreAchat,
        float $valeurDecimal,
        string $dateApplication,
        int $actif
    ): void {
        $this->baseDeDonnees->runQuery(
            "INSERT INTO parametreAchat(idTypeParametreAchat, valeurDecimal, dateApplication, actif)
            VALUES (?, ?, ?, ?)",
            [$idTypeParametreAchat, $valeurDecimal, $dateApplication, $actif]
        );
    }

    public function modifierParametreAchat(
        int $idParametreAchat,
        int $idTypeParametreAchat,
        float $valeurDecimal,
        string $dateApplication,
        int $actif
    ): void {
        $this->baseDeDonnees->runQuery(
            "UPDATE parametreAchat
            SET idTypeParametreAchat = ?, valeurDecimal = ?, dateApplication = ?, actif = ?
            WHERE idParametreAchat = ?",
            [$idTypeParametreAchat, $valeurDecimal, $dateApplication, $actif, $idParametreAchat]
        );
    }

    public function supprimerParametreAchat(int $idParametreAchat): void
    {
        $this->baseDeDonnees->runQuery(
            "DELETE FROM parametreAchat
            WHERE idParametreAchat = ?",
            [$idParametreAchat]
        );
    }
}
