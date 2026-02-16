<?php
declare(strict_types=1);

namespace app\repositories;

use flight\database\PdoWrapper;

class StockRepository
{
    private PdoWrapper $baseDeDonnees;

    public function __construct(PdoWrapper $baseDeDonnees)
    {
        $this->baseDeDonnees = $baseDeDonnees;
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
                c.nom AS categorie
            FROM produit p
            JOIN categories c ON c.idCategorie = p.idCategorie
            ORDER BY p.nom ASC"
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

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirStockDetaille(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                s.idStock,
                s.idProduit,
                p.nom AS produit,
                s.idUnite,
                u.nom AS unite,
                s.quantite
            FROM StockBNGRC s
            JOIN produit p ON p.idProduit = s.idProduit
            JOIN unite u ON u.idUnite = s.idUnite
            ORDER BY p.nom ASC, u.nom ASC"
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function obtenirStockVerrouille(int $idProduit, int $idUnite): array
    {
        return $this->baseDeDonnees->fetchRow(
            "SELECT idStock, quantite
            FROM StockBNGRC
            WHERE idProduit = ? AND idUnite = ?
            LIMIT 1
            FOR UPDATE",
            [$idProduit, $idUnite]
        )->getData();
    }

    public function insererStock(int $idProduit, int $idUnite, float $quantite): void
    {
        $this->baseDeDonnees->runQuery(
            "INSERT INTO StockBNGRC(idProduit, idUnite, quantite)
            VALUES (?, ?, ?)",
            [$idProduit, $idUnite, $quantite]
        );
    }

    public function incrementerStock(int $idStock, float $quantiteAAjouter): void
    {
        $this->baseDeDonnees->runQuery(
            "UPDATE StockBNGRC
            SET quantite = quantite + ?
            WHERE idStock = ?",
            [$quantiteAAjouter, $idStock]
        );
    }

    public function insererMouvementDon(int $idProduit, int $idUnite, float $quantite): void
    {
        $this->baseDeDonnees->runQuery(
            "INSERT INTO MvtStock(typeMvt, idProduit, idUnite, quantite, dateMvt)
            VALUES ('don', ?, ?, ?, NOW())",
            [$idProduit, $idUnite, $quantite]
        );
    }

    public function demarrerTransaction(): void
    {
        $this->baseDeDonnees->beginTransaction();
    }

    public function validerTransaction(): void
    {
        $this->baseDeDonnees->commit();
    }

    public function annulerTransaction(): void
    {
        $this->baseDeDonnees->rollBack();
    }

    public function estEnTransaction(): bool
    {
        return $this->baseDeDonnees->inTransaction();
    }
}
