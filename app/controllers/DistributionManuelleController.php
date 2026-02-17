<?php
declare(strict_types=1);

namespace app\controllers;

use app\repositories\DistributionManuelleRepository;
use app\services\DistributionManuelleService;

class DistributionManuelleController
{
    private DistributionManuelleService $serviceDistributionManuelle;

    public function __construct()
    {
        $depotDistributionManuelle = new DistributionManuelleRepository(\Flight::db());
        $this->serviceDistributionManuelle = new DistributionManuelleService($depotDistributionManuelle);
    }

    public function afficherPageDistribution(): void
    {
        $requete = \Flight::request();
        $idVilleFiltre = (int) ($requete->query['id_ville'] ?? 0);
        $messageSucces = trim((string) ($requete->query['success'] ?? ''));
        $messageErreur = trim((string) ($requete->query['error'] ?? ''));

        $donnees = $this->serviceDistributionManuelle->obtenirDonneesPage($idVilleFiltre > 0 ? $idVilleFiltre : null);

        $contenu = \Flight::view()->fetch('distribution/index', [
            'donnees' => $donnees,
            'message_succes' => $messageSucces,
            'message_erreur' => $messageErreur,
        ]);

        \Flight::render('layout', [
            'title' => 'Distribution manuelle des dons',
            'menu_actif' => 'distribution_manuelle',
            'content' => $contenu,
        ]);
    }

    public function enregistrerDistribution(): void
    {
        try {
            $requete = \Flight::request();
            $idVille = (int) ($requete->data['id_ville'] ?? 0);
            $idProduit = (int) ($requete->data['id_produit'] ?? 0);
            $idUnite = (int) ($requete->data['id_unite'] ?? 0);
            $quantite = (float) ($requete->data['quantite'] ?? 0);
            $dateDistribution = trim((string) ($requete->data['date_distribution'] ?? ''));

            $this->serviceDistributionManuelle->distribuerManuellement(
                $idVille,
                $idProduit,
                $idUnite,
                $quantite,
                $dateDistribution !== '' ? $dateDistribution : null
            );

            \Flight::redirect(BASE_URL . 'distribution?success=' . rawurlencode('Distribution manuelle enregistree avec succes.'));
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
            \Flight::redirect(BASE_URL . 'distribution?error=' . rawurlencode($exception->getMessage()));
        }
    }
}
