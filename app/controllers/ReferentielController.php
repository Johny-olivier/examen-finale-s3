<?php
declare(strict_types=1);

namespace app\controllers;

use app\repositories\ReferentielRepository;
use app\services\ReferentielService;

class ReferentielController
{
    private ReferentielService $serviceReferentiel;

    public function __construct()
    {
        $depotReferentiel = new ReferentielRepository(\Flight::db());
        $this->serviceReferentiel = new ReferentielService($depotReferentiel);
    }

    public function afficherPageReferentiels(): void
    {
        $requete = \Flight::request();
        $messageSucces = trim((string) ($requete->query['success'] ?? ''));
        $messageErreur = trim((string) ($requete->query['error'] ?? ''));

        $donnees = $this->serviceReferentiel->obtenirDonneesPage();

        $contenu = \Flight::view()->fetch('referentiels/index', [
            'donnees' => $donnees,
            'message_succes' => $messageSucces,
            'message_erreur' => $messageErreur,
        ]);

        \Flight::render('layout', [
            'title' => 'Referentiels (CRUD)',
            'menu_actif' => 'referentiels',
            'content' => $contenu,
        ]);
    }

    public function ajouterRegion(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->ajouterRegion((string) ($requete->data['nom'] ?? ''));
        }, 'Region ajoutee avec succes.', 'regions');
    }

    public function modifierRegion(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->modifierRegion(
                (int) ($requete->data['id_region'] ?? 0),
                (string) ($requete->data['nom'] ?? '')
            );
        }, 'Region modifiee avec succes.', 'regions');
    }

    public function supprimerRegion(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->supprimerRegion((int) ($requete->data['id_region'] ?? 0));
        }, 'Region supprimee avec succes.', 'regions');
    }

    public function ajouterVille(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->ajouterVille(
                (int) ($requete->data['id_region'] ?? 0),
                (string) ($requete->data['nom'] ?? '')
            );
        }, 'Ville ajoutee avec succes.', 'villes');
    }

    public function modifierVille(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->modifierVille(
                (int) ($requete->data['id_ville'] ?? 0),
                (int) ($requete->data['id_region'] ?? 0),
                (string) ($requete->data['nom'] ?? '')
            );
        }, 'Ville modifiee avec succes.', 'villes');
    }

    public function supprimerVille(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->supprimerVille((int) ($requete->data['id_ville'] ?? 0));
        }, 'Ville supprimee avec succes.', 'villes');
    }

    public function ajouterCategorie(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->ajouterCategorie((string) ($requete->data['nom'] ?? ''));
        }, 'Categorie ajoutee avec succes.', 'categories');
    }

    public function modifierCategorie(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->modifierCategorie(
                (int) ($requete->data['id_categorie'] ?? 0),
                (string) ($requete->data['nom'] ?? '')
            );
        }, 'Categorie modifiee avec succes.', 'categories');
    }

    public function supprimerCategorie(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->supprimerCategorie((int) ($requete->data['id_categorie'] ?? 0));
        }, 'Categorie supprimee avec succes.', 'categories');
    }

    public function ajouterProduit(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->ajouterProduit(
                (string) ($requete->data['nom'] ?? ''),
                (int) ($requete->data['id_categorie'] ?? 0),
                (float) ($requete->data['prix_unitaire'] ?? 0)
            );
        }, 'Produit ajoute avec succes.', 'produits');
    }

    public function modifierProduit(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->modifierProduit(
                (int) ($requete->data['id_produit'] ?? 0),
                (string) ($requete->data['nom'] ?? ''),
                (int) ($requete->data['id_categorie'] ?? 0),
                (float) ($requete->data['prix_unitaire'] ?? 0)
            );
        }, 'Produit modifie avec succes.', 'produits');
    }

    public function supprimerProduit(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->supprimerProduit((int) ($requete->data['id_produit'] ?? 0));
        }, 'Produit supprime avec succes.', 'produits');
    }

    public function ajouterUnite(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->ajouterUnite((string) ($requete->data['nom'] ?? ''));
        }, 'Unite ajoutee avec succes.', 'unites');
    }

    public function modifierUnite(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->modifierUnite(
                (int) ($requete->data['id_unite'] ?? 0),
                (string) ($requete->data['nom'] ?? '')
            );
        }, 'Unite modifiee avec succes.', 'unites');
    }

    public function supprimerUnite(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->supprimerUnite((int) ($requete->data['id_unite'] ?? 0));
        }, 'Unite supprimee avec succes.', 'unites');
    }

    public function ajouterTypeParametreAchat(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->ajouterTypeParametreAchat(
                (string) ($requete->data['code'] ?? ''),
                (string) ($requete->data['libelle'] ?? '')
            );
        }, 'Type de parametre ajoute avec succes.', 'types-parametre-achat');
    }

    public function modifierTypeParametreAchat(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->modifierTypeParametreAchat(
                (int) ($requete->data['id_type_parametre_achat'] ?? 0),
                (string) ($requete->data['code'] ?? ''),
                (string) ($requete->data['libelle'] ?? '')
            );
        }, 'Type de parametre modifie avec succes.', 'types-parametre-achat');
    }

    public function supprimerTypeParametreAchat(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->supprimerTypeParametreAchat((int) ($requete->data['id_type_parametre_achat'] ?? 0));
        }, 'Type de parametre supprime avec succes.', 'types-parametre-achat');
    }

    public function ajouterParametreAchat(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->ajouterParametreAchat(
                (int) ($requete->data['id_type_parametre_achat'] ?? 0),
                (float) ($requete->data['valeur_decimal'] ?? 0),
                (string) ($requete->data['date_application'] ?? ''),
                (int) ($requete->data['actif'] ?? 0)
            );
        }, 'Parametre achat ajoute avec succes.', 'parametres-achat');
    }

    public function modifierParametreAchat(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->modifierParametreAchat(
                (int) ($requete->data['id_parametre_achat'] ?? 0),
                (int) ($requete->data['id_type_parametre_achat'] ?? 0),
                (float) ($requete->data['valeur_decimal'] ?? 0),
                (string) ($requete->data['date_application'] ?? ''),
                (int) ($requete->data['actif'] ?? 0)
            );
        }, 'Parametre achat modifie avec succes.', 'parametres-achat');
    }

    public function supprimerParametreAchat(): void
    {
        $this->executerAction(function (): void {
            $requete = \Flight::request();
            $this->serviceReferentiel->supprimerParametreAchat((int) ($requete->data['id_parametre_achat'] ?? 0));
        }, 'Parametre achat supprime avec succes.', 'parametres-achat');
    }

    /**
     * @param callable():void $action
     */
    private function executerAction(callable $action, string $messageSucces, string $ancre): void
    {
        try {
            $action();
            $this->redirigerMessage('success', $messageSucces, $ancre);
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
            $this->redirigerMessage('error', $exception->getMessage(), $ancre);
        }
    }

    private function redirigerMessage(string $type, string $message, string $ancre): void
    {
        $url = BASE_URL . 'referentiels?' . $type . '=' . rawurlencode($message);
        if ($ancre !== '') {
            $url .= '#' . $ancre;
        }

        \Flight::redirect($url);
    }
}
