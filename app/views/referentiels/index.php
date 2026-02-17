<?php
/** @var array<string,mixed> $donnees */
/** @var string $message_succes */
/** @var string $message_erreur */

$regions = $donnees['regions'] ?? [];
$villes = $donnees['villes'] ?? [];
$categories = $donnees['categories'] ?? [];
$produits = $donnees['produits'] ?? [];
$unites = $donnees['unites'] ?? [];
$typesParametreAchat = $donnees['types_parametre_achat'] ?? [];
$parametresAchat = $donnees['parametres_achat'] ?? [];
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
    <div>
        <h1 class="page-title h2 mb-2">CRUD des referentiels</h1>
        <p class="page-subtitle mb-0">
            Gestion de base des regions, villes, categories, produits, unites et parametres d'achat.
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

<section class="section-card mb-4" id="regions">
    <div class="section-card-header">
        <h2 class="section-title">Regions</h2>
    </div>
    <div class="p-3 p-lg-4">
        <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/regions/ajouter') ?>" class="row g-2 mb-3">
            <div class="col-md-8">
                <input type="text" name="nom" class="form-control" placeholder="Nom region" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-dispatch w-100">Ajouter region</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($regions as $region): ?>
                    <tr>
                        <td><?= (int) ($region['idRegion'] ?? 0) ?></td>
                        <td>
                            <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/regions/modifier') ?>" class="row g-2">
                                <input type="hidden" name="id_region" value="<?= (int) ($region['idRegion'] ?? 0) ?>">
                                <div class="col-md-8">
                                    <input type="text" name="nom" class="form-control form-control-sm" value="<?= vue_echapper($region['nom'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Modifier</button>
                                </div>
                            </form>
                        </td>
                        <td class="text-end">
                            <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/regions/supprimer') ?>">
                                <input type="hidden" name="id_region" value="<?= (int) ($region['idRegion'] ?? 0) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-dark">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section-card mb-4" id="villes">
    <div class="section-card-header">
        <h2 class="section-title">Villes</h2>
    </div>
    <div class="p-3 p-lg-4">
        <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/villes/ajouter') ?>" class="row g-2 mb-3">
            <div class="col-md-4">
                <select name="id_region" class="form-select" required>
                    <option value="">Region</option>
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= (int) ($region['idRegion'] ?? 0) ?>"><?= vue_echapper($region['nom'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <input type="text" name="nom" class="form-control" placeholder="Nom ville" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dispatch w-100">Ajouter ville</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Region</th>
                    <th>Ville</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($villes as $ville): ?>
                    <tr>
                        <td><?= (int) ($ville['idVille'] ?? 0) ?></td>
                        <td><?= vue_echapper($ville['region'] ?? '') ?></td>
                        <td>
                            <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/villes/modifier') ?>" class="row g-2">
                                <input type="hidden" name="id_ville" value="<?= (int) ($ville['idVille'] ?? 0) ?>">
                                <div class="col-md-4">
                                    <select data-no-select-personnalise="true" name="id_region" class="form-select form-select-sm" required>
                                        <?php foreach ($regions as $region): ?>
                                            <?php $idRegion = (int) ($region['idRegion'] ?? 0); ?>
                                            <option value="<?= $idRegion ?>"<?= $idRegion === (int) ($ville['idRegion'] ?? 0) ? ' selected' : '' ?>>
                                                <?= vue_echapper($region['nom'] ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" name="nom" class="form-control form-control-sm" value="<?= vue_echapper($ville['ville'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Modifier</button>
                                </div>
                            </form>
                        </td>
                        <td class="text-end">
                            <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/villes/supprimer') ?>">
                                <input type="hidden" name="id_ville" value="<?= (int) ($ville['idVille'] ?? 0) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-dark">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section-card mb-4" id="categories">
    <div class="section-card-header">
        <h2 class="section-title">Categories</h2>
    </div>
    <div class="p-3 p-lg-4">
        <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/categories/ajouter') ?>" class="row g-2 mb-3">
            <div class="col-md-8">
                <input type="text" name="nom" class="form-control" placeholder="Nom categorie" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-dispatch w-100">Ajouter categorie</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $categorie): ?>
                    <tr>
                        <td><?= (int) ($categorie['idCategorie'] ?? 0) ?></td>
                        <td>
                            <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/categories/modifier') ?>" class="row g-2">
                                <input type="hidden" name="id_categorie" value="<?= (int) ($categorie['idCategorie'] ?? 0) ?>">
                                <div class="col-md-8">
                                    <input type="text" name="nom" class="form-control form-control-sm" value="<?= vue_echapper($categorie['nom'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Modifier</button>
                                </div>
                            </form>
                        </td>
                        <td class="text-end">
                            <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/categories/supprimer') ?>">
                                <input type="hidden" name="id_categorie" value="<?= (int) ($categorie['idCategorie'] ?? 0) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-dark">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section-card mb-4" id="produits">
    <div class="section-card-header">
        <h2 class="section-title">Produits</h2>
    </div>
    <div class="p-3 p-lg-4">
        <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/produits/ajouter') ?>" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="nom" class="form-control" placeholder="Nom produit" required>
            </div>
            <div class="col-md-4">
                <select name="id_categorie" class="form-select" required>
                    <option value="">Categorie</option>
                    <?php foreach ($categories as $categorie): ?>
                        <option value="<?= (int) ($categorie['idCategorie'] ?? 0) ?>">
                            <?= vue_echapper($categorie['nom'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="prix_unitaire" class="form-control" min="0" step="0.01" placeholder="Prix" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dispatch w-100">Ajouter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Produit</th>
                    <th>Categorie</th>
                    <th class="text-end">Prix unitaire</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($produits as $produit): ?>
                    <tr>
                        <td><?= (int) ($produit['idProduit'] ?? 0) ?></td>
                        <td>
                            <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/produits/modifier') ?>" class="row g-2">
                                <input type="hidden" name="id_produit" value="<?= (int) ($produit['idProduit'] ?? 0) ?>">
                                <div class="col-md-4">
                                    <input type="text" name="nom" class="form-control form-control-sm" value="<?= vue_echapper($produit['produit'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <select data-no-select-personnalise="true" name="id_categorie" class="form-select form-select-sm" required>
                                        <?php foreach ($categories as $categorie): ?>
                                            <?php $idCategorie = (int) ($categorie['idCategorie'] ?? 0); ?>
                                            <option value="<?= $idCategorie ?>"<?= $idCategorie === (int) ($produit['idCategorie'] ?? 0) ? ' selected' : '' ?>>
                                                <?= vue_echapper($categorie['nom'] ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="prix_unitaire" class="form-control form-control-sm" min="0" step="0.01" value="<?= vue_echapper((string) ($produit['prixUnitaire'] ?? '0')) ?>" required>
                                </div>
                                <div class="col-md-2 text-md-end">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Modifier</button>
                                </div>
                            </form>
                        </td>
                        <td><?= vue_echapper($produit['categorie'] ?? '') ?></td>
                        <td class="text-end"><?= vue_formater_prix_unitaire_ar((float) ($produit['prixUnitaire'] ?? 0)) ?></td>
                        <td class="text-end">
                            <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/produits/supprimer') ?>">
                                <input type="hidden" name="id_produit" value="<?= (int) ($produit['idProduit'] ?? 0) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-dark">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section-card" id="unites">
    <div class="section-card-header">
        <h2 class="section-title">Unites</h2>
    </div>
    <div class="p-3 p-lg-4">
        <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/unites/ajouter') ?>" class="row g-2 mb-3">
            <div class="col-md-8">
                <input type="text" name="nom" class="form-control" placeholder="Nom unite" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-dispatch w-100">Ajouter unite</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($unites as $unite): ?>
                    <tr>
                        <td><?= (int) ($unite['idUnite'] ?? 0) ?></td>
                        <td>
                            <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/unites/modifier') ?>" class="row g-2">
                                <input type="hidden" name="id_unite" value="<?= (int) ($unite['idUnite'] ?? 0) ?>">
                                <div class="col-md-8">
                                    <input type="text" name="nom" class="form-control form-control-sm" value="<?= vue_echapper($unite['nom'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Modifier</button>
                                </div>
                            </form>
                        </td>
                        <td class="text-end">
                            <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/unites/supprimer') ?>">
                                <input type="hidden" name="id_unite" value="<?= (int) ($unite['idUnite'] ?? 0) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-dark">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section-card mt-4" id="types-parametre-achat">
    <div class="section-card-header">
        <h2 class="section-title">Types de parametre achat</h2>
    </div>
    <div class="p-3 p-lg-4">
        <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/types-parametre-achat/ajouter') ?>" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="code" class="form-control" placeholder="Code (ex: frais_achat_pourcentage)" required>
            </div>
            <div class="col-md-5">
                <input type="text" name="libelle" class="form-control" placeholder="Libelle type parametre" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dispatch w-100">Ajouter type</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Edition (code / libelle)</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($typesParametreAchat as $typeParametre): ?>
                    <tr>
                        <td><?= (int) ($typeParametre['idTypeParametreAchat'] ?? 0) ?></td>
                        <td>
                            <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/types-parametre-achat/modifier') ?>" class="row g-2">
                                <input type="hidden" name="id_type_parametre_achat" value="<?= (int) ($typeParametre['idTypeParametreAchat'] ?? 0) ?>">
                                <div class="col-md-5">
                                    <input type="text" name="code" class="form-control form-control-sm" value="<?= vue_echapper($typeParametre['code'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" name="libelle" class="form-control form-control-sm" value="<?= vue_echapper($typeParametre['libelle'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-2 text-md-end">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Modifier</button>
                                </div>
                            </form>
                        </td>
                        <td class="text-end">
                            <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/types-parametre-achat/supprimer') ?>">
                                <input type="hidden" name="id_type_parametre_achat" value="<?= (int) ($typeParametre['idTypeParametreAchat'] ?? 0) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-dark">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section-card mt-4" id="parametres-achat">
    <div class="section-card-header">
        <h2 class="section-title">Parametres achat</h2>
    </div>
    <div class="p-3 p-lg-4">
        <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/parametres-achat/ajouter') ?>" class="row g-2 mb-3">
            <div class="col-md-4">
                <select name="id_type_parametre_achat" class="form-select" required>
                    <option value="">Type parametre</option>
                    <?php foreach ($typesParametreAchat as $typeParametre): ?>
                        <option value="<?= (int) ($typeParametre['idTypeParametreAchat'] ?? 0) ?>">
                            <?= vue_echapper(($typeParametre['code'] ?? '') . ' - ' . ($typeParametre['libelle'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.0001" min="0" name="valeur_decimal" class="form-control" placeholder="Valeur" required>
            </div>
            <div class="col-md-3">
                <input type="datetime-local" name="date_application" class="form-control" required>
            </div>
            <div class="col-md-1">
                <select name="actif" class="form-select" required>
                    <option value="1">Actif</option>
                    <option value="0">Inactif</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dispatch w-100">Ajouter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th class="text-end">Valeur</th>
                    <th>Date application</th>
                    <th>Etat</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($parametresAchat as $parametre): ?>
                    <?php
                    $dateApplication = (string) ($parametre['dateApplication'] ?? '');
                    $horodatage = strtotime($dateApplication);
                    $datePourInput = $horodatage === false ? '' : date('Y-m-d\TH:i', $horodatage);
                    ?>
                    <tr>
                        <td><?= (int) ($parametre['idParametreAchat'] ?? 0) ?></td>
                        <td>
                            <div class="fw-semibold"><?= vue_echapper($parametre['codeType'] ?? '') ?></div>
                            <small class="text-secondary"><?= vue_echapper($parametre['libelleType'] ?? '') ?></small>
                        </td>
                        <td class="text-end"><?= vue_formater_nombre((float) ($parametre['valeurDecimal'] ?? 0)) ?></td>
                        <td><?= vue_echapper(vue_formater_date_humaine($parametre['dateApplication'] ?? '')) ?></td>
                        <td>
                            <?php if (((int) ($parametre['actif'] ?? 0)) === 1): ?>
                                <span class="badge text-bg-success">Actif</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/parametres-achat/modifier') ?>" class="row g-2 mb-2">
                                <input type="hidden" name="id_parametre_achat" value="<?= (int) ($parametre['idParametreAchat'] ?? 0) ?>">
                                <div class="col-md-12">
                                    <select data-no-select-personnalise="true" name="id_type_parametre_achat" class="form-select form-select-sm" required>
                                        <?php foreach ($typesParametreAchat as $typeParametre): ?>
                                            <?php $idType = (int) ($typeParametre['idTypeParametreAchat'] ?? 0); ?>
                                            <option value="<?= $idType ?>"<?= $idType === (int) ($parametre['idTypeParametreAchat'] ?? 0) ? ' selected' : '' ?>>
                                                <?= vue_echapper(($typeParametre['code'] ?? '') . ' - ' . ($typeParametre['libelle'] ?? '')) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" step="0.0001" min="0" name="valeur_decimal" class="form-control form-control-sm" value="<?= vue_echapper((string) ($parametre['valeurDecimal'] ?? '0')) ?>" required>
                                </div>
                                <div class="col-md-5">
                                    <input type="datetime-local" name="date_application" class="form-control form-control-sm" value="<?= vue_echapper($datePourInput) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <select data-no-select-personnalise="true" name="actif" class="form-select form-select-sm" required>
                                        <option value="1"<?= ((int) ($parametre['actif'] ?? 0)) === 1 ? ' selected' : '' ?>>Actif</option>
                                        <option value="0"<?= ((int) ($parametre['actif'] ?? 0)) === 0 ? ' selected' : '' ?>>Inactif</option>
                                    </select>
                                </div>
                                <div class="col-md-12 text-md-end">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Modifier</button>
                                </div>
                            </form>

                            <form method="post" action="<?= vue_echapper(BASE_URL . 'referentiels/parametres-achat/supprimer') ?>">
                                <input type="hidden" name="id_parametre_achat" value="<?= (int) ($parametre['idParametreAchat'] ?? 0) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-dark">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
