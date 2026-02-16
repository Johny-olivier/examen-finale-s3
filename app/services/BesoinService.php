<?php
declare(strict_types=1);

namespace app\services;

use app\repositories\BesoinRepository;

class BesoinService
{
    private BesoinRepository $depotBesoin;

    public function __construct(BesoinRepository $depotBesoin)
    {
        $this->depotBesoin = $depotBesoin;
    }

    /**
     * @return array<string,mixed>
     */
    public function obtenirDonneesPage(?int $idVilleFiltre = null): array
    {
        $besoins = $this->depotBesoin->obtenirBesoins($idVilleFiltre);
        $resume = $this->construireResume($besoins);

        return [
            'villes' => $this->depotBesoin->obtenirVilles(),
            'produits' => $this->depotBesoin->obtenirProduits(),
            'unites' => $this->depotBesoin->obtenirUnites(),
            'besoins' => $besoins,
            'resume' => $resume,
            'id_ville_filtre' => $idVilleFiltre,
        ];
    }

    public function insererBesoin(
        int $idVille,
        int $idProduit,
        float $quantite,
        int $idUnite,
        ?string $dateBesoin = null
    ): void {
        if ($idVille <= 0) {
            throw new \InvalidArgumentException('Ville invalide.');
        }
        if ($idProduit <= 0) {
            throw new \InvalidArgumentException('Produit invalide.');
        }
        if ($idUnite <= 0) {
            throw new \InvalidArgumentException('Unite invalide.');
        }
        if ($quantite <= 0) {
            throw new \InvalidArgumentException('La quantite doit etre strictement positive.');
        }

        $dateNormalisee = null;
        if ($dateBesoin !== null && trim($dateBesoin) !== '') {
            $horodatage = strtotime($dateBesoin);
            if ($horodatage === false) {
                throw new \InvalidArgumentException('Date de besoin invalide.');
            }
            $dateNormalisee = date('Y-m-d H:i:s', $horodatage);
        }

        $this->depotBesoin->insererBesoin($idVille, $idProduit, $quantite, $idUnite, $dateNormalisee);
    }

    /**
     * @param array<int,array<string,mixed>> $besoins
     * @return array<string,mixed>
     */
    private function construireResume(array $besoins): array
    {
        $quantiteTotale = 0.0;
        $nonDispatche = 0;
        $dispatche = 0;

        foreach ($besoins as $besoin) {
            $quantiteTotale += (float) ($besoin['quantite'] ?? 0);
            if (($besoin['status'] ?? '') === 'dispatche') {
                $dispatche++;
            } else {
                $nonDispatche++;
            }
        }

        return [
            'total_besoins' => count($besoins),
            'quantite_totale' => $quantiteTotale,
            'total_dispatche' => $dispatche,
            'total_non_dispatche' => $nonDispatche,
        ];
    }
}
