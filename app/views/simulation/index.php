<?php
/** @var array<string,mixed> $donnees_simulation */
/** @var bool $simulation_effectuee */
/** @var string $message_succes */
/** @var string $message_erreur */
/** @var string $mode_dispatch */
/** @var array<string,string> $modes_dispatch_disponibles */
/** @var string $libelle_mode_dispatch */

$simulationEffectuee = (($simulation_effectuee ?? false) === true);
$statistiques = $donnees_simulation['statistiques'] ?? [];
$besoins = $donnees_simulation['besoins'] ?? [];
$peutValiderDispatch = $simulationEffectuee === true && ((int) ($statistiques['total_besoins'] ?? 0) > 0);
$modeDispatchSelectionne = (string) ($mode_dispatch ?? 'date');
$modesDispatch = $modes_dispatch_disponibles ?? [];
$libelleModeDispatch = (string) ($libelle_mode_dispatch ?? '');
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
    <div>
        <h1 class="page-title h2 mb-2">Simulation et validation de dispatch</h1>
        <p class="page-subtitle mb-0">
            Simulation automatique: les villes recoivent ce qu'elles ont demande selon le mode de dispatch choisi.
        </p>
    </div>
    <div class="simulation-actions">
        <div class="simulation-actions-ligne">
            <form method="post" action="<?= vue_echapper(BASE_URL . 'simulation-dispatch/simuler') ?>" class="simulation-form-mode">
                <div class="simulation-mode-champ">
                <label for="mode_dispatch" class="form-label mb-1">Mode de dispatch</label>
                <select id="mode_dispatch" name="mode_dispatch" class="form-select">
                    <?php foreach ($modesDispatch as $valeurMode => $libelleMode): ?>
                        <option value="<?= vue_echapper($valeurMode) ?>"<?= $modeDispatchSelectionne === $valeurMode ? ' selected' : '' ?>>
                            <?= vue_echapper($libelleMode) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-outline-primary">
                <i class="fa-solid fa-flask-vial me-2"></i>
                Simuler
            </button>
        </form>
            <form method="post" action="<?= vue_echapper(BASE_URL . 'simulation-dispatch/valider') ?>">
                <input type="hidden" name="mode_dispatch" value="<?= vue_echapper($modeDispatchSelectionne) ?>">
                <button type="submit" class="btn btn-dispatch"<?= $peutValiderDispatch ? '' : ' disabled' ?>>
                    <i class="fa-solid fa-check me-2"></i>
                    Valider le dispatch
                </button>
            </form>
        </div>
        <form method="post" action="<?= vue_echapper(BASE_URL . 'simulation-dispatch/reinitialiser') ?>" class="simulation-form-reinit">
            <button type="submit" class="btn btn-outline-danger">
                <i class="fa-solid fa-rotate-left me-2"></i>
                Reinitialiser donnees
            </button>
        </form>
    </div>
</div>

<div class="mb-4">
    <span class="badge text-bg-light">
        <i class="fa-solid fa-sliders me-1"></i>
        Mode actif: <?= vue_echapper($libelleModeDispatch) ?>
    </span>
</div>

<?php if ($message_succes !== ''): ?>
    <div class="alert alert-success border-0 shadow-sm" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?= vue_echapper($message_succes) ?>
    </div>
<?php endif; ?>

<?php if ($message_erreur !== ''): ?>
    <div class="alert alert-danger border-0 shadow-sm" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i><?= vue_echapper($message_erreur) ?>
    </div>
<?php endif; ?>

<?php if ($simulationEffectuee === false): ?>
    <section class="section-card">
        <div class="section-card-header">
            <h2 class="section-title">Simulation non lancee</h2>
        </div>
        <div class="empty-state">
            <i class="fa-solid fa-flask-vial"></i>
            <p class="mb-2">Aucun resultat de simulation pour le moment.</p>
            <p class="mb-0">Cliquez sur <strong>Simuler</strong> pour generer la projection avant validation.</p>
        </div>
    </section>
<?php else: ?>
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
            <span class="badge text-bg-light">Tri: <?= vue_echapper($libelleModeDispatch) ?></span>
        </div>

        <?php if (count($besoins) === 0): ?>
            <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <p class="mb-0">Aucun besoin non dispatche a simuler.</p>
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
                        $unite = (string) ($besoin['unite'] ?? '');

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
                            <td><?= vue_echapper(vue_formater_date_humaine((string) ($besoin['date'] ?? ''))) ?></td>
                            <td>
                                <div class="fw-semibold"><?= vue_echapper($besoin['region'] ?? '') ?></div>
                                <small class="text-secondary"><?= vue_echapper($besoin['ville'] ?? '') ?></small>
                            </td>
                            <td><?= vue_echapper((string) ($besoin['produit'] ?? '')) ?></td>
                            <td class="text-end"><?= vue_formater_quantite((float) ($besoin['quantite_besoin'] ?? 0), $unite) ?></td>
                            <td class="text-end"><?= vue_formater_quantite((float) ($besoin['quantite_stock_avant'] ?? 0), $unite) ?></td>
                            <td class="text-end"><?= vue_formater_quantite($quantiteDistribuable, $unite) ?></td>
                            <td class="text-end"><?= vue_formater_quantite((float) ($besoin['quantite_restante'] ?? 0), $unite) ?></td>
                            <td class="text-center">
                                <span class="badge badge-etat <?= vue_echapper($classeBadge) ?>"><?= vue_echapper($texteBadge) ?></span>
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
                        <th class="text-end">Stock restant</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($donnees_simulation['stock_apres_simulation'] as $ligneStock): ?>
                        <tr>
                            <td class="fw-semibold"><?= vue_echapper($ligneStock['produit'] ?? '') ?></td>
                            <td class="text-end"><?= vue_formater_quantite((float) ($ligneStock['quantite'] ?? 0), (string) ($ligneStock['unite'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
