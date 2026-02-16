<?php
/** @var array<string,mixed> $donnees */

$echapper = static function ($valeur): string {
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
};

$formaterNombre = static function (float $valeur): string {
    return number_format($valeur, 2, ',', ' ');
};

$formaterDate = static function ($valeur): string {
    $date = (string) $valeur;
    if ($date === '') {
        return '-';
    }
    $horodatage = strtotime($date);
    if ($horodatage === false) {
        return $date;
    }
    return date('d/m/Y H:i', $horodatage);
};

$indicateurs = $donnees['indicateurs'] ?? [];
$resumeBesoinsParVille = $donnees['resume_besoins_par_ville'] ?? [];
$resumeDonsRecus = $donnees['resume_dons_recus'] ?? [];
$resumeDistributionsParVille = $donnees['resume_distributions_par_ville'] ?? [];
$etatGlobalStock = $donnees['etat_global_stock'] ?? [];
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
    <div>
        <h1 class="page-title h2 mb-2">Dashboard</h1>
        <p class="page-subtitle mb-0">
            Vue d'ensemble des besoins, dons, distributions et etat global du stock.
        </p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-2">
        <article class="stat-card stat-total">
            <p class="stat-label">Besoins (total)</p>
            <p class="stat-value"><?= (int) ($indicateurs['total_besoins'] ?? 0) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-2">
        <article class="stat-card stat-vide">
            <p class="stat-label">Besoins non dispatches</p>
            <p class="stat-value"><?= (int) ($indicateurs['total_besoins_non_dispatche'] ?? 0) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-2">
        <article class="stat-card stat-ok">
            <p class="stat-label">Dons recus</p>
            <p class="stat-value"><?= (int) ($indicateurs['total_dons_recus'] ?? 0) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-2">
        <article class="stat-card stat-ok">
            <p class="stat-label">Qte dons recu</p>
            <p class="stat-value"><?= $formaterNombre((float) ($indicateurs['quantite_totale_dons_recus'] ?? 0)) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-2">
        <article class="stat-card stat-partiel">
            <p class="stat-label">Qte distribuee</p>
            <p class="stat-value"><?= $formaterNombre((float) ($indicateurs['quantite_totale_distribuee'] ?? 0)) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-2">
        <article class="stat-card stat-total">
            <p class="stat-label">Stock global</p>
            <p class="stat-value"><?= $formaterNombre((float) ($indicateurs['quantite_totale_stock'] ?? 0)) ?></p>
        </article>
    </div>
</div>

<section class="section-card mb-4">
    <div class="section-card-header">
        <h2 class="section-title">Resume des besoins par ville</h2>
    </div>
    <?php if (count($resumeBesoinsParVille) === 0): ?>
        <div class="empty-state">
            <i class="fa-solid fa-city"></i>
            <p class="mb-0">Aucune donnee de besoins.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>Region</th>
                    <th>Ville</th>
                    <th class="text-end">Total besoins</th>
                    <th class="text-end">Dispatches</th>
                    <th class="text-end">Non dispatches</th>
                    <th class="text-end">Quantite restante</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($resumeBesoinsParVille as $ligne): ?>
                    <tr>
                        <td><?= $echapper($ligne['region'] ?? '') ?></td>
                        <td class="fw-semibold"><?= $echapper($ligne['ville'] ?? '') ?></td>
                        <td class="text-end"><?= (int) ($ligne['total_besoins'] ?? 0) ?></td>
                        <td class="text-end"><?= (int) ($ligne['total_dispatche'] ?? 0) ?></td>
                        <td class="text-end"><?= (int) ($ligne['total_non_dispatche'] ?? 0) ?></td>
                        <td class="text-end"><?= $formaterNombre((float) ($ligne['quantite_restante'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="section-card mb-4">
    <div class="section-card-header">
        <h2 class="section-title">Resume des dons recus</h2>
    </div>
    <?php if (count($resumeDonsRecus) === 0): ?>
        <div class="empty-state">
            <i class="fa-solid fa-hand-holding-heart"></i>
            <p class="mb-0">Aucun don recu pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>idProduit</th>
                    <th>Produit</th>
                    <th>Unite</th>
                    <th class="text-end">Nombre de dons</th>
                    <th class="text-end">Quantite totale</th>
                    <th>Dernier don</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($resumeDonsRecus as $ligne): ?>
                    <tr>
                        <td><?= (int) ($ligne['idProduit'] ?? 0) ?></td>
                        <td class="fw-semibold"><?= $echapper($ligne['produit'] ?? '') ?></td>
                        <td><?= $echapper($ligne['unite'] ?? '') ?></td>
                        <td class="text-end"><?= (int) ($ligne['nombre_dons'] ?? 0) ?></td>
                        <td class="text-end"><?= $formaterNombre((float) ($ligne['quantite_totale'] ?? 0)) ?></td>
                        <td><?= $echapper($formaterDate($ligne['dernier_don'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="section-card mb-4">
    <div class="section-card-header">
        <h2 class="section-title">Resume des dons distribues par ville</h2>
    </div>
    <div class="px-3 pt-3">
        <p class="text-secondary small mb-0">
            Cette vue est basee sur les besoins marques "dispatche".
        </p>
    </div>
    <?php if (count($resumeDistributionsParVille) === 0): ?>
        <div class="empty-state">
            <i class="fa-solid fa-truck"></i>
            <p class="mb-0">Aucune distribution enregistree.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>Region</th>
                    <th>Ville</th>
                    <th class="text-end">Quantite distribuee</th>
                    <th class="text-end">Besoins totalement dispatches</th>
                    <th class="text-end">Besoins en cours</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($resumeDistributionsParVille as $ligne): ?>
                    <tr>
                        <td><?= $echapper($ligne['region'] ?? '') ?></td>
                        <td class="fw-semibold"><?= $echapper($ligne['ville'] ?? '') ?></td>
                        <td class="text-end"><?= $formaterNombre((float) ($ligne['quantite_distribuee'] ?? 0)) ?></td>
                        <td class="text-end"><?= (int) ($ligne['besoins_totalement_dispatches'] ?? 0) ?></td>
                        <td class="text-end"><?= (int) ($ligne['besoins_en_cours'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="section-card">
    <div class="section-card-header">
        <h2 class="section-title">Etat global du stock</h2>
    </div>
    <?php if (count($etatGlobalStock) === 0): ?>
        <div class="empty-state">
            <i class="fa-solid fa-warehouse"></i>
            <p class="mb-0">Aucune ligne de stock disponible.</p>
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
                <?php foreach ($etatGlobalStock as $ligne): ?>
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
