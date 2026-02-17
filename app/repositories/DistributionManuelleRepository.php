<?php
declare(strict_types=1);

namespace app\repositories;

use flight\database\PdoWrapper;

class DistributionManuelleRepository
{
    private PdoWrapper $baseDeDonnees;

    public function __construct(PdoWrapper $baseDeDonnees)
    {
        $this->baseDeDonnees = $baseDeDonnees;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirVilles(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                v.idVille,
                v.nom AS ville,
                r.nom AS region
            FROM ville v
            JOIN regions r ON r.idRegion = v.idRegion
            ORDER BY r.nom ASC, v.nom ASC"
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
     * @return array<int,array<string,mixed>>
     */
    public function obtenirDistributionsManuelles(?int $idVille = null): array
    {
        $sql = "SELECT
                    dvm.idDistribution,
                    dvm.idVille,
                    r.nom AS region,
                    v.nom AS ville,
                    dvm.idProduit,
                    p.nom AS produit,
                    dvm.idUnite,
                    u.nom AS unite,
                    dvm.quantite,
                    dvm.dateDistribution
                FROM DistributionVille dvm
                JOIN ville v ON v.idVille = dvm.idVille
                JOIN regions r ON r.idRegion = v.idRegion
                JOIN produit p ON p.idProduit = dvm.idProduit
                JOIN unite u ON u.idUnite = dvm.idUnite";

        $parametres = [];
        if ($idVille !== null && $idVille > 0) {
            $sql .= " WHERE dvm.idVille = ?";
            $parametres[] = $idVille;
        }

        $sql .= " ORDER BY dvm.dateDistribution DESC, dvm.idDistribution DESC";

        return $this->baseDeDonnees->fetchAll($sql, $parametres);
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

    public function insererMouvementDistribution(int $idProduit, int $idUnite, float $quantiteDistribuee, ?string $dateMouvement = null): void
    {
        if ($dateMouvement !== null && $dateMouvement !== '') {
            $this->baseDeDonnees->runQuery(
                "INSERT INTO MvtStock(typeMvt, idProduit, idUnite, quantite, dateMvt)
                VALUES ('distribution', ?, ?, ?, ?)",
                [$idProduit, $idUnite, $quantiteDistribuee, $dateMouvement]
            );
            return;
        }

        $this->baseDeDonnees->runQuery(
            "INSERT INTO MvtStock(typeMvt, idProduit, idUnite, quantite, dateMvt)
            VALUES ('distribution', ?, ?, ?, NOW())",
            [$idProduit, $idUnite, $quantiteDistribuee]
        );
    }

    public function insererDistributionVille(
        int $idVille,
        int $idProduit,
        int $idUnite,
        float $quantite,
        ?string $dateDistribution = null
    ): void {
        if ($dateDistribution !== null && $dateDistribution !== '') {
            $this->baseDeDonnees->runQuery(
                "INSERT INTO DistributionVille(idVille, idProduit, idUnite, quantite, dateDistribution)
                VALUES (?, ?, ?, ?, ?)",
                [$idVille, $idProduit, $idUnite, $quantite, $dateDistribution]
            );
            return;
        }

        $this->baseDeDonnees->runQuery(
            "INSERT INTO DistributionVille(idVille, idProduit, idUnite, quantite)
            VALUES (?, ?, ?, ?)",
            [$idVille, $idProduit, $idUnite, $quantite]
        );
    }

    public function estDistributionArgent(int $idProduit, int $idUnite): bool
    {
        $total = (int) $this->baseDeDonnees->fetchField(
            "SELECT COUNT(1)
            FROM produit p
            JOIN unite u ON u.idUnite = ?
            WHERE p.idProduit = ?
              AND p.nom = 'Argent'
              AND u.nom = 'Ar'",
            [$idUnite, $idProduit]
        );

        return $total > 0;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirBesoinsNonDispatcheVilleProduitUniteVerrouilles(
        int $idVille,
        int $idProduit,
        int $idUnite
    ): array {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                b.idBesoin,
                LEAST(
                    b.quantite,
                    GREATEST(b.quantiteInitiale - COALESCE(a.totalAchete, 0), 0)
                ) AS quantite
            FROM besoins b
            LEFT JOIN (
                SELECT
                    ac.idBesoin,
                    SUM(ac.quantite) AS totalAchete
                FROM achats ac
                WHERE ac.statut = 'saisi'
                GROUP BY ac.idBesoin
            ) a ON a.idBesoin = b.idBesoin
            WHERE b.idVille = ?
              AND b.idProduit = ?
              AND b.idUnite = ?
              AND b.status = 'non_dispatche'
              AND LEAST(
                    b.quantite,
                    GREATEST(b.quantiteInitiale - COALESCE(a.totalAchete, 0), 0)
                  ) > 0
            ORDER BY b.date ASC, b.idBesoin ASC
            FOR UPDATE",
            [$idVille, $idProduit, $idUnite]
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
