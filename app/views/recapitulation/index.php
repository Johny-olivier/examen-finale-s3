<?php
/** @var array<string,mixed> $donnees */
/** @var string $url_actualisation_ajax */

$resume = $donnees['resume'] ?? [];
$villes = $donnees['villes'] ?? [];
$dateActualisation = (string) ($donnees['date_actualisation'] ?? '');
$donneesInitialesJson = json_encode(
    $donnees,
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
);
if ($donneesInitialesJson === false) {
    $donneesInitialesJson = '{}';
}
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
    <div>
        <h1 class="page-title h2 mb-2">Recapitulation des besoins en montant</h1>
        <p class="page-subtitle mb-0">
            Suivi des besoins totaux, satisfaits et restants, avec actualisation Ajax.
        </p>
    </div>
    <button
        type="button"
        id="bouton-actualiser-recap"
        class="btn btn-outline-primary"
        data-url-ajax="<?= vue_echapper($url_actualisation_ajax) ?>"
    >
        <i class="fa-solid fa-rotate me-2"></i>
        Actualiser (Ajax)
    </button>
</div>

<div id="message-ajax-recap" class="alert d-none" role="alert"></div>

<div class="row g-3 mb-4" id="cartes-recapitulation">
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-total">
            <p class="stat-label"><i class="fa-solid fa-sack-dollar me-1"></i>Besoins totaux</p>
            <p class="stat-value" id="recap-montant-besoins-totaux"><?= vue_formater_montant_ar((float) ($resume['montant_besoins_totaux'] ?? 0)) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-ok">
            <p class="stat-label"><i class="fa-solid fa-circle-check me-1"></i>Besoins satisfaits</p>
            <p class="stat-value" id="recap-montant-besoins-satisfaits"><?= vue_formater_montant_ar((float) ($resume['montant_besoins_satisfaits'] ?? 0)) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-vide">
            <p class="stat-label"><i class="fa-solid fa-hourglass-half me-1"></i>Besoins restants</p>
            <p class="stat-value" id="recap-montant-besoins-restants"><?= vue_formater_montant_ar((float) ($resume['montant_besoins_restants'] ?? 0)) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-partiel">
            <p class="stat-label"><i class="fa-solid fa-percent me-1"></i>Taux de satisfaction</p>
            <p class="stat-value" id="recap-taux-satisfaction"><?= vue_formater_nombre((float) ($resume['taux_satisfaction'] ?? 0)) ?> %</p>
        </article>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-6">
        <article class="stat-card stat-total">
            <p class="stat-label"><i class="fa-solid fa-cart-shopping me-1"></i>Montant total des achats</p>
            <p class="stat-value" id="recap-montant-total-achats"><?= vue_formater_montant_ar((float) ($resume['montant_total_achats'] ?? 0)) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-6">
        <article class="stat-card stat-vide">
            <p class="stat-label"><i class="fa-solid fa-file-invoice-dollar me-1"></i>Montant total des frais</p>
            <p class="stat-value" id="recap-montant-total-frais"><?= vue_formater_montant_ar((float) ($resume['montant_total_frais'] ?? 0)) ?></p>
        </article>
    </div>
</div>

<section class="section-card">
    <div class="section-card-header d-flex justify-content-between align-items-center gap-2">
        <h2 class="section-title">Detail par ville</h2>
        <span class="badge text-bg-light" id="recap-derniere-actualisation">
            Derniere actualisation: <?= vue_echapper(vue_formater_date_humaine($dateActualisation)) ?>
        </span>
    </div>

    <div class="empty-state" id="recap-etat-vide"<?= count($villes) === 0 ? '' : ' hidden' ?>>
        <i class="fa-solid fa-city"></i>
        <p class="mb-0">Aucune donnee disponible.</p>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="tableau-recapitulation-villes">
            <thead>
            <tr>
                <th>Region</th>
                <th>Ville</th>
                <th class="text-end">Nb besoins</th>
                <th class="text-end">Montant besoins totaux</th>
                <th class="text-end">Montant satisfaits</th>
                <th class="text-end">Montant restants</th>
            </tr>
            </thead>
            <tbody id="corps-tableau-recap-villes">
            <?php foreach ($villes as $ligne): ?>
                <tr>
                    <td><?= vue_echapper($ligne['region'] ?? '') ?></td>
                    <td class="fw-semibold"><?= vue_echapper($ligne['ville'] ?? '') ?></td>
                    <td class="text-end"><?= (int) ($ligne['total_besoins'] ?? 0) ?></td>
                    <td class="text-end"><?= vue_formater_montant_ar((float) ($ligne['montant_besoins_totaux'] ?? 0)) ?></td>
                    <td class="text-end"><?= vue_formater_montant_ar((float) ($ligne['montant_besoins_satisfaits'] ?? 0)) ?></td>
                    <td class="text-end"><?= vue_formater_montant_ar((float) ($ligne['montant_besoins_restants'] ?? 0)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<script id="donnees-recapitulation-initiales" type="application/json"><?= $donneesInitialesJson ?></script>
<script
    nonce="<?= vue_echapper((string) \Flight::get('csp_nonce')) ?>"
    src="<?= vue_echapper(BASE_URL . 'assets/js/recapitulation.js') ?>"
></script>
