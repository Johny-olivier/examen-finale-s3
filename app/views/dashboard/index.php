<?php
/** @var array<string,mixed> $donnees */

$indicateurs = $donnees['indicateurs'] ?? [];
$resumeBesoinsParVille = $donnees['resume_besoins_par_ville'] ?? [];
$resumeDonsRecus = $donnees['resume_dons_recus'] ?? [];
$resumeDistributionsParVille = $donnees['resume_distributions_par_ville'] ?? [];
$etatGlobalStock = $donnees['etat_global_stock'] ?? [];
$detailsParVille = $donnees['details_par_ville'] ?? [];
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
            <p class="stat-value"><?= vue_formater_nombre((float) ($indicateurs['quantite_totale_dons_recus'] ?? 0)) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-2">
        <article class="stat-card stat-partiel">
            <p class="stat-label">Qte distribuee (hors argent)</p>
            <p class="stat-value"><?= vue_formater_nombre((float) ($indicateurs['quantite_totale_distribuee'] ?? 0)) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-2">
        <article class="stat-card stat-total">
            <p class="stat-label">Stock global</p>
            <p class="stat-value"><?= vue_formater_nombre((float) ($indicateurs['quantite_totale_stock'] ?? 0)) ?></p>
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
                    <th class="text-end">Traites</th>
                    <th class="text-end">Non dispatches</th>
                    <th class="text-end">Quantite restante</th>
                    <th class="text-center">Details</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($resumeBesoinsParVille as $ligne): ?>
                    <?php
                    $idVille = (int) ($ligne['idVille'] ?? 0);
                    $libelleVille = trim((string) ($ligne['region'] ?? '') . ' - ' . (string) ($ligne['ville'] ?? ''));
                    ?>
                    <tr>
                        <td><?= vue_echapper($ligne['region'] ?? '') ?></td>
                        <td class="fw-semibold"><?= vue_echapper($ligne['ville'] ?? '') ?></td>
                        <td class="text-end"><?= (int) ($ligne['total_besoins'] ?? 0) ?></td>
                        <td class="text-end"><?= (int) ($ligne['total_dispatche'] ?? 0) ?></td>
                        <td class="text-end"><?= (int) ($ligne['total_non_dispatche'] ?? 0) ?></td>
                        <td class="text-end"><?= vue_formater_nombre((float) ($ligne['quantite_restante'] ?? 0)) ?></td>
                        <td class="text-center">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary bouton-voir-details-ville"
                                data-id-ville="<?= $idVille ?>"
                                data-libelle-ville="<?= vue_echapper($libelleVille) ?>"
                            >
                                Voir details
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<div id="lightbox-details-ville" class="lightbox-dashboard" hidden>
    <div class="lightbox-dialogue" role="dialog" aria-modal="true" aria-labelledby="titre-lightbox-ville">
        <div class="lightbox-entete">
            <h3 class="lightbox-titre mb-0" id="titre-lightbox-ville">Details ville</h3>
            <button type="button" class="lightbox-fermer" data-fermer-lightbox aria-label="Fermer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="lightbox-contenu p-3 p-lg-4" id="contenu-lightbox-ville"></div>
    </div>
</div>

<?php foreach ($detailsParVille as $idVille => $detailVille): ?>
    <?php
    $besoinsVille = $detailVille['besoins'] ?? [];
    $donsDistribuesVille = $detailVille['dons_distribues'] ?? [];
    $regionVille = (string) ($detailVille['region'] ?? '');
    $nomVille = (string) ($detailVille['ville'] ?? '');
    ?>
    <template id="template-ville-<?= (int) $idVille ?>">
        <div class="lightbox-intro mb-3">
            <span class="badge text-bg-light me-2"><?= vue_echapper($regionVille) ?></span>
            <span class="fw-semibold"><?= vue_echapper($nomVille) ?></span>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <section class="lightbox-section h-100">
                    <h4 class="lightbox-section-titre">Besoins de la ville</h4>
                    <?php if (count($besoinsVille) === 0): ?>
                        <p class="text-secondary mb-0">Aucun besoin saisi pour cette ville.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Produit</th>
                                    <th class="text-end">Quantite</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($besoinsVille as $besoin): ?>
                                    <?php
                                    $statusBesoin = (string) ($besoin['status'] ?? '');
                                    $metaStatus = vue_meta_statut_besoin($statusBesoin);
                                    $classeBadge = $metaStatus['classe'];
                                    $libelleStatus = $metaStatus['libelle'];
                                    ?>
                                    <tr>
                                        <td><?= (int) ($besoin['idBesoin'] ?? 0) ?></td>
                                        <td>
                                            <div class="fw-semibold"><?= vue_echapper($besoin['produit'] ?? '') ?></div>
                                            <small class="text-secondary"><?= vue_echapper($besoin['unite'] ?? '') ?></small>
                                        </td>
                                        <td class="text-end"><?= vue_formater_quantite((float) ($besoin['quantite'] ?? 0), (string) ($besoin['unite'] ?? '')) ?></td>
                                        <td><span class="badge <?= vue_echapper($classeBadge) ?>"><?= vue_echapper($libelleStatus) ?></span></td>
                                        <td><?= vue_echapper(vue_formater_date_humaine($besoin['date'] ?? '')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <div class="col-lg-6">
                <section class="lightbox-section h-100">
                    <h4 class="lightbox-section-titre">Dons obtenus par la ville</h4>
                    <p class="text-secondary small">
                        Source: distributions manuelles + distributions issues de la simulation validee.
                    </p>
                    <?php if (count($donsDistribuesVille) === 0): ?>
                        <p class="text-secondary mb-0">Aucun don distribue pour cette ville.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th># Dist</th>
                                    <th>Produit</th>
                                    <th class="text-end">Quantite obtenue</th>
                                    <th>Date distribution</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($donsDistribuesVille as $distribution): ?>
                                    <tr>
                                        <td><?= (int) ($distribution['idDistribution'] ?? 0) ?></td>
                                        <td>
                                            <div class="fw-semibold"><?= vue_echapper($distribution['produit'] ?? '') ?></div>
                                            <small class="text-secondary"><?= vue_echapper($distribution['unite'] ?? '') ?></small>
                                        </td>
                                        <td class="text-end"><?= vue_formater_quantite((float) ($distribution['quantite'] ?? 0), (string) ($distribution['unite'] ?? '')) ?></td>
                                        <td><?= vue_echapper(vue_formater_date_humaine($distribution['date'] ?? '')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </template>
<?php endforeach; ?>

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
                    <th class="text-end">Nombre de dons</th>
                    <th class="text-end">Quantite totale</th>
                    <th>Dernier don</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($resumeDonsRecus as $ligne): ?>
                    <tr>
                        <td><?= (int) ($ligne['idProduit'] ?? 0) ?></td>
                        <td class="fw-semibold"><?= vue_echapper($ligne['produit'] ?? '') ?></td>
                        <td class="text-end"><?= (int) ($ligne['nombre_dons'] ?? 0) ?></td>
                        <td class="text-end"><?= vue_formater_quantite((float) ($ligne['quantite_totale'] ?? 0), (string) ($ligne['unite'] ?? '')) ?></td>
                        <td><?= vue_echapper(vue_formater_date_humaine($ligne['dernier_don'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<script
    nonce="<?= vue_echapper((string) \Flight::get('csp_nonce')) ?>"
    src="<?= vue_echapper(BASE_URL . 'assets/js/dashboard-details.js') ?>"
></script>

<section class="section-card mb-4">
    <div class="section-card-header">
        <h2 class="section-title">Resume des dons distribues par ville</h2>
    </div>
    <div class="px-3 pt-3">
        <p class="text-secondary small mb-0">
            Cette vue est basee sur la table <strong>DistributionVille</strong> (manuel + simulation validee).
        </p>
        <p class="text-secondary small mb-0">
            La colonne "Quantite distribuee" exclut l'argent, affiche separement dans "Argent distribue".
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
                    <th class="text-end">Nb distributions</th>
                    <th class="text-end">Quantite distribuee</th>
                    <th class="text-end">Argent distribue</th>
                    <th class="text-end">Besoins traites</th>
                    <th class="text-end">Besoins en cours</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($resumeDistributionsParVille as $ligne): ?>
                    <tr>
                        <td><?= vue_echapper($ligne['region'] ?? '') ?></td>
                        <td class="fw-semibold"><?= vue_echapper($ligne['ville'] ?? '') ?></td>
                        <td class="text-end"><?= (int) ($ligne['total_distributions'] ?? 0) ?></td>
                        <td class="text-end"><?= vue_formater_nombre((float) ($ligne['quantite_distribuee'] ?? 0)) ?></td>
                        <td class="text-end"><?= vue_formater_montant_ar((float) ($ligne['montant_argent_distribue'] ?? 0)) ?></td>
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
                    <th class="text-end">Quantite disponible</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($etatGlobalStock as $ligne): ?>
                    <tr>
                        <td><?= (int) ($ligne['idProduit'] ?? 0) ?></td>
                        <td class="fw-semibold"><?= vue_echapper($ligne['produit'] ?? '') ?></td>
                        <td class="text-end"><?= vue_formater_quantite((float) ($ligne['quantite'] ?? 0), (string) ($ligne['unite'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
