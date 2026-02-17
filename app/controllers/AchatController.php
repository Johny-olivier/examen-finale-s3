<?php
declare(strict_types=1);

namespace app\controllers;

use app\repositories\AchatRepository;
use app\services\AchatService;

class AchatController
{
    private AchatService $serviceAchat;

    public function __construct()
    {
        $depotAchat = new AchatRepository(\Flight::db());
        $this->serviceAchat = new AchatService($depotAchat);
    }

    public function afficherPageAchats(): void
    {
        $requete = \Flight::request();
        $idVilleFiltre = (int) ($requete->query['id_ville'] ?? 0);
        $messageSucces = trim((string) ($requete->query['success'] ?? ''));
        $messageErreur = trim((string) ($requete->query['error'] ?? ''));

        $donnees = $this->serviceAchat->obtenirDonneesPage($idVilleFiltre > 0 ? $idVilleFiltre : null);
        $contenu = \Flight::view()->fetch('achats/index', [
            'donnees' => $donnees,
            'message_succes' => $messageSucces,
            'message_erreur' => $messageErreur,
        ]);

        \Flight::render('layout', [
            'title' => 'Achats via dons en argent',
            'menu_actif' => 'achats',
            'content' => $contenu,
        ]);
    }

    public function enregistrerAchat(): void
    {
        $requete = \Flight::request();
        $idVilleFiltre = (int) ($requete->data['id_ville_filtre'] ?? 0);

        try {
            $idBesoin = (int) ($requete->data['id_besoin'] ?? 0);
            $quantite = (float) ($requete->data['quantite'] ?? 0);
            $dateAchat = trim((string) ($requete->data['date_achat'] ?? ''));
            $commentaire = trim((string) ($requete->data['commentaire'] ?? ''));

            $this->serviceAchat->enregistrerAchatDepuisBesoin(
                $idBesoin,
                $quantite,
                $dateAchat !== '' ? $dateAchat : null,
                $commentaire !== '' ? $commentaire : null
            );

            $urlRedirection = $this->construireUrlAchats($idVilleFiltre, 'success', 'Achat enregistre avec succes.');
            \Flight::redirect($urlRedirection);
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
            $urlRedirection = $this->construireUrlAchats($idVilleFiltre, 'error', $exception->getMessage());
            \Flight::redirect($urlRedirection);
        }
    }

    private function construireUrlAchats(int $idVilleFiltre, string $cleMessage, string $message): string
    {
        $parametres = [];
        if ($idVilleFiltre > 0) {
            $parametres['id_ville'] = (string) $idVilleFiltre;
        }
        $parametres[$cleMessage] = $message;

        return BASE_URL . 'achats?' . http_build_query($parametres);
    }
}
