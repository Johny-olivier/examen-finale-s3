<?php
declare(strict_types=1);

namespace app\controllers;

use app\repositories\BesoinRepository;
use app\services\BesoinService;

class BesoinController
{
    private BesoinService $serviceBesoin;

    public function __construct()
    {
        $depotBesoin = new BesoinRepository(\Flight::db());
        $this->serviceBesoin = new BesoinService($depotBesoin);
    }

    public function afficherPageBesoins(): void
    {
        $requete = \Flight::request();
        $idVilleFiltre = (int) ($requete->query['id_ville'] ?? 0);
        $messageSucces = trim((string) ($requete->query['success'] ?? ''));
        $messageErreur = trim((string) ($requete->query['error'] ?? ''));

        $donnees = $this->serviceBesoin->obtenirDonneesPage($idVilleFiltre > 0 ? $idVilleFiltre : null);
        $contenu = \Flight::view()->fetch('besoins/index', [
            'donnees' => $donnees,
            'message_succes' => $messageSucces,
            'message_erreur' => $messageErreur,
        ]);

        \Flight::render('layout', [
            'title' => 'Insertion des besoins',
            'menu_actif' => 'besoins',
            'content' => $contenu,
        ]);
    }

    public function enregistrerBesoin(): void
    {
        try {
            $requete = \Flight::request();
            $idVille = (int) ($requete->data['id_ville'] ?? 0);
            $idProduit = (int) ($requete->data['id_produit'] ?? 0);
            $quantite = (float) ($requete->data['quantite'] ?? 0);
            $idUnite = (int) ($requete->data['id_unite'] ?? 0);
            $dateBesoin = trim((string) ($requete->data['date_besoin'] ?? ''));

            $this->serviceBesoin->insererBesoin(
                $idVille,
                $idProduit,
                $quantite,
                $idUnite,
                $dateBesoin !== '' ? $dateBesoin : null
            );

            \Flight::redirect(BASE_URL . 'besoins?success=' . rawurlencode('Besoin enregistre avec succes.'));
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
            \Flight::redirect(BASE_URL . 'besoins?error=' . rawurlencode($exception->getMessage()));
        }
    }
}
