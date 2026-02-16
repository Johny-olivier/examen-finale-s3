<?php
declare(strict_types=1);

namespace app\repositories;

use flight\database\PdoWrapper;

class BesoinRepository
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
                c.nom AS categorie,
                p.prixUnitaire
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
    public function obtenirBesoins(?int $idVille = null): array
    {
        $sql = "SELECT
                    b.idBesoin,
                    b.idVille,
                    v.nom AS ville,
                    r.nom AS region,
                    b.idProduit,
                    p.nom AS produit,
                    p.prixUnitaire,
                    b.quantite,
                    b.idUnite,
                    u.nom AS unite,
                    b.status,
                    b.date
                FROM besoins b
                JOIN ville v ON v.idVille = b.idVille
                JOIN regions r ON r.idRegion = v.idRegion
                JOIN produit p ON p.idProduit = b.idProduit
                JOIN unite u ON u.idUnite = b.idUnite";

        $parametres = [];
        if ($idVille !== null && $idVille > 0) {
            $sql .= " WHERE b.idVille = ?";
            $parametres[] = $idVille;
        }

        $sql .= " ORDER BY b.date DESC, b.idBesoin DESC";

        return $this->baseDeDonnees->fetchAll($sql, $parametres);
    }

    public function insererBesoin(
        int $idVille,
        int $idProduit,
        float $quantite,
        int $idUnite,
        ?string $dateBesoin = null
    ): void {
        if ($dateBesoin !== null && $dateBesoin !== '') {
            $this->baseDeDonnees->runQuery(
                "INSERT INTO besoins(idVille, idProduit, quantite, idUnite, status, date)
                VALUES (?, ?, ?, ?, 'non_dispatche', ?)",
                [$idVille, $idProduit, $quantite, $idUnite, $dateBesoin]
            );
            return;
        }

        $this->baseDeDonnees->runQuery(
            "INSERT INTO besoins(idVille, idProduit, quantite, idUnite, status)
            VALUES (?, ?, ?, ?, 'non_dispatche')",
            [$idVille, $idProduit, $quantite, $idUnite]
        );
    }
}
