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
        $detailsBesoinsParVille = $this->depotDashboard->obtenirDetailsBesoinsParVille();
        $detailsDistributionsParVille = $this->depotDashboard->obtenirDetailsDistributionsParVille();
        $detailsParVille = $this->construireDetailsParVille(
            $resumeBesoinsParVille,
            $detailsBesoinsParVille,
            $detailsDistributionsParVille
        );

        return [
            'resume_besoins_par_ville' => $resumeBesoinsParVille,
            'resume_dons_recus' => $resumeDonsRecus,
            'resume_distributions_par_ville' => $resumeDistributionsParVille,
            'etat_global_stock' => $etatGlobalStock,
            'details_par_ville' => $detailsParVille,
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

    /**
     * @param array<int,array<string,mixed>> $resumeBesoinsParVille
     * @param array<int,array<string,mixed>> $detailsBesoinsParVille
     * @param array<int,array<string,mixed>> $detailsDistributionsParVille
     * @return array<int,array<string,mixed>>
     */
    private function construireDetailsParVille(
        array $resumeBesoinsParVille,
        array $detailsBesoinsParVille,
        array $detailsDistributionsParVille
    ): array {
        $detailsParVille = [];

        foreach ($resumeBesoinsParVille as $ligne) {
            $idVille = (int) ($ligne['idVille'] ?? 0);
            if ($idVille <= 0) {
                continue;
            }

            $detailsParVille[$idVille] = [
                'idVille' => $idVille,
                'region' => (string) ($ligne['region'] ?? ''),
                'ville' => (string) ($ligne['ville'] ?? ''),
                'besoins' => [],
                'dons_distribues' => [],
            ];
        }

        foreach ($detailsBesoinsParVille as $besoin) {
            $idVille = (int) ($besoin['idVille'] ?? 0);
            if ($idVille <= 0) {
                continue;
            }

            if (!isset($detailsParVille[$idVille])) {
                $detailsParVille[$idVille] = [
                    'idVille' => $idVille,
                    'region' => (string) ($besoin['region'] ?? ''),
                    'ville' => (string) ($besoin['ville'] ?? ''),
                    'besoins' => [],
                    'dons_distribues' => [],
                ];
            }

            $detailsParVille[$idVille]['besoins'][] = $besoin;
        }

        foreach ($detailsDistributionsParVille as $distribution) {
            $idVille = (int) ($distribution['idVille'] ?? 0);
            if ($idVille <= 0) {
                continue;
            }

            if (!isset($detailsParVille[$idVille])) {
                $detailsParVille[$idVille] = [
                    'idVille' => $idVille,
                    'region' => (string) ($distribution['region'] ?? ''),
                    'ville' => (string) ($distribution['ville'] ?? ''),
                    'besoins' => [],
                    'dons_distribues' => [],
                ];
            }

            $detailsParVille[$idVille]['dons_distribues'][] = $distribution;
        }

        return $detailsParVille;
    }
}
