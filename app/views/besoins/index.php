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

$villes = $donnees['villes'] ?? [];
$produits = $donnees['produits'] ?? [];
$unites = $donnees['unites'] ?? [];
$besoins = $donnees['besoins'] ?? [];
$resume = $donnees['resume'] ?? [];
$idVilleFiltre = (int) ($donnees['id_ville_filtre'] ?? 0);
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
    <div>
        <h1 class="page-title h2 mb-2">Insertion des besoins (par ville)</h1>
        <p class="page-subtitle mb-0">
            Saisir les besoins des sinistres et suivre leur etat de dispatch par ville.
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
            <p class="stat-label"><i class="fa-solid fa-list-check me-1"></i>Total besoins</p>
            <p class="stat-value"><?= (int) ($resume['total_besoins'] ?? 0) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-ok">
            <p class="stat-label"><i class="fa-solid fa-check-double me-1"></i>Dispatches</p>
            <p class="stat-value"><?= (int) ($resume['total_dispatche'] ?? 0) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-vide">
            <p class="stat-label"><i class="fa-solid fa-hourglass-half me-1"></i>Non dispatches</p>
            <p class="stat-value"><?= (int) ($resume['total_non_dispatche'] ?? 0) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-partiel">
            <p class="stat-label"><i class="fa-solid fa-cubes me-1"></i>Quantite totale</p>
            <p class="stat-value"><?= $formaterNombre((float) ($resume['quantite_totale'] ?? 0)) ?></p>
        </article>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <section class="section-card h-100">
            <div class="section-card-header">
                <h2 class="section-title">Nouveau besoin</h2>
            </div>
            <div class="p-3 p-lg-4">
                <form method="post" action="<?= $echapper(BASE_URL . 'besoins') ?>" class="row g-3">
                    <div class="col-md-6">
                        <label for="id_ville" class="form-label">Ville</label>
                        <select id="id_ville" name="id_ville" class="form-select" required>
                            <option value="">Selectionner une ville</option>
                            <?php foreach ($villes as $ville): ?>
                                <option value="<?= (int) ($ville['idVille'] ?? 0) ?>">
                                    <?= $echapper(($ville['region'] ?? '') . ' - ' . ($ville['ville'] ?? '')) ?>
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
                                    <?= $echapper(($produit['produit'] ?? '') . ' - ' . ($produit['categorie'] ?? '')) ?>
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
                        <label for="id_unite" class="form-label">Unite</label>
                        <select id="id_unite" name="id_unite" class="form-select" required>
                            <option value="">Selectionner une unite</option>
                            <?php foreach ($unites as $unite): ?>
                                <option value="<?= (int) ($unite['idUnite'] ?? 0) ?>">
                                    <?= $echapper($unite['nom'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="date_besoin" class="form-label">Date besoin</label>
                        <input
                            id="date_besoin"
                            name="date_besoin"
                            type="datetime-local"
                            class="form-control"
                        >
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-dispatch">
                            <i class="fa-solid fa-plus me-2"></i>
                            Enregistrer le besoin
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
                <form method="get" action="<?= $echapper(BASE_URL . 'besoins') ?>" class="row g-3">
                    <div class="col-12">
                        <label for="filtre_ville" class="form-label">Ville</label>
                        <select id="filtre_ville" name="id_ville" class="form-select">
                            <option value="0">Toutes les villes</option>
                            <?php foreach ($villes as $ville): ?>
                                <?php $idVille = (int) ($ville['idVille'] ?? 0); ?>
                                <option value="<?= $idVille ?>"<?= $idVilleFiltre === $idVille ? ' selected' : '' ?>>
                                    <?= $echapper(($ville['region'] ?? '') . ' - ' . ($ville['ville'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-filter me-2"></i>Filtrer
                        </button>
                        <a href="<?= $echapper(BASE_URL . 'besoins') ?>" class="btn btn-outline-dark">
                            Reinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<section class="section-card">
    <div class="section-card-header d-flex justify-content-between align-items-center">
        <h2 class="section-title">Liste des besoins saisis</h2>
        <span class="badge text-bg-light">Tri: date DESC</span>
    </div>
    <?php if (count($besoins) === 0): ?>
        <div class="empty-state">
            <i class="fa-solid fa-clipboard-list"></i>
            <p class="mb-0">Aucun besoin trouve.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Region / Ville</th>
                    <th>Produit</th>
                    <th class="text-end">Quantite</th>
                    <th class="text-end">Prix unitaire</th>
                    <th class="text-end">Montant estime</th>
                    <th>Date</th>
                    <th class="text-center">Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($besoins as $besoin): ?>
                    <?php
                    $status = (string) ($besoin['status'] ?? '');
                    $classeBadge = $status === 'dispatche' ? 'text-bg-success' : 'text-bg-warning';
                    $libelleStatus = $status === 'dispatche' ? 'Dispatche' : 'Non dispatche';
                    $quantite = (float) ($besoin['quantite'] ?? 0);
                    $prixUnitaire = (float) ($besoin['prixUnitaire'] ?? 0);
                    ?>
                    <tr>
                        <td><?= (int) ($besoin['idBesoin'] ?? 0) ?></td>
                        <td>
                            <div class="fw-semibold"><?= $echapper($besoin['region'] ?? '') ?></div>
                            <small class="text-secondary"><?= $echapper($besoin['ville'] ?? '') ?></small>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= $echapper($besoin['produit'] ?? '') ?></div>
                            <small class="text-secondary"><?= $echapper($besoin['unite'] ?? '') ?></small>
                        </td>
                        <td class="text-end"><?= $formaterNombre($quantite) ?></td>
                        <td class="text-end"><?= $formaterNombre($prixUnitaire) ?></td>
                        <td class="text-end"><?= $formaterNombre($quantite * $prixUnitaire) ?></td>
                        <td><?= $echapper($formaterDate($besoin['date'] ?? '')) ?></td>
                        <td class="text-center">
                            <span class="badge badge-etat <?= $echapper($classeBadge) ?>"><?= $echapper($libelleStatus) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
