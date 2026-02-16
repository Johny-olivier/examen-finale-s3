<?php
declare(strict_types=1);

namespace app\controllers;

use app\repositories\DashboardRepository;
use app\services\DashboardService;

class DashboardController
{
    private DashboardService $serviceDashboard;

    public function __construct()
    {
        $depotDashboard = new DashboardRepository(\Flight::db());
        $this->serviceDashboard = new DashboardService($depotDashboard);
    }

    public function afficherDashboard(): void
    {
        $donnees = $this->serviceDashboard->obtenirDonneesDashboard();
        $contenu = \Flight::view()->fetch('dashboard/index', [
            'donnees' => $donnees,
        ]);

        \Flight::render('layout', [
            'title' => 'Dashboard',
            'menu_actif' => 'dashboard',
            'content' => $contenu,
        ]);
    }
}
