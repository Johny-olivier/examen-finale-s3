<?php
declare(strict_types=1);

namespace app\services;

use app\repositories\DashboardRepository;

class DashboardService
{
    private DashboardRepository $depotDashboard;

    public function __construct(DashboardRepository $depotDashboard)
    {
        $this->depotDashboard = $depotDashboard;
    }

    /**
     * @return array<string,mixed>
     */
    public function obtenirDonneesDashboard(): array
    {
        $resumeBesoinsParVille = $this->depotDashboard->obtenirResumeBesoinsParVille();
        $resumeDonsRecus = $this->depotDashboard->obtenirResumeDonsRecus();
        $resumeDistributionsParVille = $this->depotDashboard->obtenirResumeDistributionsParVille();
        $etatGlobalStock = $this->depotDashboard->obtenirEtatGlobalStock();

        return [
            'resume_besoins_par_ville' => $resumeBesoinsParVille,
            'resume_dons_recus' => $resumeDonsRecus,
            'resume_distributions_par_ville' => $resumeDistributionsParVille,
            'etat_global_stock' => $etatGlobalStock,
            'indicateurs' => [
                'total_besoins' => $this->sommeValeurs($resumeBesoinsParVille, 'total_besoins'),
                'total_besoins_non_dispatche' => $this->sommeValeurs($resumeBesoinsParVille, 'total_non_dispatche'),
                'total_dons_recus' => $this->sommeValeurs($resumeDonsRecus, 'nombre_dons'),
                'quantite_totale_dons_recus' => $this->sommeValeurs($resumeDonsRecus, 'quantite_totale'),
                'quantite_totale_distribuee' => $this->sommeValeurs($resumeDistributionsParVille, 'quantite_distribuee'),
                'quantite_totale_stock' => $this->sommeValeurs($etatGlobalStock, 'quantite'),
            ],
        ];
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
}
