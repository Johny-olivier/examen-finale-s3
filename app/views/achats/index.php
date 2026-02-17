<?php
/** @var array<string,mixed> $donnees */
/** @var string $message_succes */
/** @var string $message_erreur */

$villes = $donnees['villes'] ?? [];
$besoinsRestants = $donnees['besoins_restants'] ?? [];
$achats = $donnees['achats'] ?? [];
$resume = $donnees['resume'] ?? [];
$idVilleFiltre = (int) ($donnees['id_ville_filtre'] ?? 0);
$tauxFrais = (float) ($donnees['taux_frais'] ?? 0);
$soldeArgentDisponible = (float) ($donnees['solde_argent_disponible'] ?? 0);
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
    <div>
        <h1 class="page-title h2 mb-2">Achats via dons en argent</h1>
        <p class="page-subtitle mb-0">
            Acheter les besoins restants en nature ou materiaux a partir des dons en argent.
        </p>
    </div>
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
            <p class="stat-label"><i class="fa-solid fa-cart-shopping me-1"></i>Total achats</p>
            <p class="stat-value"><?= (int) ($resume['total_achats'] ?? 0) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-ok">
            <p class="stat-label"><i class="fa-solid fa-sack-dollar me-1"></i>Argent distribue disponible</p>
            <p class="stat-value"><?= vue_formater_montant_ar($soldeArgentDisponible) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-partiel">
            <p class="stat-label"><i class="fa-solid fa-percent me-1"></i>Frais achat</p>
            <p class="stat-value"><?= vue_formater_nombre($tauxFrais) ?> %</p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-vide">
            <p class="stat-label"><i class="fa-solid fa-receipt me-1"></i>Montant total achats</p>
            <p class="stat-value"><?= vue_formater_montant_ar((float) ($resume['montant_total'] ?? 0)) ?></p>
        </article>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <section class="section-card h-100">
            <div class="section-card-header">
                <h2 class="section-title">Nouveau achat depuis un besoin restant</h2>
            </div>
            <div class="p-3 p-lg-4">
                <form method="post" action="<?= vue_echapper(BASE_URL . 'achats') ?>" class="row g-3">
                    <input type="hidden" name="id_ville_filtre" value="<?= $idVilleFiltre ?>">

                    <div class="col-12">
                        <label for="id_besoin" class="form-label">Besoin restant</label>
                        <select id="id_besoin" name="id_besoin" class="form-select" required>
                            <option value="">Selectionner un besoin</option>
                            <?php foreach ($besoinsRestants as $besoin): ?>
                                <?php
                                $qteRestante = (float) ($besoin['quantiteRestanteAAcheter'] ?? 0);
                                if ($qteRestante <= 0) {
                                    continue;
                                }

                                $libelleBesoin = sprintf(
                                    '#%d | %s - %s | %s (%s) | restant: %s',
                                    (int) ($besoin['idBesoin'] ?? 0),
                                    (string) ($besoin['region'] ?? ''),
                                    (string) ($besoin['ville'] ?? ''),
                                    (string) ($besoin['produit'] ?? ''),
                                    (string) ($besoin['unite'] ?? ''),
                                    vue_formater_quantite($qteRestante, (string) ($besoin['unite'] ?? ''))
                                );
                                ?>
                                <option value="<?= (int) ($besoin['idBesoin'] ?? 0) ?>">
                                    <?= vue_echapper($libelleBesoin) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="quantite" class="form-label">Quantite a acheter</label>
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
                        <label for="date_achat" class="form-label">Date achat</label>
                        <input
                            id="date_achat"
                            name="date_achat"
                            type="datetime-local"
                            class="form-control"
                        >
                    </div>

                    <div class="col-md-4">
                        <label for="taux_frais_affiche" class="form-label">Taux frais applique</label>
                        <input
                            id="taux_frais_affiche"
                            type="text"
                            class="form-control"
                            value="<?= vue_echapper(vue_formater_nombre($tauxFrais)) ?> %"
                            readonly
                        >
                    </div>

                    <div class="col-12">
                        <label for="commentaire" class="form-label">Commentaire</label>
                        <textarea
                            id="commentaire"
                            name="commentaire"
                            class="form-control"
                            rows="2"
                            maxlength="255"
                            placeholder="Information complementaire sur l'achat"
                        ></textarea>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-dispatch">
                            <i class="fa-solid fa-plus me-2"></i>
                            Enregistrer l'achat
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="section-card h-100">
            <div class="section-card-header">
                <h2 class="section-title">Filtre par ville</h2>
            </div>
            <div class="p-3 p-lg-4">
                <form method="get" action="<?= vue_echapper(BASE_URL . 'achats') ?>" class="row g-3">
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
                        <a href="<?= vue_echapper(BASE_URL . 'achats') ?>" class="btn btn-outline-dark">
                            Reinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<section class="section-card mb-4">
    <div class="section-card-header d-flex justify-content-between align-items-center gap-2">
        <h2 class="section-title">Besoins restants pour achat</h2>
        <span class="badge text-bg-light">Source: besoins non dispatche</span>
    </div>
    <div class="px-3 pt-3">
        <p class="text-secondary small mb-0">
            Regle metier: achat bloque si le produit existe encore dans les dons restants (stock &gt; 0).
        </p>
        <p class="text-secondary small mb-0">
            Regle metier: l'achat est finance par l'argent deja distribue a la ville.
        </p>
    </div>

    <?php if (count($besoinsRestants) === 0): ?>
        <div class="empty-state">
            <i class="fa-solid fa-clipboard-list"></i>
            <p class="mb-0">Aucun besoin restant.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th># Besoin</th>
                    <th>Date besoin</th>
                    <th>Region / Ville</th>
                    <th>Produit</th>
                    <th class="text-end">Qte besoin</th>
                    <th class="text-end">Stock dons restants</th>
                    <th class="text-end">Deja achete</th>
                    <th class="text-end">Reste a acheter</th>
                    <th class="text-end">PU</th>
                    <th class="text-end">Montant estime</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($besoinsRestants as $ligne): ?>
                    <?php
                    $unite = (string) ($ligne['unite'] ?? '');
                    $qteRestante = (float) ($ligne['quantiteRestanteAAcheter'] ?? 0);
                    $prixUnitaire = (float) ($ligne['prixUnitaire'] ?? 0);
                    $montantEstime = round($qteRestante * $prixUnitaire * (1 + ($tauxFrais / 100)), 2);
                    ?>
                    <tr>
                        <td><?= (int) ($ligne['idBesoin'] ?? 0) ?></td>
                        <td><?= vue_echapper(vue_formater_date_humaine($ligne['date'] ?? '')) ?></td>
                        <td>
                            <div class="fw-semibold"><?= vue_echapper($ligne['region'] ?? '') ?></div>
                            <small class="text-secondary"><?= vue_echapper($ligne['ville'] ?? '') ?></small>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= vue_echapper($ligne['produit'] ?? '') ?></div>
                            <small class="text-secondary"><?= vue_echapper($unite) ?></small>
                        </td>
                        <td class="text-end"><?= vue_formater_quantite((float) ($ligne['quantiteBesoin'] ?? 0), $unite) ?></td>
                        <td class="text-end"><?= vue_formater_quantite((float) ($ligne['quantiteStockRestante'] ?? 0), $unite) ?></td>
                        <td class="text-end"><?= vue_formater_quantite((float) ($ligne['quantiteDejaAchetee'] ?? 0), $unite) ?></td>
                        <td class="text-end fw-semibold"><?= vue_formater_quantite($qteRestante, $unite) ?></td>
                        <td class="text-end"><?= vue_formater_prix_unitaire_ar($prixUnitaire) ?></td>
                        <td class="text-end"><?= vue_formater_montant_ar($montantEstime) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="section-card">
    <div class="section-card-header d-flex justify-content-between align-items-center gap-2">
        <h2 class="section-title">Liste des achats</h2>
        <span class="badge text-bg-light">Tri: date DESC</span>
    </div>

    <?php if (count($achats) === 0): ?>
        <div class="empty-state">
            <i class="fa-solid fa-cart-shopping"></i>
            <p class="mb-0">Aucun achat enregistre.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th># Achat</th>
                    <th># Besoin</th>
                    <th>Date achat</th>
                    <th>Region / Ville</th>
                    <th>Produit</th>
                    <th class="text-end">Quantite</th>
                    <th class="text-end">PU</th>
                    <th class="text-end">Sous-total</th>
                    <th class="text-end">Frais</th>
                    <th class="text-end">Total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($achats as $achat): ?>
                    <tr>
                        <td><?= (int) ($achat['idAchat'] ?? 0) ?></td>
                        <td><?= (int) ($achat['idBesoin'] ?? 0) ?></td>
                        <td><?= vue_echapper(vue_formater_date_humaine($achat['dateAchat'] ?? '')) ?></td>
                        <td>
                            <div class="fw-semibold"><?= vue_echapper($achat['region'] ?? '') ?></div>
                            <small class="text-secondary"><?= vue_echapper($achat['ville'] ?? '') ?></small>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= vue_echapper($achat['produit'] ?? '') ?></div>
                            <small class="text-secondary"><?= vue_echapper($achat['unite'] ?? '') ?></small>
                        </td>
                        <td class="text-end"><?= vue_formater_quantite((float) ($achat['quantite'] ?? 0), (string) ($achat['unite'] ?? '')) ?></td>
                        <td class="text-end"><?= vue_formater_prix_unitaire_ar((float) ($achat['prixUnitaire'] ?? 0)) ?></td>
                        <td class="text-end"><?= vue_formater_montant_ar((float) ($achat['montantSousTotal'] ?? 0)) ?></td>
                        <td class="text-end"><?= vue_formater_montant_ar((float) ($achat['montantFrais'] ?? 0)) ?></td>
                        <td class="text-end fw-semibold"><?= vue_formater_montant_ar((float) ($achat['montantTotal'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
