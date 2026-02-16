<?php
declare(strict_types=1);

namespace app\repositories;

use flight\database\PdoWrapper;

class DistributionRepository
{
    private PdoWrapper $baseDeDonnees;

    public function __construct(PdoWrapper $baseDeDonnees)
    {
        $this->baseDeDonnees = $baseDeDonnees;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirBesoinsNonDispatcheParPriorite(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                b.idBesoin,
                b.idProduit,
                b.idUnite,
                b.quantite,
                b.date,
                v.nom AS ville,
                r.nom AS region,
                p.nom AS produit,
                u.nom AS unite
            FROM besoins b
            JOIN ville v ON v.idVille = b.idVille
            JOIN regions r ON r.idRegion = v.idRegion
            JOIN produit p ON p.idProduit = b.idProduit
            JOIN unite u ON u.idUnite = b.idUnite
            WHERE b.status = 'non_dispatche'
            ORDER BY b.date ASC, b.idBesoin ASC"
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirStockAvecLibelles(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                s.idStock,
                s.idProduit,
                s.idUnite,
                s.quantite,
                p.nom AS produit,
                u.nom AS unite
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

    public function decrementerStock(int $idStock, float $quantiteADeduire): void
    {
        $this->baseDeDonnees->runQuery(
            "UPDATE StockBNGRC
            SET quantite = quantite - ?
            WHERE idStock = ?",
            [$quantiteADeduire, $idStock]
        );
    }

    public function enregistrerMouvementDistribution(int $idProduit, int $idUnite, float $quantiteDistribuee): void
    {
        $this->baseDeDonnees->runQuery(
            "INSERT INTO MvtStock(typeMvt, idProduit, idUnite, quantite, dateMvt)
            VALUES ('distribution', ?, ?, ?, NOW())",
            [$idProduit, $idUnite, $quantiteDistribuee]
        );
    }

    public function marquerBesoinDispatche(int $idBesoin): void
    {
        $this->baseDeDonnees->runQuery(
            "UPDATE besoins
            SET status = 'dispatche'
            WHERE idBesoin = ?",
            [$idBesoin]
        );
    }

    public function mettreAJourQuantiteBesoin(int $idBesoin, float $quantiteRestante): void
    {
        $this->baseDeDonnees->runQuery(
            "UPDATE besoins
            SET quantite = ?
            WHERE idBesoin = ?",
            [$quantiteRestante, $idBesoin]
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
