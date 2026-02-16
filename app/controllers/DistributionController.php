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
        $donneesSimulation = $this->serviceDistribution->simulerDistribution();

        $requete = \Flight::request();
        $messageSucces = trim((string) ($requete->query['success'] ?? ''));
        $messageErreur = trim((string) ($requete->query['error'] ?? ''));

        $contenu = \Flight::view()->fetch('distribution/index', [
            'donnees_simulation' => $donneesSimulation,
            'message_succes' => $messageSucces,
            'message_erreur' => $messageErreur,
        ]);

        \Flight::render('layout', [
            'title' => 'Distribution des dons',
            'menu_actif' => 'distribution',
            'content' => $contenu,
        ]);
    }

    public function validerDistribution(): void
    {
        try {
            $resultat = $this->serviceDistribution->validerDistribution();

            $message = sprintf(
                'Dispatch valide : %.2f unites distribuées, %d besoins dispatchés completement, %d partiellement.',
                (float) $resultat['quantite_totale_distribuee'],
                (int) $resultat['besoins_dispatche_complet'],
                (int) $resultat['besoins_dispatche_partiel']
            );

            \Flight::redirect(BASE_URL . 'distribution?success=' . rawurlencode($message));
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
            \Flight::redirect(BASE_URL . 'distribution?error=' . rawurlencode('Erreur pendant la validation du dispatch.'));
        }
    }
}
