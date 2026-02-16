# Examen Finale S3 - BNGRC

Application web PHP (FlightPHP) pour la gestion des dons, besoins, stock BNGRC et distribution.
Le projet est orienté contexte Madagascar (Toamasina / Majunga) avec priorisation de distribution par date de besoin.

Ce README est volontairement detaille pour permettre a un nouveau developpeur (ou une autre IA) de reprendre et modifier le projet sans historique de chat.

## 1) Objectif metier

L'application permet de:
- enregistrer des dons recus
- enregistrer des besoins par ville
- initialiser/consulter le stock BNGRC
- simuler puis valider une distribution des dons
- suivre un dashboard global (besoins, dons, distributions, stock)

## 2) Fonctionnalites implementees

- Ajouter Stock BNGRC
- Consulter le stock BNGRC
- Inserer des dons
- Inserer des besoins (par ville)
- Simuler et valider la distribution des dons (workflow en 2 boutons: Simuler puis Valider, priorite date ASC puis idBesoin ASC)
- Dashboard avec:
  - resume besoins par ville
  - resume dons recus
  - resume dons distribues par ville
  - etat global du stock
  - details par ville via lightbox (besoins + dons distribues)
- Interface moderne avec sidebar + mode clair/sombre

## 3) Stack technique

- PHP 8.x
- FlightPHP 3.x
- PDO (via `Flight::db()` enregistre dans `app/config/services.php`)
- MySQL/MariaDB
- Front: Bootstrap 5, Font Awesome, CSS custom, JS vanilla
- Debug: Tracy

Dependances principales: `composer.json`.

## 4) Architecture

Architecture MVC decoupee par fonctionnalite:

- `app/repositories/*Repository.php`
  - acces SQL uniquement
  - aucune logique d'affichage
- `app/services/*Service.php`
  - logique metier
  - orchestration transactionnelle
- `app/controllers/*Controller.php`
  - reception HTTP
  - appels services
  - rendu des vues
- `app/views/*`
  - templates PHP
  - UI

Convention actuelle:
- noms de fonctions/variables en francais
- dependance DB injectee dans repository via `new XRepository(Flight::db())`
- routes centralisees dans `app/config/routes.php`

## 5) Structure des dossiers

```txt
app/
  config/
    bootstrap.php
    config.php
    constant.php
    routes.php
    services.php
  controllers/
    BesoinController.php
    DashboardController.php
    DistributionController.php
    DonController.php
    StockController.php
  repositories/
    BesoinRepository.php
    DashboardRepository.php
    DistributionRepository.php
    DonRepository.php
    StockRepository.php
  services/
    BesoinService.php
    DashboardService.php
    DistributionService.php
    DonService.php
    StockService.php
  views/
    layout.php
    besoins/index.php
    dashboard/index.php
    distribution/index.php
    dons/index.php
    stock/initialisation.php
    stock/consultation.php
    errors/404.php
    errors/500.php
assets/
  css/app.css
  js/dashboard-details.js
  js/theme.js
sql/
  16022026-01-schema.sql
  16022026-02-init-data.sql
  16022026-03-test-data-distribution.sql
  16022026-04-select.sql
index.php
.htaccess
git.sh
todo
```

## 6) Base de donnees

### Scripts SQL

Executer dans cet ordre:

1. `sql/16022026-01-schema.sql`
2. `sql/16022026-02-init-data.sql`
3. (optionnel test) `sql/16022026-03-test-data-distribution.sql`
4. (verification) `sql/16022026-04-select.sql`

### Tables principales

- `regions`, `ville`
- `categories`, `produit`, `unite`
- `besoins`
- `dons`
- `StockBNGRC`
- `MvtStock`

### Regles metier importantes

- **Ordre de distribution**:
  - `besoins.status = 'non_dispatche'`
  - tri `date ASC`, puis `idBesoin ASC`
- **Simulation (bouton Simuler)**:
  - calcule le resultat theorique de dispatch
  - ne modifie pas la base de donnees
- **Distribution complete**:
  - decrement stock
  - insertion `MvtStock(typeMvt='distribution')`
  - `besoins.status = 'dispatche'`
- **Distribution partielle**:
  - decrement stock
  - insertion `MvtStock(typeMvt='distribution')`
  - `besoins.quantite` remplacee par la quantite restante
  - `status` reste `non_dispatche`

## 7) Routes HTTP

Definies dans `app/config/routes.php`.

- `GET /` -> redirection vers `/distribution`
- `GET /distribution`
- `POST /distribution/simuler`
- `POST /distribution/valider`
- `GET /stock/initialisation`
- `POST /stock/initialisation`
- `GET /stock/consultation`
- `GET /dashboard`
- `GET /besoins`
- `POST /besoins`
- `GET /dons`
- `POST /dons`

Gestion erreurs:
- `notFound` -> `app/views/errors/404.php`
- `error` -> `app/views/errors/500.php`

## 8) Demarrage local

### Prerequis

- PHP 8+
- MySQL/MariaDB
- Composer
- Apache avec `mod_rewrite` recommande

### Installation

```bash
composer install
```

Configurer la DB dans `app/config/config.php`:
- `host`
- `dbname`
- `user`
- `password`

### Import SQL (exemple)

```bash
mysql -u root -p < sql/16022026-01-schema.sql
mysql -u root -p < sql/16022026-02-init-data.sql
```

### URL d'acces

Le projet est configure pour une entree racine via `index.php` + `.htaccess`.
Exemple:

`http://localhost/l2/examens/fev/examen-finale-s3/`

## 9) Configuration URL (important en FTP)

Fichier: `app/config/constant.php`

- `BASE_URL` est calculee automatiquement depuis `$_SERVER['SCRIPT_NAME']`
- override possible via variable d'environnement:
  - `APP_BASE_URL`

Exemple:
`APP_BASE_URL=http://mon-serveur/chemin/projet/`

Utiliser cet override si l'hebergement produit des URLs incorrectes.

## 10) UI / Frontend

- Layout global: `app/views/layout.php`
- Styles globaux: `assets/css/app.css`
- Theme:
  - switch clair/sombre
  - persistance via `localStorage`
  - script: `assets/js/theme.js`
- Dashboard details ville:
  - bouton "Voir details" par ligne
  - lightbox sur la zone contenu (hors sidebar)
  - script: `assets/js/dashboard-details.js`
- Distribution:
  - bouton `Simuler` pour afficher la projection
  - bouton `Valider le dispatch` pour appliquer les mouvements reels

## 11) Securite HTTP

Middleware: `app/middlewares/SecurityHeadersMiddleware.php`

- CSP avec nonce dynamique (`csp_nonce`) pour scripts inline necessaires
- X-Frame-Options, X-Content-Type-Options, Referrer-Policy, etc.

Si vous ajoutez un script inline dans une vue, utiliser le nonce:
- `nonce="<?= htmlspecialchars((string) Flight::get('csp_nonce'), ENT_QUOTES, 'UTF-8') ?>"`

## 12) Git workflow du projet

Regle appliquee:
- pas de commit direct sur `main`
- pas de commit direct sur `DEV`
- travail sur `feature/*`, merge vers `DEV`, puis release vers `main`

Script utilitaire: `git.sh`

Exemples:

```bash
./git.sh nouvelle-feature 11-amelioration-x
./git.sh fusion-dev feature/11-amelioration-x
./git.sh release-main "release(main): amelioration x"
```

Push initial:

```bash
./git.sh push-initial https://github.com/<compte>/<repo>.git
```

## 13) Guide de modification rapide (pour IA/dev)

Pour ajouter une fonctionnalite:

1. Ajouter/adapter requetes SQL dans un `Repository`.
2. Ajouter logique metier dans le `Service`.
3. Exposer via `Controller`.
4. Ajouter/adapter vue dans `app/views/...`.
5. Declarer route dans `app/config/routes.php`.
6. Si schema impacte:
   - ajouter script SQL versionne dans `sql/`.
7. Mettre a jour `todo` et `README.md`.

## 14) Points d'attention / limitations

- Le dashboard "dons distribues par ville" est base sur les besoins `status='dispatche'`.
  - Limitation structurelle: `MvtStock` ne contient pas `idVille` ni `idBesoin`.
- Pas de suite de tests automatises pour l'instant.
- `docker-compose.yml` provient du skeleton initial et cible `public/`.
  - Le projet courant utilise une entree racine (`index.php` a la racine).
  - Adapter la commande Docker si vous voulez l'utiliser.
- `vendor/` est versionne dans ce projet.

## 15) Fichiers clefs a lire en premier

Si vous reprenez le projet, lisez dans cet ordre:

1. `app/config/routes.php`
2. `app/services/DistributionService.php`
3. `app/services/StockService.php`
4. `app/services/DonService.php`
5. `app/services/BesoinService.php`
6. `app/services/DashboardService.php`
7. `app/views/layout.php`
8. `app/views/dashboard/index.php`
9. `sql/16022026-01-schema.sql`
