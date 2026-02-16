<?php
$echapper = static function ($valeur): string {
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
};

$titrePage = $title ?? 'Application BNGRC';
$menuActif = $menu_actif ?? '';
$nonceCsp = (string) \Flight::get('csp_nonce');

$menus = [
    [
        'cle' => 'stock_initial',
        'titre' => 'Ajouter Stock BNGRC',
        'icone' => 'fa-solid fa-box-open',
        'href' => BASE_URL . 'stock/initialisation',
    ],
    [
        'cle' => 'dons',
        'titre' => 'Insertion des dons',
        'icone' => 'fa-solid fa-hand-holding-heart',
        'href' => BASE_URL . 'dons',
    ],
    [
        'cle' => 'besoins',
        'titre' => 'Insertion des besoins',
        'icone' => 'fa-solid fa-clipboard-list',
        'href' => BASE_URL . 'besoins',
    ],
    [
        'cle' => 'distribution',
        'titre' => 'Distribution des dons',
        'icone' => 'fa-solid fa-truck-ramp-box',
        'href' => BASE_URL . 'distribution',
    ],
    [
        'cle' => 'stock',
        'titre' => 'Consultation du stock',
        'icone' => 'fa-solid fa-warehouse',
        'href' => BASE_URL . 'stock/consultation',
    ],
    [
        'cle' => 'dashboard',
        'titre' => 'Dashboard',
        'icone' => 'fa-solid fa-chart-line',
        'href' => BASE_URL . 'dashboard',
    ],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $echapper($titrePage) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="<?= $echapper(BASE_URL . 'assets/css/app.css') ?>" rel="stylesheet">
</head>
<body class="app-page">
    <div class="app-shell">
        <aside class="app-sidebar shadow-lg">
            <div class="sidebar-brand">
                <span class="brand-badge">BNGRC</span>
                <h1>Suivi des collectes</h1>
            </div>

            <nav class="nav flex-column sidebar-nav">
                <?php foreach ($menus as $menu): ?>
                    <?php $estActif = $menuActif === $menu['cle']; ?>
                    <a class="nav-link sidebar-link<?= $estActif ? ' active' : '' ?>" href="<?= $echapper($menu['href']) ?>">
                        <i class="<?= $echapper($menu['icone']) ?>"></i>
                        <span><?= $echapper($menu['titre']) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <main class="app-main">
            <header class="app-main-header">
                <h2 class="mb-0"><?= $echapper($titrePage) ?></h2>
                <div class="header-actions">
                    <button
                        type="button"
                        id="bouton-theme"
                        class="btn-theme-toggle"
                        aria-pressed="false"
                        aria-label="Changer de theme"
                    >
                        <i class="fa-solid fa-moon"></i>
                        <span>Mode sombre</span>
                    </button>
                    <span class="header-pill">
                        <i class="fa-solid fa-bolt me-1"></i>
                        BNGRC
                    </span>
                </div>
            </header>

            <section class="app-content card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <?= $content ?? '' ?>
                </div>
            </section>
        </main>
    </div>

    <script
        nonce="<?= $echapper($nonceCsp) ?>"
        src="<?= $echapper(BASE_URL . 'assets/js/theme.js') ?>"
    ></script>
    <script
        nonce="<?= $echapper($nonceCsp) ?>"
        src="<?= $echapper(BASE_URL . 'assets/js/select-personnalise.js') ?>"
    ></script>
</body>
</html>
