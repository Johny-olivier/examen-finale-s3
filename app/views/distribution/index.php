<?php
/** @var array<string,mixed> $donnees */
/** @var string $message_succes */
/** @var string $message_erreur */

$villes = $donnees['villes'] ?? [];
$produits = $donnees['produits'] ?? [];
$unites = $donnees['unites'] ?? [];
$stockDetaille = $donnees['stock_detaille'] ?? [];
$distributions = $donnees['distributions'] ?? [];
$resume = $donnees['resume'] ?? [];
$idVilleFiltre = (int) ($donnees['id_ville_filtre'] ?? 0);
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
    <div>
        <h1 class="page-title h2 mb-2">Distribution manuelle des dons</h1>
        <p class="page-subtitle mb-0">
            Cette page permet de distribuer manuellement des dons a une ville, meme hors besoin saisi.
        </p>
    </div>
    <a href="<?= vue_echapper(BASE_URL . 'simulation-dispatch') ?>" class="btn btn-outline-primary">
        <i class="fa-solid fa-robot me-2"></i>
        Aller a la simulation automatique
    </a>
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

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-total">
            <p class="stat-label"><i class="fa-solid fa-truck-ramp-box me-1"></i>Total distributions</p>
            <p class="stat-value"><?= (int) ($resume['total_distributions'] ?? 0) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-ok">
            <p class="stat-label"><i class="fa-solid fa-cubes me-1"></i>Quantite distribuee</p>
            <p class="stat-value"><?= vue_formater_nombre((float) ($resume['quantite_totale'] ?? 0)) ?></p>
        </article>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <section class="section-card h-100">
            <div class="section-card-header">
                <h2 class="section-title">Nouvelle distribution manuelle</h2>
            </div>
            <div class="p-3 p-lg-4">
                <form method="post" action="<?= vue_echapper(BASE_URL . 'distribution') ?>" class="row g-3">
                    <div class="col-md-6">
                        <label for="id_ville" class="form-label">Ville</label>
                        <select id="id_ville" name="id_ville" class="form-select" required>
                            <option value="">Selectionner une ville</option>
                            <?php foreach ($villes as $ville): ?>
                                <option value="<?= (int) ($ville['idVille'] ?? 0) ?>">
                                    <?= vue_echapper(($ville['region'] ?? '') . ' - ' . ($ville['ville'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="id_produit" class="form-label">Produit</label>
                        <select id="id_produit" name="id_produit" class="form-select" required>
                            <option value="">Selectionner un produit</option>
                            <?php foreach ($produits as $produit): ?>
                                <option value="<?= (int) ($produit['idProduit'] ?? 0) ?>">
                                    <?= vue_echapper(($produit['produit'] ?? '') . ' - ' . ($produit['categorie'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="id_unite" class="form-label">Unite</label>
                        <select id="id_unite" name="id_unite" class="form-select" required>
                            <option value="">Selectionner une unite</option>
                            <?php foreach ($unites as $unite): ?>
                                <option value="<?= (int) ($unite['idUnite'] ?? 0) ?>">
                                    <?= vue_echapper($unite['nom'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="quantite" class="form-label">Quantite</label>
                        <input
                            id="quantite"
                            name="quantite"
                            type="number"
                            class="form-control"
                            min="0.01"
                            step="0.01"
                            required
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="date_distribution" class="form-label">Date distribution</label>
                        <input
                            id="date_distribution"
                            name="date_distribution"
                            type="datetime-local"
                            class="form-control"
                        >
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-dispatch">
                            <i class="fa-solid fa-paper-plane me-2"></i>
                            Enregistrer la distribution
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="section-card h-100">
            <div class="section-card-header">
                <h2 class="section-title">Filtre historique</h2>
            </div>
            <div class="p-3 p-lg-4">
                <form method="get" action="<?= vue_echapper(BASE_URL . 'distribution') ?>" class="row g-3 mb-4">
                    <div class="col-12">
                        <label for="filtre_ville" class="form-label">Ville</label>
                        <select id="filtre_ville" name="id_ville" class="form-select">
                            <option value="0">Toutes les villes</option>
                            <?php foreach ($villes as $ville): ?>
                                <?php $idVille = (int) ($ville['idVille'] ?? 0); ?>
                                <option value="<?= $idVille ?>"<?= $idVilleFiltre === $idVille ? ' selected' : '' ?>>
                                    <?= vue_echapper(($ville['region'] ?? '') . ' - ' . ($ville['ville'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-filter me-2"></i>Filtrer
                        </button>
                        <a href="<?= vue_echapper(BASE_URL . 'distribution') ?>" class="btn btn-outline-dark">Reinitialiser</a>
                    </div>
                </form>

                <h3 class="section-title mb-2">Apercu stock actuel</h3>
                <?php if (count($stockDetaille) === 0): ?>
                    <p class="text-secondary mb-0">Aucun stock disponible.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead>
                            <tr>
                                <th>Produit</th>
                                <th class="text-end">Quantite</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($stockDetaille as $ligneStock): ?>
                                <tr>
                                    <td class="fw-semibold"><?= vue_echapper($ligneStock['produit'] ?? '') ?></td>
                                    <td class="text-end"><?= vue_formater_quantite((float) ($ligneStock['quantite'] ?? 0), (string) ($ligneStock['unite'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<section class="section-card">
    <div class="section-card-header d-flex justify-content-between align-items-center">
        <h2 class="section-title">Historique des distributions manuelles</h2>
        <span class="badge text-bg-light">Tri: date DESC</span>
    </div>
    <?php if (count($distributions) === 0): ?>
        <div class="empty-state">
            <i class="fa-solid fa-truck-ramp-box"></i>
            <p class="mb-0">Aucune distribution manuelle enregistree.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Region / Ville</th>
                    <th>Produit</th>
                    <th class="text-end">Quantite distribuee</th>
                    <th>Date distribution</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($distributions as $distribution): ?>
                    <tr>
                        <td><?= (int) ($distribution['idDistribution'] ?? 0) ?></td>
                        <td>
                            <div class="fw-semibold"><?= vue_echapper($distribution['region'] ?? '') ?></div>
                            <small class="text-secondary"><?= vue_echapper($distribution['ville'] ?? '') ?></small>
                        </td>
                        <td class="fw-semibold"><?= vue_echapper($distribution['produit'] ?? '') ?></td>
                        <td class="text-end"><?= vue_formater_quantite((float) ($distribution['quantite'] ?? 0), (string) ($distribution['unite'] ?? '')) ?></td>
                        <td><?= vue_echapper(vue_formater_date_humaine($distribution['dateDistribution'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
