<?php
declare(strict_types=1);

namespace app\services;

use app\repositories\DonRepository;

class DonService
{
    private DonRepository $depotDon;

    public function __construct(DonRepository $depotDon)
    {
        $this->depotDon = $depotDon;
    }

    /**
     * @return array<string,mixed>
     */
    public function obtenirDonneesPage(): array
    {
        $donsRecents = $this->depotDon->obtenirDonsRecents();

        return [
            'produits' => $this->depotDon->obtenirProduits(),
            'unites' => $this->depotDon->obtenirUnites(),
            'dons_recents' => $donsRecents,
            'resume' => $this->construireResume($donsRecents),
        ];
    }

    public function insererDon(
        int $idProduit,
        int $idUnite,
        float $quantite,
        string $donateur,
        ?string $dateDon = null
    ): void {
        if ($idProduit <= 0) {
            throw new \InvalidArgumentException('Produit invalide.');
        }
        if ($idUnite <= 0) {
            throw new \InvalidArgumentException('Unite invalide.');
        }
        if ($quantite <= 0) {
            throw new \InvalidArgumentException('La quantite doit etre strictement positive.');
        }

        $donateurNettoye = trim($donateur);
        if ($donateurNettoye === '') {
            $donateurNettoye = 'Donateur anonyme';
        }

        $dateNormalisee = null;
        if ($dateDon !== null && trim($dateDon) !== '') {
            $horodatage = strtotime($dateDon);
            if ($horodatage === false) {
                throw new \InvalidArgumentException('Date de don invalide.');
            }
            $dateNormalisee = date('Y-m-d H:i:s', $horodatage);
        }

        $this->depotDon->demarrerTransaction();

        try {
            $stockExistant = $this->depotDon->obtenirStockVerrouille($idProduit, $idUnite);
            $idStock = (int) ($stockExistant['idStock'] ?? 0);

            if ($idStock > 0) {
                $this->depotDon->incrementerStock($idStock, $quantite);
            } else {
                $this->depotDon->insererStock($idProduit, $idUnite, $quantite);
            }

            $this->depotDon->insererDon($idProduit, $idUnite, $quantite, $donateurNettoye, $dateNormalisee);
            $this->depotDon->insererMouvementDon($idProduit, $idUnite, $quantite);

            $this->depotDon->validerTransaction();
        } catch (\Throwable $exception) {
            if ($this->depotDon->estEnTransaction() === true) {
                $this->depotDon->annulerTransaction();
            }
            throw $exception;
        }
    }

    /**
     * @param array<int,array<string,mixed>> $donsRecents
     * @return array<string,mixed>
     */
    private function construireResume(array $donsRecents): array
    {
        $quantiteTotale = 0.0;
        foreach ($donsRecents as $don) {
            $quantiteTotale += (float) ($don['quantite'] ?? 0);
        }

        return [
            'total_dons' => count($donsRecents),
            'quantite_totale' => $quantiteTotale,
        ];
    }
}
