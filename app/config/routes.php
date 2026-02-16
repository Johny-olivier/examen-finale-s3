<?php

use app\controllers\DashboardController;
use app\controllers\BesoinController;
use app\controllers\DonController;
use app\controllers\DistributionController;
use app\controllers\StockController;
use app\middlewares\SecurityHeadersMiddleware;
use flight\net\Router;

$router = Flight::router();

$router->group('', function (Router $router) {

    $controleurDistribution = new DistributionController();
    $controleurStock = new StockController();
    $controleurDashboard = new DashboardController();
    $controleurBesoin = new BesoinController();
    $controleurDon = new DonController();

    $router->get('/', function () {
        Flight::redirect(BASE_URL . 'distribution');
    });

    $router->get('/distribution', [$controleurDistribution, 'afficherPageDistribution']);
    $router->post('/distribution/valider', [$controleurDistribution, 'validerDistribution']);

    $router->get('/stock/initialisation', [$controleurStock, 'afficherPageInitialisation']);
    $router->post('/stock/initialisation', [$controleurStock, 'enregistrerInitialisation']);
    $router->get('/stock/consultation', [$controleurStock, 'afficherPageConsultation']);

    $router->get('/dashboard', [$controleurDashboard, 'afficherDashboard']);

    $router->get('/besoins', [$controleurBesoin, 'afficherPageBesoins']);
    $router->post('/besoins', [$controleurBesoin, 'enregistrerBesoin']);

    $router->get('/dons', [$controleurDon, 'afficherPageDons']);
    $router->post('/dons', [$controleurDon, 'enregistrerDon']);

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
