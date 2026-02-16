<?php
/** @var array<string,mixed> $donnees_simulation */
/** @var string $message_succes */
/** @var string $message_erreur */

$echapper = static function ($valeur): string {
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
};

$formaterNombre = static function (float $valeur): string {
    return number_format($valeur, 2, ',', ' ');
};

$formaterDate = static function (string $valeur): string {
    $horodatage = strtotime($valeur);
    if ($horodatage === false) {
        return $valeur;
    }
    return date('d/m/Y H:i', $horodatage);
};

$statistiques = $donnees_simulation['statistiques'] ?? [];
$besoins = $donnees_simulation['besoins'] ?? [];
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
    <div>
        <h1 class="page-title h2 mb-2">Page de distribution des dons</h1>
        <p class="page-subtitle mb-0">
            La simulation respecte l'ordre de priorite par date de besoin, puis par ordre de saisie.
        </p>
    </div>
    <form method="post" action="<?= $echapper(BASE_URL . 'distribution/valider') ?>">
        <button type="submit" class="btn btn-dispatch">
            <i class="fa-solid fa-check me-2"></i>
            Valider le dispatch
        </button>
    </form>
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
            <p class="stat-label"><i class="fa-solid fa-list-check me-1"></i>Besoins en attente</p>
            <p class="stat-value"><?= (int) ($statistiques['total_besoins'] ?? 0) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-ok">
            <p class="stat-label"><i class="fa-solid fa-thumbs-up me-1"></i>Dispatchables</p>
            <p class="stat-value"><?= (int) ($statistiques['total_dispatcheables'] ?? 0) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-partiel">
            <p class="stat-label"><i class="fa-solid fa-hourglass-half me-1"></i>Partiels</p>
            <p class="stat-value"><?= (int) ($statistiques['total_partiels'] ?? 0) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-vide">
            <p class="stat-label"><i class="fa-solid fa-ban me-1"></i>Non servis</p>
            <p class="stat-value"><?= (int) ($statistiques['total_non_servis'] ?? 0) ?></p>
        </article>
    </div>
</div>

<section class="section-card mb-4">
    <div class="section-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h2 class="section-title">Details de la simulation</h2>
        <span class="badge text-bg-light">Tri: date ASC puis idBesoin ASC</span>
    </div>

    <?php if (count($besoins) === 0): ?>
        <div class="empty-state">
            <i class="fa-solid fa-inbox"></i>
            <p class="mb-0">Aucun besoin non dispatche.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Date besoin</th>
                    <th>Region / Ville</th>
                    <th>Produit</th>
                    <th class="text-end">Besoin</th>
                    <th class="text-end">Stock avant</th>
                    <th class="text-end">Distribuable</th>
                    <th class="text-end">Reste</th>
                    <th class="text-center">Etat</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($besoins as $indice => $besoin): ?>
                    <?php
                    $seraDispatche = (($besoin['sera_dispatche'] ?? false) === true);
                    $quantiteDistribuable = (float) ($besoin['quantite_distribuable'] ?? 0);

                    $classeBadge = 'text-bg-secondary';
                    $texteBadge = 'Non servi';
                    if ($seraDispatche === true) {
                        $classeBadge = 'text-bg-success';
                        $texteBadge = 'Dispatche';
                    } elseif ($quantiteDistribuable > 0) {
                        $classeBadge = 'text-bg-warning';
                        $texteBadge = 'Partiel';
                    }
                    ?>
                    <tr>
                        <td><?= (int) $indice + 1 ?></td>
                        <td><?= $echapper($formaterDate((string) ($besoin['date'] ?? ''))) ?></td>
                        <td>
                            <div class="fw-semibold"><?= $echapper($besoin['region'] ?? '') ?></div>
                            <small class="text-secondary"><?= $echapper($besoin['ville'] ?? '') ?></small>
                        </td>
                        <td><?= $echapper(($besoin['produit'] ?? '') . ' (' . ($besoin['unite'] ?? '') . ')') ?></td>
                        <td class="text-end"><?= $formaterNombre((float) ($besoin['quantite_besoin'] ?? 0)) ?></td>
                        <td class="text-end"><?= $formaterNombre((float) ($besoin['quantite_stock_avant'] ?? 0)) ?></td>
                        <td class="text-end"><?= $formaterNombre($quantiteDistribuable) ?></td>
                        <td class="text-end"><?= $formaterNombre((float) ($besoin['quantite_restante'] ?? 0)) ?></td>
                        <td class="text-center">
                            <span class="badge badge-etat <?= $echapper($classeBadge) ?>"><?= $echapper($texteBadge) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="section-card">
    <div class="section-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h2 class="section-title">Stock theorique apres simulation</h2>
        <span class="badge text-bg-light">Projection avant validation</span>
    </div>

    <?php if (empty($donnees_simulation['stock_apres_simulation']) === true): ?>
        <div class="empty-state">
            <i class="fa-solid fa-warehouse"></i>
            <p class="mb-0">Aucun stock disponible.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>Produit</th>
                    <th>Unite</th>
                    <th class="text-end">Stock restant</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($donnees_simulation['stock_apres_simulation'] as $ligneStock): ?>
                    <tr>
                        <td class="fw-semibold"><?= $echapper($ligneStock['produit'] ?? '') ?></td>
                        <td><?= $echapper($ligneStock['unite'] ?? '') ?></td>
                        <td class="text-end"><?= $formaterNombre((float) ($ligneStock['quantite'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
