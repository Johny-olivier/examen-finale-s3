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
                r.nom AS region,
                v.nom AS ville,
                COUNT(b.idBesoin) AS total_besoins,
                COALESCE(SUM(CASE WHEN b.status = 'dispatche' THEN 1 ELSE 0 END), 0) AS total_dispatche,
                COALESCE(SUM(CASE WHEN b.status = 'non_dispatche' THEN 1 ELSE 0 END), 0) AS total_non_dispatche,
                COALESCE(SUM(CASE WHEN b.status = 'non_dispatche' THEN b.quantite ELSE 0 END), 0) AS quantite_restante
            FROM ville v
            JOIN regions r ON r.idRegion = v.idRegion
            LEFT JOIN besoins b ON b.idVille = v.idVille
            GROUP BY r.nom, v.nom
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
                COALESCE(SUM(CASE WHEN b.status = 'dispatche' THEN b.quantite ELSE 0 END), 0) AS quantite_distribuee,
                COALESCE(SUM(CASE WHEN b.status = 'dispatche' THEN 1 ELSE 0 END), 0) AS besoins_totalement_dispatches,
                COALESCE(SUM(CASE WHEN b.status = 'non_dispatche' THEN 1 ELSE 0 END), 0) AS besoins_en_cours
            FROM ville v
            JOIN regions r ON r.idRegion = v.idRegion
            LEFT JOIN besoins b ON b.idVille = v.idVille
            GROUP BY r.nom, v.nom
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
}
