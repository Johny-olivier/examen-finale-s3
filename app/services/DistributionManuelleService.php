<?php
declare(strict_types=1);

namespace app\services;

use app\repositories\DistributionManuelleRepository;

class DistributionManuelleService
{
    private const MARGE_FLOTTANTE = 0.000001;

    private DistributionManuelleRepository $depotDistributionManuelle;

    public function __construct(DistributionManuelleRepository $depotDistributionManuelle)
    {
        $this->depotDistributionManuelle = $depotDistributionManuelle;
    }

    /**
     * @return array<string,mixed>
     */
    public function obtenirDonneesPage(?int $idVilleFiltre = null): array
    {
        $villes = $this->depotDistributionManuelle->obtenirVilles();
        $produits = $this->depotDistributionManuelle->obtenirProduits();
        $unites = $this->depotDistributionManuelle->obtenirUnites();
        $stockDetaille = $this->depotDistributionManuelle->obtenirStockDetaille();
        $distributions = $this->depotDistributionManuelle->obtenirDistributionsManuelles($idVilleFiltre);

        return [
            'villes' => $villes,
            'produits' => $produits,
            'unites' => $unites,
            'stock_detaille' => $stockDetaille,
            'distributions' => $distributions,
            'id_ville_filtre' => $idVilleFiltre ?? 0,
            'resume' => [
                'total_distributions' => count($distributions),
                'quantite_totale' => $this->sommeValeurs($distributions, 'quantite'),
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function distribuerManuellement(
        int $idVille,
        int $idProduit,
        int $idUnite,
        float $quantite,
        ?string $dateDistribution = null
    ): array {
        if ($idVille <= 0) {
            throw new \InvalidArgumentException('Ville invalide.');
        }

        if ($idProduit <= 0) {
            throw new \InvalidArgumentException('Produit invalide.');
        }

        if ($idUnite <= 0) {
            throw new \InvalidArgumentException('Unite invalide.');
        }

        if ($quantite <= self::MARGE_FLOTTANTE) {
            throw new \InvalidArgumentException('La quantite doit etre strictement positive.');
        }

        $this->depotDistributionManuelle->demarrerTransaction();

        try {
            $stockVerrouille = $this->depotDistributionManuelle->obtenirStockVerrouille($idProduit, $idUnite);
            $idStock = $stockVerrouille['idStock'] ?? null;

            if ($idStock === null) {
                throw new \RuntimeException('Stock introuvable pour ce produit et cette unite.');
            }

            $quantiteDisponible = (float) ($stockVerrouille['quantite'] ?? 0);
            if ($quantiteDisponible < ($quantite - self::MARGE_FLOTTANTE)) {
                throw new \RuntimeException(
                    sprintf(
                        'Stock insuffisant: disponible %.2f, demande %.2f.',
                        $quantiteDisponible,
                        $quantite
                    )
                );
            }

            $this->depotDistributionManuelle->decrementerStock((int) $idStock, $quantite);
            $this->depotDistributionManuelle->insererMouvementDistribution($idProduit, $idUnite, $quantite, $dateDistribution);
            $this->depotDistributionManuelle->insererDistributionVille(
                $idVille,
                $idProduit,
                $idUnite,
                $quantite,
                $dateDistribution
            );
            $this->appliquerMiseAJourBesoinsSiMateriel($idVille, $idProduit, $idUnite, $quantite);

            $this->depotDistributionManuelle->validerTransaction();

            return [
                'id_ville' => $idVille,
                'id_produit' => $idProduit,
                'id_unite' => $idUnite,
                'quantite' => $quantite,
            ];
        } catch (\Throwable $exception) {
            if ($this->depotDistributionManuelle->estEnTransaction() === true) {
                $this->depotDistributionManuelle->annulerTransaction();
            }
            throw $exception;
        }
    }

    /**
     * @param array<int,array<string,mixed>> $lignes
     */
    private function sommeValeurs(array $lignes, string $cle): float
    {
        $total = 0.0;
        foreach ($lignes as $ligne) {
            $total += (float) ($ligne[$cle] ?? 0);
        }
        return $total;
    }

    private function appliquerMiseAJourBesoinsSiMateriel(
        int $idVille,
        int $idProduit,
        int $idUnite,
        float $quantiteDistribuee
    ): void {
        if ($this->depotDistributionManuelle->estDistributionArgent($idProduit, $idUnite) === true) {
            return;
        }

        $quantiteRestanteADispatcher = $quantiteDistribuee;
        $besoins = $this->depotDistributionManuelle->obtenirBesoinsNonDispatcheVilleProduitUniteVerrouilles(
            $idVille,
            $idProduit,
            $idUnite
        );

        foreach ($besoins as $besoin) {
            if ($quantiteRestanteADispatcher <= self::MARGE_FLOTTANTE) {
                break;
            }

            $idBesoin = (int) ($besoin['idBesoin'] ?? 0);
            $quantiteBesoin = (float) ($besoin['quantite'] ?? 0);
            if ($idBesoin <= 0 || $quantiteBesoin <= self::MARGE_FLOTTANTE) {
                continue;
            }

            if ($quantiteRestanteADispatcher + self::MARGE_FLOTTANTE >= $quantiteBesoin) {
                $this->depotDistributionManuelle->marquerBesoinDispatche($idBesoin);
                $quantiteRestanteADispatcher -= $quantiteBesoin;
                continue;
            }

            $quantiteRestanteBesoin = $quantiteBesoin - $quantiteRestanteADispatcher;
            if ($quantiteRestanteBesoin <= self::MARGE_FLOTTANTE) {
                $this->depotDistributionManuelle->marquerBesoinDispatche($idBesoin);
            } else {
                $this->depotDistributionManuelle->mettreAJourQuantiteBesoin($idBesoin, $quantiteRestanteBesoin);
            }
            $quantiteRestanteADispatcher = 0.0;
        }
    }
}
