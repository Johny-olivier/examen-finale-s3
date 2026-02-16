<?php
declare(strict_types=1);

namespace app\controllers;

use app\repositories\DonRepository;
use app\services\DonService;

class DonController
{
    private DonService $serviceDon;

    public function __construct()
    {
        $depotDon = new DonRepository(\Flight::db());
        $this->serviceDon = new DonService($depotDon);
    }

    public function afficherPageDons(): void
    {
        $requete = \Flight::request();
        $messageSucces = trim((string) ($requete->query['success'] ?? ''));
        $messageErreur = trim((string) ($requete->query['error'] ?? ''));

        $donnees = $this->serviceDon->obtenirDonneesPage();
        $contenu = \Flight::view()->fetch('dons/index', [
            'donnees' => $donnees,
            'message_succes' => $messageSucces,
            'message_erreur' => $messageErreur,
        ]);

        \Flight::render('layout', [
            'title' => 'Insertion des dons',
            'menu_actif' => 'dons',
            'content' => $contenu,
        ]);
    }

    public function enregistrerDon(): void
    {
        try {
            $requete = \Flight::request();
            $idProduit = (int) ($requete->data['id_produit'] ?? 0);
            $idUnite = (int) ($requete->data['id_unite'] ?? 0);
            $quantite = (float) ($requete->data['quantite'] ?? 0);
            $donateur = trim((string) ($requete->data['donateur'] ?? ''));
            $dateDon = trim((string) ($requete->data['date_don'] ?? ''));

            $this->serviceDon->insererDon(
                $idProduit,
                $idUnite,
                $quantite,
                $donateur,
                $dateDon !== '' ? $dateDon : null
            );

            \Flight::redirect(BASE_URL . 'dons?success=' . rawurlencode('Don enregistre avec succes.'));
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
            \Flight::redirect(BASE_URL . 'dons?error=' . rawurlencode($exception->getMessage()));
        }
    }
}
