<?php
declare(strict_types=1);

namespace app\services;

use app\repositories\ReferentielRepository;

class ReferentielService
{
    private ReferentielRepository $depotReferentiel;

    public function __construct(ReferentielRepository $depotReferentiel)
    {
        $this->depotReferentiel = $depotReferentiel;
    }

    /**
     * @return array<string,mixed>
     */
    public function obtenirDonneesPage(): array
    {
        return [
            'regions' => $this->depotReferentiel->obtenirRegions(),
            'villes' => $this->depotReferentiel->obtenirVilles(),
            'categories' => $this->depotReferentiel->obtenirCategories(),
            'produits' => $this->depotReferentiel->obtenirProduits(),
            'unites' => $this->depotReferentiel->obtenirUnites(),
            'types_parametre_achat' => $this->depotReferentiel->obtenirTypesParametreAchat(),
            'parametres_achat' => $this->depotReferentiel->obtenirParametresAchat(),
        ];
    }

    public function ajouterRegion(string $nom): void
    {
        $this->depotReferentiel->ajouterRegion($this->validerNom($nom, 'Nom de region'));
    }

    public function modifierRegion(int $idRegion, string $nom): void
    {
        $this->validerIdentifiant($idRegion, 'Region');
        $this->depotReferentiel->modifierRegion($idRegion, $this->validerNom($nom, 'Nom de region'));
    }

    public function supprimerRegion(int $idRegion): void
    {
        $this->validerIdentifiant($idRegion, 'Region');
        $this->depotReferentiel->supprimerRegion($idRegion);
    }

    public function ajouterVille(int $idRegion, string $nom): void
    {
        $this->validerIdentifiant($idRegion, 'Region');
        $this->depotReferentiel->ajouterVille($idRegion, $this->validerNom($nom, 'Nom de ville'));
    }

    public function modifierVille(int $idVille, int $idRegion, string $nom): void
    {
        $this->validerIdentifiant($idVille, 'Ville');
        $this->validerIdentifiant($idRegion, 'Region');
        $this->depotReferentiel->modifierVille($idVille, $idRegion, $this->validerNom($nom, 'Nom de ville'));
    }

    public function supprimerVille(int $idVille): void
    {
        $this->validerIdentifiant($idVille, 'Ville');
        $this->depotReferentiel->supprimerVille($idVille);
    }

    public function ajouterCategorie(string $nom): void
    {
        $this->depotReferentiel->ajouterCategorie($this->validerNom($nom, 'Nom de categorie'));
    }

    public function modifierCategorie(int $idCategorie, string $nom): void
    {
        $this->validerIdentifiant($idCategorie, 'Categorie');
        $this->depotReferentiel->modifierCategorie($idCategorie, $this->validerNom($nom, 'Nom de categorie'));
    }

    public function supprimerCategorie(int $idCategorie): void
    {
        $this->validerIdentifiant($idCategorie, 'Categorie');
        $this->depotReferentiel->supprimerCategorie($idCategorie);
    }

    public function ajouterProduit(string $nom, int $idCategorie, float $prixUnitaire): void
    {
        $this->validerIdentifiant($idCategorie, 'Categorie');
        $this->validerPrixUnitaire($prixUnitaire);
        $this->depotReferentiel->ajouterProduit(
            $this->validerNom($nom, 'Nom de produit'),
            $idCategorie,
            $prixUnitaire
        );
    }

    public function modifierProduit(int $idProduit, string $nom, int $idCategorie, float $prixUnitaire): void
    {
        $this->validerIdentifiant($idProduit, 'Produit');
        $this->validerIdentifiant($idCategorie, 'Categorie');
        $this->validerPrixUnitaire($prixUnitaire);
        $this->depotReferentiel->modifierProduit(
            $idProduit,
            $this->validerNom($nom, 'Nom de produit'),
            $idCategorie,
            $prixUnitaire
        );
    }

    public function supprimerProduit(int $idProduit): void
    {
        $this->validerIdentifiant($idProduit, 'Produit');
        $this->depotReferentiel->supprimerProduit($idProduit);
    }

    public function ajouterUnite(string $nom): void
    {
        $this->depotReferentiel->ajouterUnite($this->validerNom($nom, 'Nom d\'unite'));
    }

    public function modifierUnite(int $idUnite, string $nom): void
    {
        $this->validerIdentifiant($idUnite, 'Unite');
        $this->depotReferentiel->modifierUnite($idUnite, $this->validerNom($nom, 'Nom d\'unite'));
    }

    public function supprimerUnite(int $idUnite): void
    {
        $this->validerIdentifiant($idUnite, 'Unite');
        $this->depotReferentiel->supprimerUnite($idUnite);
    }

    public function ajouterTypeParametreAchat(string $code, string $libelle): void
    {
        $this->depotReferentiel->ajouterTypeParametreAchat(
            $this->validerCode($code, 'Code type parametre'),
            $this->validerNom($libelle, 'Libelle type parametre')
        );
    }

    public function modifierTypeParametreAchat(int $idTypeParametreAchat, string $code, string $libelle): void
    {
        $this->validerIdentifiant($idTypeParametreAchat, 'Type parametre');
        $this->depotReferentiel->modifierTypeParametreAchat(
            $idTypeParametreAchat,
            $this->validerCode($code, 'Code type parametre'),
            $this->validerNom($libelle, 'Libelle type parametre')
        );
    }

    public function supprimerTypeParametreAchat(int $idTypeParametreAchat): void
    {
        $this->validerIdentifiant($idTypeParametreAchat, 'Type parametre');
        $this->depotReferentiel->supprimerTypeParametreAchat($idTypeParametreAchat);
    }

    public function ajouterParametreAchat(
        int $idTypeParametreAchat,
        float $valeurDecimal,
        string $dateApplication,
        int $actif
    ): void {
        $this->validerIdentifiant($idTypeParametreAchat, 'Type parametre');
        $dateApplicationNormalisee = $this->normaliserDateApplication($dateApplication);
        $this->depotReferentiel->ajouterParametreAchat(
            $idTypeParametreAchat,
            $this->validerValeurDecimal($valeurDecimal, 'Valeur parametre'),
            $dateApplicationNormalisee,
            $this->normaliserActif($actif)
        );
    }

    public function modifierParametreAchat(
        int $idParametreAchat,
        int $idTypeParametreAchat,
        float $valeurDecimal,
        string $dateApplication,
        int $actif
    ): void {
        $this->validerIdentifiant($idParametreAchat, 'Parametre achat');
        $this->validerIdentifiant($idTypeParametreAchat, 'Type parametre');
        $dateApplicationNormalisee = $this->normaliserDateApplication($dateApplication);
        $this->depotReferentiel->modifierParametreAchat(
            $idParametreAchat,
            $idTypeParametreAchat,
            $this->validerValeurDecimal($valeurDecimal, 'Valeur parametre'),
            $dateApplicationNormalisee,
            $this->normaliserActif($actif)
        );
    }

    public function supprimerParametreAchat(int $idParametreAchat): void
    {
        $this->validerIdentifiant($idParametreAchat, 'Parametre achat');
        $this->depotReferentiel->supprimerParametreAchat($idParametreAchat);
    }

    private function validerNom(string $nom, string $champ): string
    {
        $nomNettoye = trim($nom);
        if ($nomNettoye === '') {
            throw new \InvalidArgumentException($champ . ' obligatoire.');
        }

        return $nomNettoye;
    }

    private function validerIdentifiant(int $identifiant, string $libelle): void
    {
        if ($identifiant <= 0) {
            throw new \InvalidArgumentException($libelle . ' invalide.');
        }
    }

    private function validerPrixUnitaire(float $prixUnitaire): void
    {
        if ($prixUnitaire < 0) {
            throw new \InvalidArgumentException('Le prix unitaire ne peut pas etre negatif.');
        }
    }

    private function validerCode(string $code, string $champ): string
    {
        $codeNettoye = trim($code);
        if ($codeNettoye === '') {
            throw new \InvalidArgumentException($champ . ' obligatoire.');
        }

        return $codeNettoye;
    }

    private function validerValeurDecimal(float $valeur, string $champ): float
    {
        if ($valeur < 0) {
            throw new \InvalidArgumentException($champ . ' ne peut pas etre negatif.');
        }

        return $valeur;
    }

    private function normaliserDateApplication(string $dateApplication): string
    {
        if (trim($dateApplication) === '') {
            throw new \InvalidArgumentException('Date application obligatoire.');
        }

        $horodatage = strtotime($dateApplication);
        if ($horodatage === false) {
            throw new \InvalidArgumentException('Date application invalide.');
        }

        return date('Y-m-d H:i:s', $horodatage);
    }

    private function normaliserActif(int $actif): int
    {
        return $actif === 1 ? 1 : 0;
    }
}
