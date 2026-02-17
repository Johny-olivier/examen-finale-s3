<?php
declare(strict_types=1);

namespace app\controllers;

use app\repositories\RecapitulationRepository;
use app\services\RecapitulationService;

class RecapitulationController
{
    private RecapitulationService $serviceRecapitulation;

    public function __construct()
    {
        $depotRecapitulation = new RecapitulationRepository(\Flight::db());
        $this->serviceRecapitulation = new RecapitulationService($depotRecapitulation);
    }

    public function afficherPageRecapitulation(): void
    {
        $donnees = $this->serviceRecapitulation->obtenirDonneesRecapitulation();

        $contenu = \Flight::view()->fetch('recapitulation/index', [
            'donnees' => $donnees,
            'url_actualisation_ajax' => BASE_URL . 'recapitulation/donnees',
        ]);

        \Flight::render('layout', [
            'title' => 'Recapitulation des montants',
            'menu_actif' => 'recapitulation',
            'content' => $contenu,
        ]);
    }

    public function obtenirDonneesRecapitulationAjax(): void
    {
        try {
            $donnees = $this->serviceRecapitulation->obtenirDonneesRecapitulation();
            $this->envoyerJson([
                'succes' => true,
                'donnees' => $donnees,
            ]);
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
            http_response_code(500);
            $this->envoyerJson([
                'succes' => false,
                'message' => 'Erreur pendant l\'actualisation de la recapitulation.',
            ]);
        }
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function envoyerJson(array $payload): void
    {
        \Flight::response()->header('Content-Type', 'application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
