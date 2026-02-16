<?php
declare(strict_types=1);

namespace app\controllers;

use app\repositories\DistributionRepository;
use app\services\DistributionService;

class DistributionController
{
    private DistributionService $serviceDistribution;

    public function __construct()
    {
        $depotDistribution = new DistributionRepository(\Flight::db());
        $this->serviceDistribution = new DistributionService($depotDistribution);
    }

    public function afficherPageDistribution(): void
    {
        $requete = \Flight::request();
        $messageSucces = trim((string) ($requete->query['success'] ?? ''));
        $messageErreur = trim((string) ($requete->query['error'] ?? ''));
        $simulationEffectuee = ((string) ($requete->query['simuler'] ?? '0')) === '1';
        $donneesSimulation = $this->creerSimulationVide();

        if ($simulationEffectuee === true) {
            $donneesSimulation = $this->serviceDistribution->simulerDistribution();
        }

        $contenu = \Flight::view()->fetch('distribution/index', [
            'donnees_simulation' => $donneesSimulation,
            'message_succes' => $messageSucces,
            'message_erreur' => $messageErreur,
            'simulation_effectuee' => $simulationEffectuee,
        ]);

        \Flight::render('layout', [
            'title' => 'Simulation et validation de distribution',
            'menu_actif' => 'distribution',
            'content' => $contenu,
        ]);
    }

    public function simulerDistribution(): void
    {
        \Flight::redirect(BASE_URL . 'distribution?simuler=1');
    }

    public function validerDistribution(): void
    {
        try {
            $resultat = $this->serviceDistribution->validerDistribution();

            $message = sprintf(
                'Validation effectuee : %.2f unites distribuees, %d besoins dispatches completement, %d partiellement, %d non touches.',
                (float) $resultat['quantite_totale_distribuee'],
                (int) $resultat['besoins_dispatche_complet'],
                (int) $resultat['besoins_dispatche_partiel'],
                (int) $resultat['besoins_non_touches']
            );

            \Flight::redirect(BASE_URL . 'distribution?simuler=1&success=' . rawurlencode($message));
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
            \Flight::redirect(BASE_URL . 'distribution?simuler=1&error=' . rawurlencode('Erreur pendant la validation du dispatch.'));
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function creerSimulationVide(): array
    {
        return [
            'besoins' => [],
            'stock_apres_simulation' => [],
            'statistiques' => [
                'total_besoins' => 0,
                'total_dispatcheables' => 0,
                'total_partiels' => 0,
                'total_non_servis' => 0,
            ],
        ];
    }
}
