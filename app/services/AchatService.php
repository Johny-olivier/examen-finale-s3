<?php
declare(strict_types=1);

namespace app\services;

use app\repositories\AchatRepository;

class AchatService
{
    private const MARGE_FLOTTANTE = 0.000001;

    private AchatRepository $depotAchat;

    public function __construct(AchatRepository $depotAchat)
    {
        $this->depotAchat = $depotAchat;
    }

    /**
     * @return array<string,mixed>
     */
    public function obtenirDonneesPage(?int $idVilleFiltre = null): array
    {
        $besoinsRestants = $this->depotAchat->obtenirBesoinsRestantsPourAchat($idVilleFiltre);
        $achats = $this->depotAchat->obtenirAchats($idVilleFiltre);
        $tauxFrais = $this->depotAchat->obtenirTauxFraisAchatActif();

        $configurationArgent = $this->depotAchat->obtenirProduitArgentUniteAr();
        $idProduitArgent = (int) ($configurationArgent['idProduit'] ?? 0);
        $idUniteArgent = (int) ($configurationArgent['idUnite'] ?? 0);

        $soldeArgentDisponible = 0.0;
        if ($idProduitArgent > 0 && $idUniteArgent > 0) {
            $soldeArgentDisponible = $this->depotAchat->obtenirSoldeArgentDistribueDisponible(
                $idProduitArgent,
                $idUniteArgent,
                $idVilleFiltre
            );
        }

        return [
            'villes' => $this->depotAchat->obtenirVilles(),
            'besoins_restants' => $besoinsRestants,
            'achats' => $achats,
            'resume' => $this->construireResume($achats),
            'id_ville_filtre' => $idVilleFiltre,
            'taux_frais' => $tauxFrais,
            'solde_argent_disponible' => $soldeArgentDisponible,
        ];
    }

    public function enregistrerAchatDepuisBesoin(
        int $idBesoin,
        float $quantiteAAcheter,
        ?string $dateAchat = null,
        ?string $commentaire = null
    ): void {
        if ($idBesoin <= 0) {
            throw new \InvalidArgumentException('Besoin invalide.');
        }
        if ($quantiteAAcheter <= 0) {
            throw new \InvalidArgumentException('La quantite a acheter doit etre strictement positive.');
        }

        $dateNormalisee = null;
        if ($dateAchat !== null && trim($dateAchat) !== '') {
            $horodatage = strtotime($dateAchat);
            if ($horodatage === false) {
                throw new \InvalidArgumentException('Date achat invalide.');
            }
            $dateNormalisee = date('Y-m-d H:i:s', $horodatage);
        }

        $commentaireNettoye = trim((string) $commentaire);
        if ($commentaireNettoye === '') {
            $commentaireNettoye = null;
        }

        $this->depotAchat->demarrerTransaction();

        try {
            $besoin = $this->depotAchat->obtenirBesoinVerrouille($idBesoin);
            if (empty($besoin) === true) {
                throw new \RuntimeException('Besoin introuvable.');
            }

            $status = (string) ($besoin['status'] ?? '');
            if ($status !== 'non_dispatche') {
                throw new \RuntimeException('Achat impossible: ce besoin est deja traite (dispatche ou achete).');
            }

            $idProduit = (int) ($besoin['idProduit'] ?? 0);
            $idUnite = (int) ($besoin['idUnite'] ?? 0);
            $idVille = (int) ($besoin['idVille'] ?? 0);
            $quantiteRestanteTable = (float) ($besoin['quantite'] ?? 0);
            $quantiteInitiale = (float) ($besoin['quantiteInitiale'] ?? $quantiteRestanteTable);
            $prixUnitaire = (float) ($besoin['prixUnitaire'] ?? 0);

            if ($idProduit <= 0 || $idUnite <= 0 || $idVille <= 0) {
                throw new \RuntimeException('Besoin invalide pour achat.');
            }

            $quantiteDejaAchetee = $this->depotAchat->obtenirQuantiteAcheteeBesoinVerrouille($idBesoin);
            $quantiteRestanteSelonAchats = max(0.0, $quantiteInitiale - $quantiteDejaAchetee);
            $quantiteBesoinRestant = min($quantiteRestanteTable, $quantiteRestanteSelonAchats);

            if ($quantiteBesoinRestant <= self::MARGE_FLOTTANTE) {
                throw new \RuntimeException('Achat impossible: ce besoin a deja ete entierement achete.');
            }

            if (($quantiteAAcheter - $quantiteBesoinRestant) > self::MARGE_FLOTTANTE) {
                throw new \RuntimeException('Achat impossible: quantite demandee superieure au besoin restant.');
            }

            $stockExistant = $this->depotAchat->obtenirStockVerrouille($idProduit, $idUnite);
            $quantiteStockRestante = (float) ($stockExistant['quantite'] ?? 0);
            if ($quantiteStockRestante > self::MARGE_FLOTTANTE) {
                throw new \RuntimeException('Achat impossible: ce produit existe encore dans les dons restants.');
            }

            $configurationArgent = $this->depotAchat->obtenirProduitArgentUniteAr();
            $idProduitArgent = (int) ($configurationArgent['idProduit'] ?? 0);
            $idUniteArgent = (int) ($configurationArgent['idUnite'] ?? 0);
            if ($idProduitArgent <= 0 || $idUniteArgent <= 0) {
                throw new \RuntimeException('Configuration argent introuvable (produit Argent / unite Ar).');
            }

            $tauxFrais = $this->depotAchat->obtenirTauxFraisAchatActif();
            if ($tauxFrais < 0) {
                throw new \RuntimeException('Taux de frais d\'achat invalide.');
            }

            $montantSousTotal = round($quantiteAAcheter * $prixUnitaire, 2);
            $montantFrais = round($montantSousTotal * ($tauxFrais / 100), 2);
            $montantTotal = round($montantSousTotal + $montantFrais, 2);

            $montantArgentDistribueVille = $this->depotAchat->obtenirMontantArgentDistribueVilleVerrouille(
                $idVille,
                $idProduitArgent,
                $idUniteArgent
            );
            $montantTotalAchatsVille = $this->depotAchat->obtenirMontantTotalAchatsVilleVerrouille($idVille);
            $soldeDisponible = max(0.0, $montantArgentDistribueVille - $montantTotalAchatsVille);

            if (($montantTotal - $soldeDisponible) > self::MARGE_FLOTTANTE) {
                throw new \RuntimeException(
                    sprintf(
                        'Fonds insuffisants pour cette ville: argent distribue disponible %.2f Ar, achat requis %.2f Ar.',
                        $soldeDisponible,
                        $montantTotal
                    )
                );
            }

            $this->depotAchat->insererAchat(
                $idBesoin,
                $idVille,
                $idProduit,
                $idUnite,
                $quantiteAAcheter,
                $prixUnitaire,
                $tauxFrais,
                $montantSousTotal,
                $montantFrais,
                $montantTotal,
                $dateNormalisee,
                $commentaireNettoye
            );

            $idStock = (int) ($stockExistant['idStock'] ?? 0);
            if ($idStock > 0) {
                $this->depotAchat->incrementerStock($idStock, $quantiteAAcheter);
            } else {
                $this->depotAchat->insererStock($idProduit, $idUnite, $quantiteAAcheter);
            }

            $this->depotAchat->insererMouvementAchat($idProduit, $idUnite, $quantiteAAcheter, $dateNormalisee);

            $quantiteRestanteApresAchat = $quantiteBesoinRestant - $quantiteAAcheter;
            if ($quantiteRestanteApresAchat <= self::MARGE_FLOTTANTE) {
                $this->depotAchat->marquerBesoinAchete($idBesoin);
            } else {
                $this->depotAchat->mettreAJourQuantiteBesoin($idBesoin, $quantiteRestanteApresAchat);
            }

            $this->depotAchat->validerTransaction();
        } catch (\Throwable $exception) {
            if ($this->depotAchat->estEnTransaction() === true) {
                $this->depotAchat->annulerTransaction();
            }
            throw $exception;
        }
    }

    /**
     * @param array<int,array<string,mixed>> $achats
     * @return array<string,mixed>
     */
    private function construireResume(array $achats): array
    {
        $montantTotal = 0.0;
        $montantFrais = 0.0;
        $montantSousTotal = 0.0;
        $quantiteTotale = 0.0;

        foreach ($achats as $achat) {
            if (($achat['statut'] ?? '') !== 'saisi') {
                continue;
            }
            $quantiteTotale += (float) ($achat['quantite'] ?? 0);
            $montantSousTotal += (float) ($achat['montantSousTotal'] ?? 0);
            $montantFrais += (float) ($achat['montantFrais'] ?? 0);
            $montantTotal += (float) ($achat['montantTotal'] ?? 0);
        }

        return [
            'total_achats' => count($achats),
            'quantite_totale' => $quantiteTotale,
            'montant_sous_total' => $montantSousTotal,
            'montant_total' => $montantTotal,
            'montant_frais_total' => $montantFrais,
        ];
    }
}
