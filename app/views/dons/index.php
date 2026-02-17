<?php
/** @var array<string,mixed> $donnees */
/** @var string $message_succes */
/** @var string $message_erreur */

$produits = $donnees['produits'] ?? [];
$unites = $donnees['unites'] ?? [];
$donsRecents = $donnees['dons_recents'] ?? [];
$resume = $donnees['resume'] ?? [];
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
    <div>
        <h1 class="page-title h2 mb-2">Page d'insertion des dons</h1>
        <p class="page-subtitle mb-0">
            Enregistrer un don recu et mettre a jour automatiquement le stock BNGRC.
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
            <p class="stat-label"><i class="fa-solid fa-hand-holding-heart me-1"></i>Total dons</p>
            <p class="stat-value"><?= (int) ($resume['total_dons'] ?? 0) ?></p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-ok">
            <p class="stat-label"><i class="fa-solid fa-cubes me-1"></i>Quantite totale recue</p>
            <p class="stat-value"><?= vue_formater_nombre((float) ($resume['quantite_totale'] ?? 0)) ?></p>
        </article>
    </div>
</div>

<section class="section-card mb-4">
    <div class="section-card-header">
        <h2 class="section-title">Nouveau don</h2>
    </div>
    <div class="p-3 p-lg-4">
        <form method="post" action="<?= vue_echapper(BASE_URL . 'dons') ?>" class="row g-3">
            <div class="col-md-4">
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
            <div class="col-md-3">
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
            <div class="col-md-3">
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
            <div class="col-md-2">
                <label for="date_don" class="form-label">Date don</label>
                <input
                    id="date_don"
                    name="date_don"
                    type="datetime-local"
                    class="form-control"
                >
            </div>
            <div class="col-md-8">
                <label for="donateur" class="form-label">Donateur</label>
                <input
                    id="donateur"
                    name="donateur"
                    type="text"
                    class="form-control"
                    maxlength="150"
                    placeholder="Ex: Croix-Rouge, UNICEF, Association X"
                >
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-dispatch w-100">
                    <i class="fa-solid fa-plus me-2"></i>
                    Enregistrer le don
                </button>
            </div>
        </form>
    </div>
</section>

<section class="section-card">
    <div class="section-card-header d-flex justify-content-between align-items-center">
        <h2 class="section-title">Historique des dons recus</h2>
        <span class="badge text-bg-light">Tri: date DESC</span>
    </div>
    <?php if (count($donsRecents) === 0): ?>
        <div class="empty-state">
            <i class="fa-solid fa-inbox"></i>
            <p class="mb-0">Aucun don enregistre.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Produit</th>
                    <th class="text-end">Quantite</th>
                    <th>Donateur</th>
                    <th>Date don</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($donsRecents as $don): ?>
                    <tr>
                        <td><?= (int) ($don['idDon'] ?? 0) ?></td>
                        <td class="fw-semibold"><?= vue_echapper($don['produit'] ?? '') ?></td>
                        <td class="text-end"><?= vue_formater_quantite((float) ($don['quantite'] ?? 0), (string) ($don['unite'] ?? '')) ?></td>
                        <td><?= vue_echapper($don['donateur'] ?? 'Donateur anonyme') ?></td>
                        <td><?= vue_echapper(vue_formater_date_humaine($don['dateDon'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
