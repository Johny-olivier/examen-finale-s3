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

    public function afficherPageSimulation(): void
    {
        $requete = \Flight::request();
        $messageSucces = trim((string) ($requete->query['success'] ?? ''));
        $messageErreur = trim((string) ($requete->query['error'] ?? ''));
        $simulationEffectuee = ((string) ($requete->query['simuler'] ?? '0')) === '1';
        $modeDispatch = $this->serviceDistribution->normaliserModeDispatch((string) ($requete->query['mode_dispatch'] ?? 'date'));
        $donneesSimulation = $this->creerSimulationVide($modeDispatch);

        if ($simulationEffectuee === true) {
            $donneesSimulation = $this->serviceDistribution->simulerDistribution($modeDispatch);
        }

        $contenu = \Flight::view()->fetch('simulation/index', [
            'donnees_simulation' => $donneesSimulation,
            'message_succes' => $messageSucces,
            'message_erreur' => $messageErreur,
            'simulation_effectuee' => $simulationEffectuee,
            'mode_dispatch' => $modeDispatch,
            'modes_dispatch_disponibles' => $this->serviceDistribution->obtenirModesDispatchDisponibles(),
            'libelle_mode_dispatch' => $this->serviceDistribution->obtenirLibelleModeDispatch($modeDispatch),
        ]);

        \Flight::render('layout', [
            'title' => 'Simulation et validation de distribution',
            'menu_actif' => 'simulation_dispatch',
            'content' => $contenu,
        ]);
    }

    public function simulerDistribution(): void
    {
        $requete = \Flight::request();
        $modeDispatch = $this->serviceDistribution->normaliserModeDispatch((string) ($requete->data['mode_dispatch'] ?? 'date'));
        \Flight::redirect(BASE_URL . 'simulation-dispatch?simuler=1&mode_dispatch=' . rawurlencode($modeDispatch));
    }

    public function validerDistribution(): void
    {
        $requete = \Flight::request();
        $modeDispatch = $this->serviceDistribution->normaliserModeDispatch((string) ($requete->data['mode_dispatch'] ?? 'date'));

        try {
            $resultat = $this->serviceDistribution->validerDistribution($modeDispatch);

            $message = sprintf(
                'Validation effectuee (%s) : %.2f unites distribuees, %d besoins dispatches completement, %d partiellement, %d non touches.',
                (string) ($resultat['libelle_mode_dispatch'] ?? ''),
                (float) $resultat['quantite_totale_distribuee'],
                (int) $resultat['besoins_dispatche_complet'],
                (int) $resultat['besoins_dispatche_partiel'],
                (int) $resultat['besoins_non_touches']
            );

            \Flight::redirect(
                BASE_URL
                . 'simulation-dispatch?simuler=1&mode_dispatch='
                . rawurlencode($modeDispatch)
                . '&success='
                . rawurlencode($message)
            );
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
            \Flight::redirect(
                BASE_URL
                . 'simulation-dispatch?simuler=1&mode_dispatch='
                . rawurlencode($modeDispatch)
                . '&error='
                . rawurlencode('Erreur pendant la validation du dispatch.')
            );
        }
    }

    public function reinitialiserDonneesPointDepart(): void
    {
        try {
            $this->serviceDistribution->reinitialiserDonneesPointDepart();
            \Flight::redirect(
                BASE_URL
                . 'simulation-dispatch?success='
                . rawurlencode('Donnees reinitialisees au point de depart avec succes.')
            );
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
            \Flight::redirect(
                BASE_URL
                . 'simulation-dispatch?error='
                . rawurlencode('Erreur pendant la reinitialisation des donnees.')
            );
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function creerSimulationVide(string $modeDispatch): array
    {
        return [
            'besoins' => [],
            'stock_apres_simulation' => [],
            'mode_dispatch' => $modeDispatch,
            'libelle_mode_dispatch' => $this->serviceDistribution->obtenirLibelleModeDispatch($modeDispatch),
            'statistiques' => [
                'total_besoins' => 0,
                'total_dispatcheables' => 0,
                'total_partiels' => 0,
                'total_non_servis' => 0,
            ],
        ];
    }
}
