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
                b.idVille,
                b.idProduit,
                b.idUnite,
                LEAST(
                    b.quantite,
                    GREATEST(b.quantiteInitiale - COALESCE(a.totalAchete, 0), 0)
                ) AS quantite,
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
            LEFT JOIN (
                SELECT
                    ac.idBesoin,
                    SUM(ac.quantite) AS totalAchete
                FROM achats ac
                WHERE ac.statut = 'saisi'
                GROUP BY ac.idBesoin
            ) a ON a.idBesoin = b.idBesoin
            WHERE b.status = 'non_dispatche'
              AND LEAST(
                    b.quantite,
                    GREATEST(b.quantiteInitiale - COALESCE(a.totalAchete, 0), 0)
                  ) > 0
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

    public function insererDistributionVille(
        int $idVille,
        int $idProduit,
        int $idUnite,
        float $quantite
    ): void {
        $this->baseDeDonnees->runQuery(
            "INSERT INTO DistributionVille(idVille, idProduit, idUnite, quantite)
            VALUES (?, ?, ?, ?)",
            [$idVille, $idProduit, $idUnite, $quantite]
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

    public function reinitialiserDonneesDepuisScriptSql(string $cheminScriptSql): void
    {
        if (is_file($cheminScriptSql) === false) {
            throw new \RuntimeException('Script SQL de reinitialisation introuvable.');
        }

        $contenuScript = file_get_contents($cheminScriptSql);
        if ($contenuScript === false || trim($contenuScript) === '') {
            throw new \RuntimeException('Script SQL de reinitialisation vide ou illisible.');
        }

        $requetes = $this->extraireRequetesSql($contenuScript);
        foreach ($requetes as $requete) {
            $this->baseDeDonnees->exec($requete);
        }
    }

    /**
     * @return array<int,string>
     */
    private function extraireRequetesSql(string $contenuScript): array
    {
        $contenuSansCommentaires = preg_replace('/^\s*--.*$/m', '', $contenuScript);
        if ($contenuSansCommentaires === null) {
            return [];
        }

        $requetes = [];
        $tampon = '';
        $dansSimpleQuote = false;
        $dansDoubleQuote = false;
        $longueur = strlen($contenuSansCommentaires);

        for ($i = 0; $i < $longueur; $i++) {
            $caractere = $contenuSansCommentaires[$i];
            $precedent = $i > 0 ? $contenuSansCommentaires[$i - 1] : '';

            if ($caractere === "'" && $dansDoubleQuote === false && $precedent !== '\\') {
                $dansSimpleQuote = ($dansSimpleQuote === false);
            } elseif ($caractere === '"' && $dansSimpleQuote === false && $precedent !== '\\') {
                $dansDoubleQuote = ($dansDoubleQuote === false);
            }

            if ($caractere === ';' && $dansSimpleQuote === false && $dansDoubleQuote === false) {
                $requete = trim($tampon);
                if ($requete !== '') {
                    $requetes[] = $requete;
                }
                $tampon = '';
                continue;
            }

            $tampon .= $caractere;
        }

        $requeteFinale = trim($tampon);
        if ($requeteFinale !== '') {
            $requetes[] = $requeteFinale;
        }

        return $requetes;
    }
}
