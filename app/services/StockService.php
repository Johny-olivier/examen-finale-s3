<?php
declare(strict_types=1);

namespace app\services;

use app\repositories\StockRepository;

class StockService
{
    private StockRepository $depotStock;

    public function __construct(StockRepository $depotStock)
    {
        $this->depotStock = $depotStock;
    }

    /**
     * @return array<string,mixed>
     */
    public function obtenirDonneesInitialisation(): array
    {
        $stockDetaille = $this->depotStock->obtenirStockDetaille();

        return [
            'produits' => $this->depotStock->obtenirProduits(),
            'unites' => $this->depotStock->obtenirUnites(),
            'stock_detaille' => $stockDetaille,
            'resume' => $this->construireResumeStock($stockDetaille),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function ajouterStockInitial(int $idProduit, int $idUnite, float $quantite): array
    {
        if ($idProduit <= 0 || $idUnite <= 0) {
            throw new \InvalidArgumentException('Produit ou unite invalide.');
        }
        if ($quantite <= 0) {
            throw new \InvalidArgumentException('La quantite doit etre strictement positive.');
        }

        $this->depotStock->demarrerTransaction();

        try {
            $stockExistant = $this->depotStock->obtenirStockVerrouille($idProduit, $idUnite);
            $idStock = (int) ($stockExistant['idStock'] ?? 0);

            if ($idStock > 0) {
                $this->depotStock->incrementerStock($idStock, $quantite);
            } else {
                $this->depotStock->insererStock($idProduit, $idUnite, $quantite);
            }

            $this->depotStock->insererMouvementDon($idProduit, $idUnite, $quantite);
            $this->depotStock->validerTransaction();

            return [
                'id_produit' => $idProduit,
                'id_unite' => $idUnite,
                'quantite_ajoutee' => $quantite,
            ];
        } catch (\Throwable $exception) {
            if ($this->depotStock->estEnTransaction() === true) {
                $this->depotStock->annulerTransaction();
            }
            throw $exception;
        }
    }

    /**
     * @return array<string,mixed>
     */
    public function obtenirDonneesConsultation(): array
    {
        $stockDetaille = $this->depotStock->obtenirStockDetaille();

        return [
            'stock_detaille' => $stockDetaille,
            'resume' => $this->construireResumeStock($stockDetaille),
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $stockDetaille
     * @return array<string,mixed>
     */
    private function construireResumeStock(array $stockDetaille): array
    {
        $totalQuantite = 0.0;
        foreach ($stockDetaille as $ligne) {
            $totalQuantite += (float) ($ligne['quantite'] ?? 0);
        }

        return [
            'nombre_lignes' => count($stockDetaille),
            'quantite_totale' => $totalQuantite,
        ];
    }
}
