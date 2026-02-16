<?php
/** @var array<string,mixed> $donnees */
/** @var string $message_succes */
/** @var string $message_erreur */

$echapper = static function ($valeur): string {
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
};

$formaterNombre = static function (float $valeur): string {
    return number_format($valeur, 2, ',', ' ');
};

$produits = $donnees['produits'] ?? [];
$unites = $donnees['unites'] ?? [];
$stockDetaille = $donnees['stock_detaille'] ?? [];
$resume = $donnees['resume'] ?? [];
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
    <div>
        <h1 class="page-title h2 mb-2">Ajouter stock BNGRC</h1>
        <p class="page-subtitle mb-0">
            Ajouter des quantites au stock et enregistrer automatiquement un mouvement de type don.
        </p>
    </div>
</div>

<?php if ($message_succes !== ''): ?>
    <div class="alert alert-success border-0 shadow-sm" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?= $echapper($message_succes) ?>
    </div>
<?php endif; ?>

<?php if ($message_erreur !== ''): ?>
    <div class="alert alert-danger border-0 shadow-sm" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $echapper($message_erreur) ?>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-total">
            <p class="stat-label"><i class="fa-solid fa-layer-group me-1"></i>Lignes de stock</p>
            <p class="stat-value"><?= (int) ($resume['nombre_lignes'] ?? 0) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-ok">
            <p class="stat-label"><i class="fa-solid fa-cubes me-1"></i>Quantite totale</p>
            <p class="stat-value"><?= $formaterNombre((float) ($resume['quantite_totale'] ?? 0)) ?></p>
        </article>
    </div>
</div>

<section class="section-card mb-4">
    <div class="section-card-header">
        <h2 class="section-title">Formulaire d'initialisation</h2>
    </div>
    <div class="p-3 p-lg-4">
        <form method="post" action="<?= $echapper(BASE_URL . 'stock/initialisation') ?>" class="row g-3">
            <div class="col-md-5">
                <label for="id_produit" class="form-label">Produit</label>
                <select class="form-select" id="id_produit" name="id_produit" required>
                    <option value="">Selectionner un produit</option>
                    <?php foreach ($produits as $produit): ?>
                        <option value="<?= (int) ($produit['idProduit'] ?? 0) ?>">
                            <?= $echapper(($produit['produit'] ?? '') . ' - ' . ($produit['categorie'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="id_unite" class="form-label">Unite</label>
                <select class="form-select" id="id_unite" name="id_unite" required>
                    <option value="">Selectionner une unite</option>
                    <?php foreach ($unites as $unite): ?>
                        <option value="<?= (int) ($unite['idUnite'] ?? 0) ?>">
                            <?= $echapper($unite['nom'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="quantite" class="form-label">Quantite a ajouter</label>
                <input
                    type="number"
                    class="form-control"
                    id="quantite"
                    name="quantite"
                    min="0.01"
                    step="0.01"
                    placeholder="Ex: 50"
                    required
                >
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-dispatch">
                    <i class="fa-solid fa-plus me-2"></i>
                    Ajouter au stock
                </button>
            </div>
        </form>
    </div>
</section>

<section class="section-card">
    <div class="section-card-header d-flex justify-content-between align-items-center">
        <h2 class="section-title">Etat actuel du stock</h2>
        <span class="badge text-bg-light">Apercu immediat</span>
    </div>
    <?php if (count($stockDetaille) === 0): ?>
        <div class="empty-state">
            <i class="fa-solid fa-warehouse"></i>
            <p class="mb-0">Aucune ligne de stock pour le moment.</p>
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
