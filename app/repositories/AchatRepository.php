<?php
declare(strict_types=1);

namespace app\repositories;

use flight\database\PdoWrapper;

class AchatRepository
{
    private PdoWrapper $baseDeDonnees;
    private ?string $typeMouvementAchatCompatible = null;

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
    public function obtenirBesoinsRestantsPourAchat(?int $idVille = null): array
    {
        $sql = "SELECT
                    b.idBesoin,
                    b.idVille,
                    r.nom AS region,
                    v.nom AS ville,
                    b.idProduit,
                    p.nom AS produit,
                    b.idUnite,
                    u.nom AS unite,
                    b.quantiteInitiale AS quantiteBesoin,
                    p.prixUnitaire,
                    COALESCE(s.quantite, 0) AS quantiteStockRestante,
                    COALESCE(a.totalAchete, 0) AS quantiteDejaAchetee,
                    LEAST(
                        b.quantite,
                        GREATEST(b.quantiteInitiale - COALESCE(a.totalAchete, 0), 0)
                    ) AS quantiteRestanteAAcheter,
                    b.date
                FROM besoins b
                JOIN ville v ON v.idVille = b.idVille
                JOIN regions r ON r.idRegion = v.idRegion
                JOIN produit p ON p.idProduit = b.idProduit
                JOIN unite u ON u.idUnite = b.idUnite
                LEFT JOIN StockBNGRC s
                    ON s.idProduit = b.idProduit
                    AND s.idUnite = b.idUnite
                LEFT JOIN (
                    SELECT
                        ac.idBesoin,
                        SUM(ac.quantite) AS totalAchete
                    FROM achats ac
                    WHERE ac.statut = 'saisi'
                    GROUP BY ac.idBesoin
                ) a ON a.idBesoin = b.idBesoin
                WHERE b.status = 'non_dispatche'";

        $parametres = [];
        if ($idVille !== null && $idVille > 0) {
            $sql .= " AND b.idVille = ?";
            $parametres[] = $idVille;
        }

        $sql .= " ORDER BY b.date ASC, b.idBesoin ASC";

        return $this->baseDeDonnees->fetchAll($sql, $parametres);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenirAchats(?int $idVille = null): array
    {
        $sql = "SELECT
                    a.idAchat,
                    a.idBesoin,
                    a.idVille,
                    r.nom AS region,
                    v.nom AS ville,
                    a.idProduit,
                    p.nom AS produit,
                    a.idUnite,
                    u.nom AS unite,
                    a.quantite,
                    a.prixUnitaire,
                    a.tauxFrais,
                    a.montantSousTotal,
                    a.montantFrais,
                    a.montantTotal,
                    a.dateAchat,
                    a.statut,
                    a.commentaire
                FROM achats a
                JOIN ville v ON v.idVille = a.idVille
                JOIN regions r ON r.idRegion = v.idRegion
                JOIN produit p ON p.idProduit = a.idProduit
                JOIN unite u ON u.idUnite = a.idUnite";

        $parametres = [];
        if ($idVille !== null && $idVille > 0) {
            $sql .= " WHERE a.idVille = ?";
            $parametres[] = $idVille;
        }

        $sql .= " ORDER BY a.dateAchat DESC, a.idAchat DESC";

        return $this->baseDeDonnees->fetchAll($sql, $parametres);
    }

    public function obtenirTauxFraisAchatActif(): float
    {
        $ligne = $this->baseDeDonnees->fetchRow(
            "SELECT p.valeurDecimal
            FROM parametreAchat p
            JOIN typeParametreAchat t ON t.idTypeParametreAchat = p.idTypeParametreAchat
            WHERE t.code = 'frais_achat_pourcentage'
              AND p.actif = 1
            ORDER BY p.dateApplication DESC, p.idParametreAchat DESC
            LIMIT 1"
        )->getData();

        return (float) ($ligne['valeurDecimal'] ?? 0);
    }

    /**
     * @return array<string,mixed>
     */
    public function obtenirProduitArgentUniteAr(): array
    {
        return $this->baseDeDonnees->fetchRow(
            "SELECT
                p.idProduit,
                u.idUnite
            FROM produit p
            JOIN unite u ON u.nom = 'Ar'
            WHERE p.nom = 'Argent'
            LIMIT 1"
        )->getData();
    }

    public function obtenirSoldeArgentDistribueDisponible(
        int $idProduitArgent,
        int $idUniteArgent,
        ?int $idVille = null
    ): float
    {
        $sqlArgentDistribue = "SELECT COALESCE(SUM(dvm.quantite), 0)
            FROM DistributionVille dvm
            WHERE dvm.idProduit = ?
              AND dvm.idUnite = ?";
        $parametresArgentDistribue = [$idProduitArgent, $idUniteArgent];

        $sqlAchats = "SELECT COALESCE(SUM(a.montantTotal), 0)
            FROM achats a
            WHERE a.statut = 'saisi'";
        $parametresAchats = [];

        if ($idVille !== null && $idVille > 0) {
            $sqlArgentDistribue .= " AND dvm.idVille = ?";
            $parametresArgentDistribue[] = $idVille;

            $sqlAchats .= " AND a.idVille = ?";
            $parametresAchats[] = $idVille;
        }

        $montantArgentDistribue = (float) $this->baseDeDonnees->fetchField(
            $sqlArgentDistribue,
            $parametresArgentDistribue
        );

        $montantTotalAchats = (float) $this->baseDeDonnees->fetchField(
            $sqlAchats,
            $parametresAchats
        );

        return max(0.0, $montantArgentDistribue - $montantTotalAchats);
    }

    /**
     * @return array<string,mixed>
     */
    public function obtenirBesoinVerrouille(int $idBesoin): array
    {
        return $this->baseDeDonnees->fetchRow(
            "SELECT
                b.idBesoin,
                b.idVille,
                b.idProduit,
                b.idUnite,
                b.quantite,
                b.quantiteInitiale,
                b.status,
                p.prixUnitaire
            FROM besoins b
            JOIN produit p ON p.idProduit = b.idProduit
            WHERE b.idBesoin = ?
            LIMIT 1
            FOR UPDATE",
            [$idBesoin]
        )->getData();
    }

    public function obtenirQuantiteAcheteeBesoinVerrouille(int $idBesoin): float
    {
        return (float) $this->baseDeDonnees->fetchField(
            "SELECT COALESCE(SUM(a.quantite), 0)
            FROM achats a
            WHERE a.idBesoin = ?
              AND a.statut = 'saisi'",
            [$idBesoin]
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

    public function obtenirMontantArgentDistribueVilleVerrouille(
        int $idVille,
        int $idProduitArgent,
        int $idUniteArgent
    ): float
    {
        return (float) $this->baseDeDonnees->fetchField(
            "SELECT COALESCE(SUM(dvm.quantite), 0)
            FROM DistributionVille dvm
            WHERE dvm.idVille = ?
              AND dvm.idProduit = ?
              AND dvm.idUnite = ?",
            [$idVille, $idProduitArgent, $idUniteArgent]
        );
    }

    public function obtenirMontantTotalAchatsVilleVerrouille(int $idVille): float
    {
        return (float) $this->baseDeDonnees->fetchField(
            "SELECT COALESCE(SUM(a.montantTotal), 0)
            FROM achats a
            WHERE a.idVille = ?
              AND a.statut = 'saisi'",
            [$idVille]
        );
    }

    public function insererAchat(
        int $idBesoin,
        int $idVille,
        int $idProduit,
        int $idUnite,
        float $quantite,
        float $prixUnitaire,
        float $tauxFrais,
        float $montantSousTotal,
        float $montantFrais,
        float $montantTotal,
        ?string $dateAchat = null,
        ?string $commentaire = null
    ): int {
        if ($dateAchat !== null && $dateAchat !== '') {
            $this->baseDeDonnees->runQuery(
                "INSERT INTO achats(
                    idBesoin, idVille, idProduit, idUnite, quantite,
                    prixUnitaire, tauxFrais, montantSousTotal, montantFrais, montantTotal,
                    dateAchat, statut, commentaire
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'saisi', ?)",
                [
                    $idBesoin,
                    $idVille,
                    $idProduit,
                    $idUnite,
                    $quantite,
                    $prixUnitaire,
                    $tauxFrais,
                    $montantSousTotal,
                    $montantFrais,
                    $montantTotal,
                    $dateAchat,
                    $commentaire,
                ]
            );
        } else {
            $this->baseDeDonnees->runQuery(
                "INSERT INTO achats(
                    idBesoin, idVille, idProduit, idUnite, quantite,
                    prixUnitaire, tauxFrais, montantSousTotal, montantFrais, montantTotal,
                    statut, commentaire
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'saisi', ?)",
                [
                    $idBesoin,
                    $idVille,
                    $idProduit,
                    $idUnite,
                    $quantite,
                    $prixUnitaire,
                    $tauxFrais,
                    $montantSousTotal,
                    $montantFrais,
                    $montantTotal,
                    $commentaire,
                ]
            );
        }

        return (int) $this->baseDeDonnees->lastInsertId();
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

    public function insererMouvementAchat(int $idProduit, int $idUnite, float $quantite, ?string $dateMouvement = null): void
    {
        $typeMouvement = $this->obtenirTypeMouvementAchatCompatible();

        if ($dateMouvement !== null && $dateMouvement !== '') {
            $this->baseDeDonnees->runQuery(
                "INSERT INTO MvtStock(typeMvt, idProduit, idUnite, quantite, dateMvt)
                VALUES (?, ?, ?, ?, ?)",
                [$typeMouvement, $idProduit, $idUnite, $quantite, $dateMouvement]
            );
            return;
        }

        $this->baseDeDonnees->runQuery(
            "INSERT INTO MvtStock(typeMvt, idProduit, idUnite, quantite, dateMvt)
            VALUES (?, ?, ?, ?, NOW())",
            [$typeMouvement, $idProduit, $idUnite, $quantite]
        );
    }

    private function obtenirTypeMouvementAchatCompatible(): string
    {
        if ($this->typeMouvementAchatCompatible !== null) {
            return $this->typeMouvementAchatCompatible;
        }

        $typeMouvement = 'achat';

        try {
            $ligne = $this->baseDeDonnees->fetchRow(
                "SELECT COLUMNS.COLUMN_TYPE AS typeColonne
                FROM information_schema.COLUMNS
                WHERE COLUMNS.TABLE_SCHEMA = DATABASE()
                  AND COLUMNS.TABLE_NAME = 'MvtStock'
                  AND COLUMNS.COLUMN_NAME = 'typeMvt'"
            )->getData();

            $typeColonne = strtolower((string) ($ligne['typeColonne'] ?? ''));
            if ($typeColonne !== '' && strpos($typeColonne, "'achat'") === false) {
                $typeMouvement = 'don';
                error_log(
                    "Schema MvtStock obsolète: enum typeMvt sans valeur 'achat'. " .
                    "Fallback automatique sur typeMvt='don'. " .
                    "Corriger la table pour inclure 'achat'."
                );
            }
        } catch (\Throwable $exception) {
            // Si la verification du schema echoue, on conserve le comportement metier attendu.
            $typeMouvement = 'achat';
        }

        $this->typeMouvementAchatCompatible = $typeMouvement;
        return $typeMouvement;
    }

    public function marquerBesoinAchete(int $idBesoin): void
    {
        $this->baseDeDonnees->runQuery(
            "UPDATE besoins
            SET status = 'achete'
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
