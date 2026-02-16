<?php
declare(strict_types=1);

namespace app\services;

use app\repositories\DistributionRepository;

class DistributionService
{
    private const MARGE_FLOTTANTE = 0.000001;

    private DistributionRepository $depotDistribution;

    public function __construct(DistributionRepository $depotDistribution)
    {
        $this->depotDistribution = $depotDistribution;
    }

    /**
     * @return array<string,mixed>
     */
    public function simulerDistribution(): array
    {
        $besoinsEnAttente = $this->depotDistribution->obtenirBesoinsNonDispatcheParPriorite();
        $lignesStock = $this->depotDistribution->obtenirStockAvecLibelles();

        $stockDeTravail = [];
        foreach ($lignesStock as $ligneStock) {
            $cleStock = $this->genererCleStock((int) $ligneStock['idProduit'], (int) $ligneStock['idUnite']);
            $stockDeTravail[$cleStock] = [
                'idStock' => (int) $ligneStock['idStock'],
                'idProduit' => (int) $ligneStock['idProduit'],
                'idUnite' => (int) $ligneStock['idUnite'],
                'produit' => (string) $ligneStock['produit'],
                'unite' => (string) $ligneStock['unite'],
                'quantite' => (float) $ligneStock['quantite'],
            ];
        }

        $besoinsSimules = [];
        $totalDispatcheables = 0;
        $totalPartiels = 0;
        $totalNonServis = 0;

        foreach ($besoinsEnAttente as $besoin) {
            $quantiteBesoin = (float) $besoin['quantite'];
            $cleStock = $this->genererCleStock((int) $besoin['idProduit'], (int) $besoin['idUnite']);
            $quantiteStockAvant = $stockDeTravail[$cleStock]['quantite'] ?? 0.0;
            $quantiteDistribuable = min($quantiteBesoin, $quantiteStockAvant);
            $quantiteRestante = max(0.0, $quantiteBesoin - $quantiteDistribuable);

            if (isset($stockDeTravail[$cleStock]) === true) {
                $stockDeTravail[$cleStock]['quantite'] = $quantiteStockAvant - $quantiteDistribuable;
            }

            $seraDispatche = ($quantiteRestante <= self::MARGE_FLOTTANTE);
            if ($seraDispatche === true && $quantiteDistribuable > self::MARGE_FLOTTANTE) {
                $totalDispatcheables++;
            } elseif ($quantiteDistribuable > self::MARGE_FLOTTANTE) {
                $totalPartiels++;
            } else {
                $totalNonServis++;
            }

            $besoinsSimules[] = [
                'idBesoin' => (int) $besoin['idBesoin'],
                'region' => (string) $besoin['region'],
                'ville' => (string) $besoin['ville'],
                'produit' => (string) $besoin['produit'],
                'unite' => (string) $besoin['unite'],
                'date' => (string) $besoin['date'],
                'quantite_besoin' => $quantiteBesoin,
                'quantite_stock_avant' => $quantiteStockAvant,
                'quantite_distribuable' => $quantiteDistribuable,
                'quantite_restante' => $quantiteRestante,
                'sera_dispatche' => $seraDispatche,
            ];
        }

        return [
            'besoins' => $besoinsSimules,
            'stock_apres_simulation' => array_values($stockDeTravail),
            'statistiques' => [
                'total_besoins' => count($besoinsSimules),
                'total_dispatcheables' => $totalDispatcheables,
                'total_partiels' => $totalPartiels,
                'total_non_servis' => $totalNonServis,
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function validerDistribution(): array
    {
        $this->depotDistribution->demarrerTransaction();

        try {
            $besoinsEnAttente = $this->depotDistribution->obtenirBesoinsNonDispatcheParPriorite();

            $quantiteTotaleDistribuee = 0.0;
            $besoinsDispatcheComplet = 0;
            $besoinsDispatchePartiel = 0;
            $besoinsNonTouches = 0;

            foreach ($besoinsEnAttente as $besoin) {
                $quantiteBesoin = (float) $besoin['quantite'];
                if ($quantiteBesoin <= self::MARGE_FLOTTANTE) {
                    continue;
                }

                $stockVerrouille = $this->depotDistribution->obtenirStockVerrouille(
                    (int) $besoin['idProduit'],
                    (int) $besoin['idUnite']
                );

                $idStock = $stockVerrouille['idStock'] ?? null;
                if ($idStock === null) {
                    $besoinsNonTouches++;
                    continue;
                }

                $quantiteDisponible = (float) ($stockVerrouille['quantite'] ?? 0);
                if ($quantiteDisponible <= self::MARGE_FLOTTANTE) {
                    $besoinsNonTouches++;
                    continue;
                }

                $quantiteDistribuee = min($quantiteDisponible, $quantiteBesoin);
                if ($quantiteDistribuee <= self::MARGE_FLOTTANTE) {
                    $besoinsNonTouches++;
                    continue;
                }

                $this->depotDistribution->decrementerStock((int) $idStock, $quantiteDistribuee);
                $this->depotDistribution->enregistrerMouvementDistribution(
                    (int) $besoin['idProduit'],
                    (int) $besoin['idUnite'],
                    $quantiteDistribuee
                );

                if (($quantiteBesoin - $quantiteDistribuee) <= self::MARGE_FLOTTANTE) {
                    $this->depotDistribution->marquerBesoinDispatche((int) $besoin['idBesoin']);
                    $besoinsDispatcheComplet++;
                } else {
                    $quantiteRestante = $quantiteBesoin - $quantiteDistribuee;
                    $this->depotDistribution->mettreAJourQuantiteBesoin((int) $besoin['idBesoin'], $quantiteRestante);
                    $besoinsDispatchePartiel++;
                }

                $quantiteTotaleDistribuee += $quantiteDistribuee;
            }

            $this->depotDistribution->validerTransaction();

            return [
                'quantite_totale_distribuee' => $quantiteTotaleDistribuee,
                'besoins_dispatche_complet' => $besoinsDispatcheComplet,
                'besoins_dispatche_partiel' => $besoinsDispatchePartiel,
                'besoins_non_touches' => $besoinsNonTouches,
            ];
        } catch (\Throwable $exception) {
            if ($this->depotDistribution->estEnTransaction() === true) {
                $this->depotDistribution->annulerTransaction();
            }
            throw $exception;
        }
    }

    private function genererCleStock(int $idProduit, int $idUnite): string
    {
        return $idProduit . ':' . $idUnite;
    }
}
