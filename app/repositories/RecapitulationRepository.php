<?php
declare(strict_types=1);

namespace app\repositories;

use flight\database\PdoWrapper;

class RecapitulationRepository
{
    private PdoWrapper $baseDeDonnees;

    public function __construct(PdoWrapper $baseDeDonnees)
    {
        $this->baseDeDonnees = $baseDeDonnees;
    }

    /**
     * @return array<string,mixed>
     */
    public function obtenirResumeMontantsBesoins(): array
    {
        return $this->baseDeDonnees->fetchRow(
            "SELECT
                COALESCE(SUM(b.quantiteInitiale * p.prixUnitaire), 0) AS montant_besoins_totaux,
                COALESCE(SUM(
                    CASE
                        WHEN b.status IN ('dispatche', 'achete') THEN b.quantiteInitiale * p.prixUnitaire
                        ELSE LEAST(
                            GREATEST(
                                GREATEST(b.quantiteInitiale - b.quantite, 0),
                                COALESCE(a.quantiteAchetee, 0)
                            ),
                            b.quantiteInitiale
                        ) * p.prixUnitaire
                    END
                ), 0) AS montant_besoins_satisfaits,
                COALESCE(SUM(
                    CASE
                        WHEN b.status IN ('dispatche', 'achete') THEN 0
                        ELSE GREATEST(
                            b.quantiteInitiale - LEAST(
                                GREATEST(
                                    GREATEST(b.quantiteInitiale - b.quantite, 0),
                                    COALESCE(a.quantiteAchetee, 0)
                                ),
                                b.quantiteInitiale
                            ),
                            0
                        ) * p.prixUnitaire
                    END
                ), 0) AS montant_besoins_restants,
                (
                    SELECT COALESCE(SUM(ac.montantTotal), 0)
                    FROM achats ac
                    WHERE ac.statut = 'saisi'
                ) AS montant_total_achats,
                (
                    SELECT COALESCE(SUM(ac.montantFrais), 0)
                    FROM achats ac
                    WHERE ac.statut = 'saisi'
                ) AS montant_total_frais
            FROM besoins b
            JOIN produit p ON p.idProduit = b.idProduit
            LEFT JOIN (
                SELECT
                    ac.idBesoin,
                    SUM(ac.quantite) AS quantiteAchetee
                FROM achats ac
                WHERE ac.statut = 'saisi'
                GROUP BY ac.idBesoin
            ) a ON a.idBesoin = b.idBesoin"
        )->getData();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirResumeMontantsParVille(): array
    {
        return $this->baseDeDonnees->fetchAll(
            "SELECT
                v.idVille,
                r.nom AS region,
                v.nom AS ville,
                COUNT(b.idBesoin) AS total_besoins,
                COALESCE(SUM(CASE WHEN b.idBesoin IS NULL THEN 0 ELSE b.quantiteInitiale * p.prixUnitaire END), 0) AS montant_besoins_totaux,
                COALESCE(SUM(
                    CASE
                        WHEN b.idBesoin IS NULL THEN 0
                        WHEN b.status IN ('dispatche', 'achete') THEN b.quantiteInitiale * p.prixUnitaire
                        ELSE LEAST(
                            GREATEST(
                                GREATEST(b.quantiteInitiale - b.quantite, 0),
                                COALESCE(a.quantiteAchetee, 0)
                            ),
                            b.quantiteInitiale
                        ) * p.prixUnitaire
                    END
                ), 0) AS montant_besoins_satisfaits,
                COALESCE(SUM(
                    CASE
                        WHEN b.idBesoin IS NULL THEN 0
                        WHEN b.status IN ('dispatche', 'achete') THEN 0
                        ELSE GREATEST(
                            b.quantiteInitiale - LEAST(
                                GREATEST(
                                    GREATEST(b.quantiteInitiale - b.quantite, 0),
                                    COALESCE(a.quantiteAchetee, 0)
                                ),
                                b.quantiteInitiale
                            ),
                            0
                        ) * p.prixUnitaire
                    END
                ), 0) AS montant_besoins_restants
            FROM ville v
            JOIN regions r ON r.idRegion = v.idRegion
            LEFT JOIN besoins b ON b.idVille = v.idVille
            LEFT JOIN produit p ON p.idProduit = b.idProduit
            LEFT JOIN (
                SELECT
                    ac.idBesoin,
                    SUM(ac.quantite) AS quantiteAchetee
                FROM achats ac
                WHERE ac.statut = 'saisi'
                GROUP BY ac.idBesoin
            ) a ON a.idBesoin = b.idBesoin
            GROUP BY v.idVille, r.nom, v.nom
            ORDER BY r.nom ASC, v.nom ASC"
        );
    }
}
