<?php
declare(strict_types=1);

namespace app\controllers;

use app\repositories\StockRepository;
use app\services\StockService;

class StockController
{
    private StockService $serviceStock;

    public function __construct()
    {
        $depotStock = new StockRepository(\Flight::db());
        $this->serviceStock = new StockService($depotStock);
    }

    public function afficherPageInitialisation(): void
    {
        $requete = \Flight::request();
        $messageSucces = trim((string) ($requete->query['success'] ?? ''));
        $messageErreur = trim((string) ($requete->query['error'] ?? ''));

        $donnees = $this->serviceStock->obtenirDonneesInitialisation();
        $contenu = \Flight::view()->fetch('stock/initialisation', [
            'donnees' => $donnees,
            'message_succes' => $messageSucces,
            'message_erreur' => $messageErreur,
        ]);

        \Flight::render('layout', [
            'title' => 'Ajouter stock BNGRC',
            'menu_actif' => 'stock_initial',
            'content' => $contenu,
        ]);
    }

    public function enregistrerInitialisation(): void
    {
        try {
            $requete = \Flight::request();
            $idProduit = (int) ($requete->data['id_produit'] ?? 0);
            $idUnite = (int) ($requete->data['id_unite'] ?? 0);
            $quantite = (float) ($requete->data['quantite'] ?? 0);

            $resultat = $this->serviceStock->ajouterStockInitial($idProduit, $idUnite, $quantite);
            $message = sprintf(
                'Stock initialise: +%.2f unite(s) pour produit #%d et unite #%d.',
                (float) $resultat['quantite_ajoutee'],
                (int) $resultat['id_produit'],
                (int) $resultat['id_unite']
            );

            \Flight::redirect(BASE_URL . 'stock/initialisation?success=' . rawurlencode($message));
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
            \Flight::redirect(BASE_URL . 'stock/initialisation?error=' . rawurlencode($exception->getMessage()));
        }
    }

    public function afficherPageConsultation(): void
    {
        $donnees = $this->serviceStock->obtenirDonneesConsultation();
        $contenu = \Flight::view()->fetch('stock/consultation', [
            'donnees' => $donnees,
        ]);

        \Flight::render('layout', [
            'title' => 'Consultation du stock',
            'menu_actif' => 'stock',
            'content' => $contenu,
        ]);
    }
}
