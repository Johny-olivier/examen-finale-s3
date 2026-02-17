<?php
declare(strict_types=1);

namespace app\services;

use app\repositories\RecapitulationRepository;

class RecapitulationService
{
    private RecapitulationRepository $depotRecapitulation;

    public function __construct(RecapitulationRepository $depotRecapitulation)
    {
        $this->depotRecapitulation = $depotRecapitulation;
    }

    /**
     * @return array<string,mixed>
     */
    public function obtenirDonneesRecapitulation(): array
    {
        $resumeBrut = $this->depotRecapitulation->obtenirResumeMontantsBesoins();
        $montantBesoinsTotaux = (float) ($resumeBrut['montant_besoins_totaux'] ?? 0);
        $montantBesoinsSatisfaits = (float) ($resumeBrut['montant_besoins_satisfaits'] ?? 0);
        $montantBesoinsRestants = (float) ($resumeBrut['montant_besoins_restants'] ?? 0);

        if ($montantBesoinsRestants < 0) {
            $montantBesoinsRestants = 0.0;
        }

        $tauxSatisfaction = 0.0;
        if ($montantBesoinsTotaux > 0) {
            $tauxSatisfaction = round(($montantBesoinsSatisfaits / $montantBesoinsTotaux) * 100, 2);
        }

        return [
            'resume' => [
                'montant_besoins_totaux' => $montantBesoinsTotaux,
                'montant_besoins_satisfaits' => $montantBesoinsSatisfaits,
                'montant_besoins_restants' => $montantBesoinsRestants,
                'taux_satisfaction' => $tauxSatisfaction,
                'montant_total_achats' => (float) ($resumeBrut['montant_total_achats'] ?? 0),
                'montant_total_frais' => (float) ($resumeBrut['montant_total_frais'] ?? 0),
            ],
            'villes' => $this->depotRecapitulation->obtenirResumeMontantsParVille(),
            'date_actualisation' => date('Y-m-d H:i:s'),
        ];
    }
}
