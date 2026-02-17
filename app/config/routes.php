<?php

use app\controllers\DashboardController;
use app\controllers\BesoinController;
use app\controllers\DonController;
use app\controllers\DistributionController;
use app\controllers\DistributionManuelleController;
use app\controllers\AchatController;
use app\controllers\RecapitulationController;
use app\controllers\ReferentielController;
use app\controllers\StockController;
use app\middlewares\SecurityHeadersMiddleware;
use flight\net\Router;

$router = Flight::router();

$router->group('', function (Router $router) {

    $controleurSimulationDispatch = new DistributionController();
    $controleurDistributionManuelle = new DistributionManuelleController();
    $controleurStock = new StockController();
    $controleurDashboard = new DashboardController();
    $controleurBesoin = new BesoinController();
    $controleurDon = new DonController();
    $controleurAchat = new AchatController();
    $controleurRecapitulation = new RecapitulationController();
    $controleurReferentiel = new ReferentielController();

    $router->get('/', function () {
        Flight::redirect(BASE_URL . 'dashboard');
    });

    $router->get('/distribution', [$controleurDistributionManuelle, 'afficherPageDistribution']);
    $router->post('/distribution', [$controleurDistributionManuelle, 'enregistrerDistribution']);

    $router->get('/simulation-dispatch', [$controleurSimulationDispatch, 'afficherPageSimulation']);
    $router->post('/simulation-dispatch/simuler', [$controleurSimulationDispatch, 'simulerDistribution']);
    $router->post('/simulation-dispatch/valider', [$controleurSimulationDispatch, 'validerDistribution']);
    $router->post('/simulation-dispatch/reinitialiser', [$controleurSimulationDispatch, 'reinitialiserDonneesPointDepart']);

    $router->get('/stock/initialisation', [$controleurStock, 'afficherPageInitialisation']);
    $router->post('/stock/initialisation', [$controleurStock, 'enregistrerInitialisation']);
    $router->get('/stock/consultation', [$controleurStock, 'afficherPageConsultation']);

    $router->get('/dashboard', [$controleurDashboard, 'afficherDashboard']);

    $router->get('/besoins', [$controleurBesoin, 'afficherPageBesoins']);
    $router->post('/besoins', [$controleurBesoin, 'enregistrerBesoin']);

    $router->get('/dons', [$controleurDon, 'afficherPageDons']);
    $router->post('/dons', [$controleurDon, 'enregistrerDon']);

    $router->get('/achats', [$controleurAchat, 'afficherPageAchats']);
    $router->post('/achats', [$controleurAchat, 'enregistrerAchat']);

    $router->get('/recapitulation', [$controleurRecapitulation, 'afficherPageRecapitulation']);
    $router->get('/recapitulation/donnees', [$controleurRecapitulation, 'obtenirDonneesRecapitulationAjax']);

    $router->get('/referentiels', [$controleurReferentiel, 'afficherPageReferentiels']);
    $router->post('/referentiels/regions/ajouter', [$controleurReferentiel, 'ajouterRegion']);
    $router->post('/referentiels/regions/modifier', [$controleurReferentiel, 'modifierRegion']);
    $router->post('/referentiels/regions/supprimer', [$controleurReferentiel, 'supprimerRegion']);
    $router->post('/referentiels/villes/ajouter', [$controleurReferentiel, 'ajouterVille']);
    $router->post('/referentiels/villes/modifier', [$controleurReferentiel, 'modifierVille']);
    $router->post('/referentiels/villes/supprimer', [$controleurReferentiel, 'supprimerVille']);
    $router->post('/referentiels/categories/ajouter', [$controleurReferentiel, 'ajouterCategorie']);
    $router->post('/referentiels/categories/modifier', [$controleurReferentiel, 'modifierCategorie']);
    $router->post('/referentiels/categories/supprimer', [$controleurReferentiel, 'supprimerCategorie']);
    $router->post('/referentiels/produits/ajouter', [$controleurReferentiel, 'ajouterProduit']);
    $router->post('/referentiels/produits/modifier', [$controleurReferentiel, 'modifierProduit']);
    $router->post('/referentiels/produits/supprimer', [$controleurReferentiel, 'supprimerProduit']);
    $router->post('/referentiels/unites/ajouter', [$controleurReferentiel, 'ajouterUnite']);
    $router->post('/referentiels/unites/modifier', [$controleurReferentiel, 'modifierUnite']);
    $router->post('/referentiels/unites/supprimer', [$controleurReferentiel, 'supprimerUnite']);
    $router->post('/referentiels/types-parametre-achat/ajouter', [$controleurReferentiel, 'ajouterTypeParametreAchat']);
    $router->post('/referentiels/types-parametre-achat/modifier', [$controleurReferentiel, 'modifierTypeParametreAchat']);
    $router->post('/referentiels/types-parametre-achat/supprimer', [$controleurReferentiel, 'supprimerTypeParametreAchat']);
    $router->post('/referentiels/parametres-achat/ajouter', [$controleurReferentiel, 'ajouterParametreAchat']);
    $router->post('/referentiels/parametres-achat/modifier', [$controleurReferentiel, 'modifierParametreAchat']);
    $router->post('/referentiels/parametres-achat/supprimer', [$controleurReferentiel, 'supprimerParametreAchat']);

}, [SecurityHeadersMiddleware::class]);

Flight::map('notFound', function () {
    http_response_code(404);
    Flight::render('errors/404');
});

Flight::map('error', function (\Throwable $ex) {
    http_response_code(500);

    error_log($ex->getMessage());

    Flight::render('errors/500', [
        'exception' => $ex 
    ]);
});
