<?php
/** @var array<string,mixed> $donnees */

$echapper = static function ($valeur): string {
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
};

$formaterNombre = static function (float $valeur): string {
    return number_format($valeur, 2, ',', ' ');
};

$stockDetaille = $donnees['stock_detaille'] ?? [];
$resume = $donnees['resume'] ?? [];
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
    <div>
        <h1 class="page-title h2 mb-2">Consultation du stock</h1>
        <p class="page-subtitle mb-0">
            Visualisation des disponibilites actuelles par produit et unite.
        </p>
    </div>
    <a href="<?= $echapper(BASE_URL . 'stock/initialisation') ?>" class="btn btn-dispatch">
        <i class="fa-solid fa-boxes-stacked me-2"></i>
        Initialiser le stock
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-total">
            <p class="stat-label"><i class="fa-solid fa-layer-group me-1"></i>Lignes de stock</p>
            <p class="stat-value"><?= (int) ($resume['nombre_lignes'] ?? 0) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-ok">
            <p class="stat-label"><i class="fa-solid fa-cubes me-1"></i>Quantite disponible</p>
            <p class="stat-value"><?= $formaterNombre((float) ($resume['quantite_totale'] ?? 0)) ?></p>
        </article>
    </div>
</div>

<section class="section-card">
    <div class="section-card-header d-flex justify-content-between align-items-center">
        <h2 class="section-title">Stock BNGRC (idProduit / quantite)</h2>
        <span class="badge text-bg-light">Etat global</span>
    </div>

    <?php if (count($stockDetaille) === 0): ?>
        <div class="empty-state">
            <i class="fa-solid fa-box-open"></i>
            <p class="mb-0">Aucun stock disponible.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>idProduit</th>
                    <th>Produit</th>
                    <th>Unite</th>
                    <th class="text-end">Quantite disponible</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($stockDetaille as $ligne): ?>
                    <tr>
                        <td><?= (int) ($ligne['idProduit'] ?? 0) ?></td>
                        <td class="fw-semibold"><?= $echapper($ligne['produit'] ?? '') ?></td>
                        <td><?= $echapper($ligne['unite'] ?? '') ?></td>
                        <td class="text-end"><?= $formaterNombre((float) ($ligne['quantite'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
