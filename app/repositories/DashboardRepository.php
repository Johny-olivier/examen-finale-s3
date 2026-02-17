<?php
declare(strict_types=1);

namespace app\repositories;

use flight\database\PdoWrapper;

class DashboardRepository
{
    private PdoWrapper $baseDeDonnees;

    public function __construct(PdoWrapper $baseDeDonnees)
    {
        $this->baseDeDonnees = $baseDeDonnees;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirResumeBesoinsParVille(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                v.idVille,
                r.nom AS region,
                v.nom AS ville,
                COUNT(b.idBesoin) AS total_besoins,
                COALESCE(SUM(CASE WHEN b.status IN ('dispatche', 'achete') THEN 1 ELSE 0 END), 0) AS total_dispatche,
                COALESCE(SUM(CASE WHEN b.status = 'non_dispatche' THEN 1 ELSE 0 END), 0) AS total_non_dispatche,
                COALESCE(SUM(
                    CASE
                        WHEN b.status = 'non_dispatche' THEN LEAST(
                            b.quantite,
                            GREATEST(b.quantiteInitiale - COALESCE(a.totalAchete, 0), 0)
                        )
                        ELSE 0
                    END
                ), 0) AS quantite_restante
            FROM ville v
            JOIN regions r ON r.idRegion = v.idRegion
            LEFT JOIN besoins b ON b.idVille = v.idVille
            LEFT JOIN (
                SELECT
                    ac.idBesoin,
                    SUM(ac.quantite) AS totalAchete
                FROM achats ac
                WHERE ac.statut = 'saisi'
                GROUP BY ac.idBesoin
            ) a ON a.idBesoin = b.idBesoin
            GROUP BY v.idVille, r.nom, v.nom
            ORDER BY r.nom ASC, v.nom ASC"
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirResumeDonsRecus(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                p.idProduit,
                p.nom AS produit,
                u.nom AS unite,
                COUNT(d.idDon) AS nombre_dons,
                COALESCE(SUM(d.quantite), 0) AS quantite_totale,
                MAX(d.dateDon) AS dernier_don
            FROM dons d
            JOIN produit p ON p.idProduit = d.idProduit
            JOIN unite u ON u.idUnite = d.idUnite
            GROUP BY p.idProduit, p.nom, u.nom
            ORDER BY p.nom ASC, u.nom ASC"
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirResumeDistributionsParVille(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                r.nom AS region,
                v.nom AS ville,
                COALESCE(dist.quantite_distribuee, 0) AS quantite_distribuee,
                COALESCE(dist.total_distributions, 0) AS total_distributions,
                COALESCE(dist.montant_argent_distribue, 0) AS montant_argent_distribue,
                COALESCE(bes.besoins_totalement_dispatches, 0) AS besoins_totalement_dispatches,
                COALESCE(bes.besoins_en_cours, 0) AS besoins_en_cours
            FROM ville v
            JOIN regions r ON r.idRegion = v.idRegion
            LEFT JOIN (
                SELECT
                    dvm.idVille,
                    COALESCE(SUM(CASE WHEN p.nom = 'Argent' AND u.nom = 'Ar' THEN 0 ELSE dvm.quantite END), 0) AS quantite_distribuee,
                    COUNT(dvm.idDistribution) AS total_distributions,
                    COALESCE(SUM(CASE WHEN p.nom = 'Argent' AND u.nom = 'Ar' THEN dvm.quantite ELSE 0 END), 0) AS montant_argent_distribue
                FROM DistributionVille dvm
                JOIN produit p ON p.idProduit = dvm.idProduit
                JOIN unite u ON u.idUnite = dvm.idUnite
                GROUP BY dvm.idVille
            ) dist ON dist.idVille = v.idVille
            LEFT JOIN (
                SELECT
                    b.idVille,
                    COALESCE(SUM(CASE WHEN b.status IN ('dispatche', 'achete') THEN 1 ELSE 0 END), 0) AS besoins_totalement_dispatches,
                    COALESCE(SUM(CASE WHEN b.status = 'non_dispatche' THEN 1 ELSE 0 END), 0) AS besoins_en_cours
                FROM besoins b
                GROUP BY b.idVille
            ) bes ON bes.idVille = v.idVille
            ORDER BY r.nom ASC, v.nom ASC"
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirEtatGlobalStock(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                s.idProduit,
                p.nom AS produit,
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
    public function obtenirDetailsBesoinsParVille(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                b.idBesoin,
                b.idVille,
                r.nom AS region,
                v.nom AS ville,
                p.nom AS produit,
                u.nom AS unite,
                CASE
                    WHEN b.status = 'non_dispatche' THEN LEAST(
                        b.quantite,
                        GREATEST(b.quantiteInitiale - COALESCE(a.totalAchete, 0), 0)
                    )
                    ELSE b.quantiteInitiale
                END AS quantite,
                b.status,
                b.date
            FROM besoins b
            JOIN ville v ON v.idVille = b.idVille
            JOIN regions r ON r.idRegion = v.idRegion
            JOIN produit p ON p.idProduit = b.idProduit
            JOIN unite u ON u.idUnite = b.idUnite
            LEFT JOIN (
                SELECT
                    ac.idBesoin,
                    SUM(ac.quantite) AS totalAchete
                FROM achats ac
                WHERE ac.statut = 'saisi'
                GROUP BY ac.idBesoin
            ) a ON a.idBesoin = b.idBesoin
            ORDER BY v.idVille ASC, b.date DESC, b.idBesoin DESC"
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirDetailsDistributionsParVille(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                dvm.idDistribution,
                dvm.idVille,
                r.nom AS region,
                v.nom AS ville,
                p.nom AS produit,
                u.nom AS unite,
                dvm.quantite,
                dvm.dateDistribution AS date
            FROM DistributionVille dvm
            JOIN ville v ON v.idVille = dvm.idVille
            JOIN regions r ON r.idRegion = v.idRegion
            JOIN produit p ON p.idProduit = dvm.idProduit
            JOIN unite u ON u.idUnite = dvm.idUnite
            ORDER BY v.idVille ASC, dvm.dateDistribution DESC, dvm.idDistribution DESC"
        );
    }
}
