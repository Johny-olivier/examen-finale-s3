<?php
declare(strict_types=1);

namespace app\services;

use app\repositories\DistributionRepository;
use flight\util\Collection;

class DistributionService
{
    private const MARGE_FLOTTANTE = 0.000001;
    public const MODE_DISPATCH_DATE = 'date';
    public const MODE_DISPATCH_QUANTITE = 'quantite';
    public const MODE_DISPATCH_PROPORTIONNEL = 'proportionnel';

    private const LIBELLES_MODES_DISPATCH = [
        self::MODE_DISPATCH_DATE => 'Priorite par date',
        self::MODE_DISPATCH_QUANTITE => 'Priorite par petite quantite',
        self::MODE_DISPATCH_PROPORTIONNEL => 'Proportionnel',
    ];

    private const SCRIPT_SQL_REINITIALISATION = PROJECT_ROOT . '/sql/16022026-02-init-data.sql';

    private DistributionRepository $depotDistribution;

    public function __construct(DistributionRepository $depotDistribution)
    {
        $this->depotDistribution = $depotDistribution;
    }

    /**
     * @return array<string,mixed>
     */
    public function simulerDistribution(string $modeDispatch = self::MODE_DISPATCH_DATE): array
    {
        $modeNormalise = $this->normaliserModeDispatch($modeDispatch);
        $besoinsEnAttente = $this->normaliserLignes(
            $this->depotDistribution->obtenirBesoinsNonDispatcheParPriorite()
        );
        $this->trierBesoinsSelonMode($besoinsEnAttente, $modeNormalise);
        $lignesStock = $this->normaliserLignes($this->depotDistribution->obtenirStockAvecLibelles());

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
        if ($modeNormalise === self::MODE_DISPATCH_PROPORTIONNEL) {
            $besoinsSimules = $this->simulerDistributionProportionnelle($besoinsEnAttente, $stockDeTravail);
        } else {
            foreach ($besoinsEnAttente as $besoin) {
                $quantiteBesoin = (float) $besoin['quantite'];
                $cleStock = $this->genererCleStock((int) $besoin['idProduit'], (int) $besoin['idUnite']);
                $quantiteStockAvant = $stockDeTravail[$cleStock]['quantite'] ?? 0.0;
                $quantiteDistribuable = min($quantiteBesoin, $quantiteStockAvant);

                if (isset($stockDeTravail[$cleStock]) === true) {
                    $stockDeTravail[$cleStock]['quantite'] = $quantiteStockAvant - $quantiteDistribuable;
                }

                $besoinsSimules[] = $this->creerLigneSimulation($besoin, $quantiteStockAvant, $quantiteDistribuable);
            }
        }

        $statistiques = $this->calculerStatistiquesSimulation($besoinsSimules);

        return [
            'besoins' => $besoinsSimules,
            'stock_apres_simulation' => array_values($stockDeTravail),
            'mode_dispatch' => $modeNormalise,
            'libelle_mode_dispatch' => $this->obtenirLibelleModeDispatch($modeNormalise),
            'statistiques' => $statistiques,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function validerDistribution(string $modeDispatch = self::MODE_DISPATCH_DATE): array
    {
        $modeNormalise = $this->normaliserModeDispatch($modeDispatch);
        $this->depotDistribution->demarrerTransaction();

        try {
            $besoinsEnAttente = $this->normaliserLignes(
                $this->depotDistribution->obtenirBesoinsNonDispatcheParPriorite()
            );
            $this->trierBesoinsSelonMode($besoinsEnAttente, $modeNormalise);

            if ($modeNormalise === self::MODE_DISPATCH_PROPORTIONNEL) {
                $resultat = $this->validerDistributionProportionnelle($besoinsEnAttente);
                $resultat['mode_dispatch'] = $modeNormalise;
                $resultat['libelle_mode_dispatch'] = $this->obtenirLibelleModeDispatch($modeNormalise);
                $this->depotDistribution->validerTransaction();
                return $resultat;
            }

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
                $this->depotDistribution->insererDistributionVille(
                    (int) $besoin['idVille'],
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
                'mode_dispatch' => $modeNormalise,
                'libelle_mode_dispatch' => $this->obtenirLibelleModeDispatch($modeNormalise),
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

    public function reinitialiserDonneesPointDepart(): void
    {
        $this->depotDistribution->reinitialiserDonneesDepuisScriptSql(self::SCRIPT_SQL_REINITIALISATION);
    }

    /**
     * @return array<string,string>
     */
    public function obtenirModesDispatchDisponibles(): array
    {
        return self::LIBELLES_MODES_DISPATCH;
    }

    public function normaliserModeDispatch(string $modeDispatch): string
    {
        $mode = strtolower(trim($modeDispatch));
        if (isset(self::LIBELLES_MODES_DISPATCH[$mode]) === false) {
            return self::MODE_DISPATCH_DATE;
        }
        return $mode;
    }

    public function obtenirLibelleModeDispatch(string $modeDispatch): string
    {
        $mode = $this->normaliserModeDispatch($modeDispatch);
        return self::LIBELLES_MODES_DISPATCH[$mode];
    }

    /**
     * @param array<int,array<string,mixed>> $besoins
     */
    private function trierBesoinsSelonMode(array &$besoins, string $modeDispatch): void
    {
        $mode = $this->normaliserModeDispatch($modeDispatch);

        usort($besoins, static function ($a, $b) use ($mode): int {
            $ligneA = $a instanceof Collection ? $a->getData() : (array) $a;
            $ligneB = $b instanceof Collection ? $b->getData() : (array) $b;

            if ($mode === self::MODE_DISPATCH_QUANTITE) {
                $comparaisonQuantite = ((float) ($ligneA['quantite'] ?? 0)) <=> ((float) ($ligneB['quantite'] ?? 0));
                if ($comparaisonQuantite !== 0) {
                    return $comparaisonQuantite;
                }
            }

            $dateA = (string) ($ligneA['date'] ?? '');
            $dateB = (string) ($ligneB['date'] ?? '');
            if ($dateA !== $dateB) {
                return $dateA <=> $dateB;
            }

            return ((int) ($ligneA['idBesoin'] ?? 0)) <=> ((int) ($ligneB['idBesoin'] ?? 0));
        });
    }

    /**
     * @param iterable<int,mixed> $lignes
     * @return array<int,array<string,mixed>>
     */
    private function normaliserLignes(iterable $lignes): array
    {
        $resultat = [];
        foreach ($lignes as $ligne) {
            $resultat[] = $this->normaliserLigne($ligne);
        }
        return $resultat;
    }

    /**
     * @param mixed $ligne
     * @return array<string,mixed>
     */
    private function normaliserLigne($ligne): array
    {
        if ($ligne instanceof Collection) {
            return $ligne->getData();
        }
        if (is_array($ligne) === true) {
            return $ligne;
        }
        if (is_object($ligne) === true) {
            return get_object_vars($ligne);
        }
        return [];
    }

    /**
     * @param array<int,array<string,mixed>> $besoinsEnAttente
     * @param array<string,array<string,mixed>> $stockDeTravail
     * @return array<int,array<string,mixed>>
     */
    private function simulerDistributionProportionnelle(array $besoinsEnAttente, array &$stockDeTravail): array
    {
        $besoinsParCleStock = $this->regrouperBesoinsParCleStock($besoinsEnAttente);
        $lignesParBesoin = [];

        foreach ($besoinsParCleStock as $cleStock => $besoinsAssocies) {
            $quantiteStockAvant = (float) ($stockDeTravail[$cleStock]['quantite'] ?? 0.0);
            $allocationsParBesoin = $this->calculerAllocationsProportionnelles($besoinsAssocies, $quantiteStockAvant);
            $totalDistribue = array_sum($allocationsParBesoin);

            if (isset($stockDeTravail[$cleStock]) === true) {
                $stockDeTravail[$cleStock]['quantite'] = max(0.0, $quantiteStockAvant - $totalDistribue);
            }

            foreach ($besoinsAssocies as $besoin) {
                $idBesoin = (int) ($besoin['idBesoin'] ?? 0);
                $quantiteDistribuable = (float) ($allocationsParBesoin[$idBesoin] ?? 0.0);
                $lignesParBesoin[$idBesoin] = $this->creerLigneSimulation(
                    $besoin,
                    $quantiteStockAvant,
                    $quantiteDistribuable
                );
            }
        }

        $lignesTriees = [];
        foreach ($besoinsEnAttente as $besoin) {
            $idBesoin = (int) ($besoin['idBesoin'] ?? 0);
            if ($idBesoin > 0 && isset($lignesParBesoin[$idBesoin]) === true) {
                $lignesTriees[] = $lignesParBesoin[$idBesoin];
            }
        }

        return $lignesTriees;
    }

    /**
     * @param array<int,array<string,mixed>> $besoinsEnAttente
     * @return array<string,mixed>
     */
    private function validerDistributionProportionnelle(array $besoinsEnAttente): array
    {
        $quantiteTotaleDistribuee = 0.0;
        $besoinsDispatcheComplet = 0;
        $besoinsDispatchePartiel = 0;
        $besoinsNonTouches = 0;

        $besoinsParCleStock = $this->regrouperBesoinsParCleStock($besoinsEnAttente);

        foreach ($besoinsParCleStock as $besoinsAssocies) {
            $premierBesoin = $besoinsAssocies[0] ?? null;
            if ($premierBesoin === null) {
                continue;
            }

            $stockVerrouille = $this->depotDistribution->obtenirStockVerrouille(
                (int) ($premierBesoin['idProduit'] ?? 0),
                (int) ($premierBesoin['idUnite'] ?? 0)
            );

            $idStock = $stockVerrouille['idStock'] ?? null;
            if ($idStock === null) {
                foreach ($besoinsAssocies as $besoin) {
                    if ((float) ($besoin['quantite'] ?? 0) > self::MARGE_FLOTTANTE) {
                        $besoinsNonTouches++;
                    }
                }
                continue;
            }

            $quantiteDisponible = (float) ($stockVerrouille['quantite'] ?? 0.0);
            $allocationsParBesoin = $this->calculerAllocationsProportionnelles($besoinsAssocies, $quantiteDisponible);

            foreach ($besoinsAssocies as $besoin) {
                $idBesoin = (int) ($besoin['idBesoin'] ?? 0);
                $quantiteBesoin = (float) ($besoin['quantite'] ?? 0);
                if ($idBesoin <= 0 || $quantiteBesoin <= self::MARGE_FLOTTANTE) {
                    continue;
                }

                $quantiteDistribuee = (float) ($allocationsParBesoin[$idBesoin] ?? 0.0);
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
                $this->depotDistribution->insererDistributionVille(
                    (int) $besoin['idVille'],
                    (int) $besoin['idProduit'],
                    (int) $besoin['idUnite'],
                    $quantiteDistribuee
                );

                if (($quantiteBesoin - $quantiteDistribuee) <= self::MARGE_FLOTTANTE) {
                    $this->depotDistribution->marquerBesoinDispatche($idBesoin);
                    $besoinsDispatcheComplet++;
                } else {
                    $quantiteRestante = $quantiteBesoin - $quantiteDistribuee;
                    $this->depotDistribution->mettreAJourQuantiteBesoin($idBesoin, $quantiteRestante);
                    $besoinsDispatchePartiel++;
                }

                $quantiteTotaleDistribuee += $quantiteDistribuee;
            }
        }

        return [
            'quantite_totale_distribuee' => $quantiteTotaleDistribuee,
            'besoins_dispatche_complet' => $besoinsDispatcheComplet,
            'besoins_dispatche_partiel' => $besoinsDispatchePartiel,
            'besoins_non_touches' => $besoinsNonTouches,
        ];
    }

    /**
     * @param array<string,mixed> $besoin
     * @return array<string,mixed>
     */
    private function creerLigneSimulation(array $besoin, float $quantiteStockAvant, float $quantiteDistribuable): array
    {
        $quantiteBesoin = (float) ($besoin['quantite'] ?? 0);
        $quantiteRestante = max(0.0, $quantiteBesoin - $quantiteDistribuable);
        $seraDispatche = ($quantiteRestante <= self::MARGE_FLOTTANTE) && ($quantiteDistribuable > self::MARGE_FLOTTANTE);

        return [
            'idBesoin' => (int) ($besoin['idBesoin'] ?? 0),
            'region' => (string) ($besoin['region'] ?? ''),
            'ville' => (string) ($besoin['ville'] ?? ''),
            'produit' => (string) ($besoin['produit'] ?? ''),
            'unite' => (string) ($besoin['unite'] ?? ''),
            'date' => (string) ($besoin['date'] ?? ''),
            'quantite_besoin' => $quantiteBesoin,
            'quantite_stock_avant' => $quantiteStockAvant,
            'quantite_distribuable' => $quantiteDistribuable,
            'quantite_restante' => $quantiteRestante,
            'sera_dispatche' => $seraDispatche,
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $besoinsSimules
     * @return array<string,int>
     */
    private function calculerStatistiquesSimulation(array $besoinsSimules): array
    {
        $totalDispatcheables = 0;
        $totalPartiels = 0;
        $totalNonServis = 0;

        foreach ($besoinsSimules as $ligne) {
            $seraDispatche = (($ligne['sera_dispatche'] ?? false) === true);
            $quantiteDistribuable = (float) ($ligne['quantite_distribuable'] ?? 0);

            if ($seraDispatche === true) {
                $totalDispatcheables++;
            } elseif ($quantiteDistribuable > self::MARGE_FLOTTANTE) {
                $totalPartiels++;
            } else {
                $totalNonServis++;
            }
        }

        return [
            'total_besoins' => count($besoinsSimules),
            'total_dispatcheables' => $totalDispatcheables,
            'total_partiels' => $totalPartiels,
            'total_non_servis' => $totalNonServis,
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $besoinsEnAttente
     * @return array<string,array<int,array<string,mixed>>>
     */
    private function regrouperBesoinsParCleStock(array $besoinsEnAttente): array
    {
        $groupes = [];
        foreach ($besoinsEnAttente as $besoin) {
            $cleStock = $this->genererCleStock((int) ($besoin['idProduit'] ?? 0), (int) ($besoin['idUnite'] ?? 0));
            if (isset($groupes[$cleStock]) === false) {
                $groupes[$cleStock] = [];
            }
            $groupes[$cleStock][] = $besoin;
        }
        return $groupes;
    }

    /**
     * @param array<int,array<string,mixed>> $besoinsAssocies
     * @return array<int,float>
     */
    private function calculerAllocationsProportionnelles(array $besoinsAssocies, float $quantiteStockDisponible): array
    {
        $allocations = [];
        foreach ($besoinsAssocies as $besoin) {
            $idBesoin = (int) ($besoin['idBesoin'] ?? 0);
            if ($idBesoin > 0) {
                $allocations[$idBesoin] = 0.0;
            }
        }

        $stockDisponible = max(0.0, $quantiteStockDisponible);
        if ($stockDisponible < 1.0) {
            return $allocations;
        }

        $quantitesRestantesParBesoin = [];
        foreach ($besoinsAssocies as $besoin) {
            $idBesoin = (int) ($besoin['idBesoin'] ?? 0);
            if ($idBesoin <= 0) {
                continue;
            }
            $quantitesRestantesParBesoin[$idBesoin] = max(0.0, (float) ($besoin['quantite'] ?? 0));
        }

        $nombreActifs = count($quantitesRestantesParBesoin);
        if ($nombreActifs === 0) {
            return $allocations;
        }

        $maximumIterations = max(40, $nombreActifs * 20);
        $iteration = 0;

        while ($stockDisponible >= 1.0 && $iteration < $maximumIterations) {
            $iteration++;

            $totalBesoinsRestants = 0.0;
            foreach ($quantitesRestantesParBesoin as $quantiteRestante) {
                $totalBesoinsRestants += max(0.0, $quantiteRestante);
            }

            if ($totalBesoinsRestants <= self::MARGE_FLOTTANTE) {
                break;
            }

            $distributionsEntieres = [];
            $decimales = [];
            $totalDistribueEntier = 0.0;

            foreach ($besoinsAssocies as $index => $besoin) {
                $idBesoin = (int) ($besoin['idBesoin'] ?? 0);
                if ($idBesoin <= 0) {
                    continue;
                }

                $besoinRestant = max(0.0, (float) ($quantitesRestantesParBesoin[$idBesoin] ?? 0.0));
                if ($besoinRestant <= self::MARGE_FLOTTANTE) {
                    $distributionsEntieres[$idBesoin] = 0.0;
                    $decimales[$idBesoin] = [
                        'reste_decimal' => 0.0,
                        'index' => $index,
                    ];
                    continue;
                }

                $quantiteTheorique = ($besoinRestant * $stockDisponible) / $totalBesoinsRestants;
                $partieEntiere = (float) floor($quantiteTheorique + self::MARGE_FLOTTANTE);
                if ($partieEntiere < 0.0) {
                    $partieEntiere = 0.0;
                }
                if ($partieEntiere > $besoinRestant) {
                    $partieEntiere = (float) floor($besoinRestant + self::MARGE_FLOTTANTE);
                }

                $distributionsEntieres[$idBesoin] = $partieEntiere;
                $decimales[$idBesoin] = [
                    'reste_decimal' => max(0.0, $quantiteTheorique - $partieEntiere),
                    'index' => $index,
                ];
                $totalDistribueEntier += $partieEntiere;
            }

            if ($totalDistribueEntier > self::MARGE_FLOTTANTE) {
                foreach ($distributionsEntieres as $idBesoin => $quantiteDistribuee) {
                    if ($quantiteDistribuee <= self::MARGE_FLOTTANTE) {
                        continue;
                    }
                    $allocations[$idBesoin] = (float) ($allocations[$idBesoin] ?? 0.0) + $quantiteDistribuee;
                    $quantitesRestantesParBesoin[$idBesoin] = max(
                        0.0,
                        (float) ($quantitesRestantesParBesoin[$idBesoin] ?? 0.0) - $quantiteDistribuee
                    );
                    $stockDisponible = max(0.0, $stockDisponible - $quantiteDistribuee);
                }
                continue;
            }

            // Cas de blocage: toutes les parts entieres sont a zero.
            // On attribue 1 unite au plus grand reste decimal pour eviter une boucle infinie.
            $candidats = [];
            foreach ($besoinsAssocies as $index => $besoin) {
                $idBesoin = (int) ($besoin['idBesoin'] ?? 0);
                if ($idBesoin <= 0) {
                    continue;
                }
                $besoinRestant = (float) ($quantitesRestantesParBesoin[$idBesoin] ?? 0.0);
                if ($besoinRestant < 1.0) {
                    continue;
                }
                $candidats[] = [
                    'idBesoin' => $idBesoin,
                    'reste_decimal' => (float) (($decimales[$idBesoin]['reste_decimal'] ?? 0.0)),
                    'index' => $index,
                ];
            }

            if (count($candidats) === 0) {
                break;
            }

            usort($candidats, static function (array $a, array $b): int {
                $comparaisonDecimal = ((float) ($b['reste_decimal'] ?? 0.0)) <=> ((float) ($a['reste_decimal'] ?? 0.0));
                if ($comparaisonDecimal !== 0) {
                    return $comparaisonDecimal;
                }
                return ((int) ($a['index'] ?? 0)) <=> ((int) ($b['index'] ?? 0));
            });

            $idBesoinPrioritaire = (int) ($candidats[0]['idBesoin'] ?? 0);
            if ($idBesoinPrioritaire <= 0) {
                break;
            }

            $allocations[$idBesoinPrioritaire] = (float) ($allocations[$idBesoinPrioritaire] ?? 0.0) + 1.0;
            $quantitesRestantesParBesoin[$idBesoinPrioritaire] = max(
                0.0,
                (float) ($quantitesRestantesParBesoin[$idBesoinPrioritaire] ?? 0.0) - 1.0
            );
            $stockDisponible = max(0.0, $stockDisponible - 1.0);
        }

        return $allocations;
    }
}
