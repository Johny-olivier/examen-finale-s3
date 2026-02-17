# Examen Finale S3 - BNGRC

Application web PHP (FlightPHP) pour la gestion BNGRC: dons, besoins, stock, distribution manuelle, simulation/validation automatique, achats via dons en argent, et recapitulation montants en Ajax.

Contexte de donnees actuel: Madagascar, avec 5 villes cibles (Toamasina, Mananjary, Farafangana, Nosy Be, Morondava).

## 1) Objectif metier

L'application permet de:
- enregistrer des dons recus (nature et argent)
- enregistrer des besoins par ville
- initialiser et consulter le stock BNGRC
- distribuer manuellement des produits a une ville
- simuler un dispatch automatique (mode date, quantite ou proportionnel) puis valider le dispatch reel
- reinitialiser les donnees vers un point de depart SQL
- acheter des besoins restants via dons en argent (avec frais configurable)
- consulter une recapitulation des montants (totaux/satisfaits/restants) avec actualisation Ajax
- gerer les referentiels (CRUD)

## 2) Fonctionnalites implementees

- Ajouter stock BNGRC
- Consulter le stock
- Inserer des dons
- Inserer des besoins (par ville)
- Distribution manuelle des dons
- Simulation + validation de dispatch automatique (modes `date`, `quantite`, `proportionnel`)
- Reinitialisation des donnees vers le point de depart SQL
- Dashboard avec details par ville (lightbox)
- Achats via dons en argent (filtre ville + historique)
- Recapitulation montants avec bouton actualiser en Ajax
- CRUD referentiels: regions, villes, categories, produits, unites, types de parametre achat, parametres achat
- UI moderne (sidebar, mode clair/sombre, select personnalise y compris contenu dynamique)

## 3) Regles metier principales

### Distribution automatique
- source: besoins `status='non_dispatche'`
- modes disponibles:
  - `date`: ordre `date ASC`, puis `idBesoin ASC`
  - `quantite`: ordre `quantite ASC`, puis `date ASC`, puis `idBesoin ASC`
  - `proportionnel`: allocation par coefficient `besoin_i * stock / somme_besoins`, avec arrondi inferieur
- simulation: sans impact BD
- validation: decompte stock + insertion `MvtStock(typeMvt='distribution')` + trace dans `DistributionVille`
- besoin complet: `status='dispatche'`
- besoin partiel: mise a jour de `besoins.quantite` avec la quantite restante
- bouton de reinitialisation: rejoue `sql/16022026-02-init-data.sql` pour revenir au point de depart

### Distribution manuelle
- choix libre ville/produit/unite/quantite
- blocage si stock insuffisant
- decrement stock + mouvement `distribution`
- trace dans `DistributionVille`
- si distribution materielle: mise a jour des besoins correspondants (`ville + produit + unite`) par ordre `date ASC`, `idBesoin ASC`
  - couverture complete: `status='dispatche'`
  - couverture partielle: mise a jour `besoins.quantite` restante
- si distribution d'argent (`Argent` / `Ar`): pas de mise a jour automatique des besoins (achat manuel requis)

### Achats via dons en argent
- base de travail: besoins restants (`status='non_dispatche'`)
- montant achat: `quantite * prixUnitaire`
- montant total avec frais: `sous_total + (sous_total * tauxFrais/100)`
- taux frais configurable via `parametreAchat`
- financement par argent deja distribue a la ville (`DistributionVille` avec `produit='Argent'`, `unite='Ar'`)
- achat bloque si fonds en argent distribue insuffisants pour la ville
- achat bloque si le produit existe encore dans les dons restants (`StockBNGRC > 0`)
- achat valide: insertion `achats` + ajout stock + mouvement `MvtStock(typeMvt='achat')`
- achat partiel: mise a jour de `besoins.quantite` (reste reel)
- si la quantite achetee couvre le besoin, le besoin passe en `status='achete'`

### Recapitulation montants
- page dediee avec refresh Ajax
- affiche:
  - besoins totaux (montant)
  - besoins satisfaits (montant)
  - besoins restants (montant)
  - taux de satisfaction
  - detail par ville

## 4) Stack technique

- PHP 8.x
- FlightPHP 3.x
- PDO via `Flight::db()`
- MySQL/MariaDB
- Bootstrap 5 + Font Awesome + CSS custom + JS vanilla
- Tracy (debug)

## 5) Architecture

Architecture MVC par fonctionnalite:
- `app/repositories/*Repository.php`: SQL uniquement
- `app/services/*Service.php`: logique metier
- `app/controllers/*Controller.php`: HTTP + rendu + redirection
- `app/views/*`: templates

Conventions appliquees:
- noms de fonctions/variables en francais
- base de donnees injectee via `new XRepository(Flight::db())`
- helpers de vues centralises dans `app/config/vue_helpers.php`

## 6) Structure principale

```txt
app/
  config/
    bootstrap.php
    config.php
    constant.php
    routes.php
    services.php
    vue_helpers.php
  controllers/
    AchatController.php
    BesoinController.php
    DashboardController.php
    DistributionController.php                # simulation/validation auto
    DistributionManuelleController.php        # distribution manuelle
    DonController.php
    RecapitulationController.php
    ReferentielController.php
    StockController.php
  repositories/
    AchatRepository.php
    BesoinRepository.php
    DashboardRepository.php
    DistributionRepository.php
    DistributionManuelleRepository.php
    DonRepository.php
    RecapitulationRepository.php
    ReferentielRepository.php
    StockRepository.php
  services/
    AchatService.php
    BesoinService.php
    DashboardService.php
    DistributionService.php
    DistributionManuelleService.php
    DonService.php
    RecapitulationService.php
    ReferentielService.php
    StockService.php
  views/
    achats/index.php
    besoins/index.php
    dashboard/index.php
    distribution/index.php
    dons/index.php
    recapitulation/index.php
    referentiels/index.php
    simulation/index.php
    stock/consultation.php
    stock/initialisation.php
    errors/404.php
    errors/500.php
    layout.php
assets/
  css/app.css
  js/dashboard-details.js
  js/recapitulation.js
  js/select-personnalise.js
  js/theme.js
sql/
  16022026-01-schema.sql
  16022026-02-init-data.sql
  16022026-03-test-data-distribution.sql
```

## 7) Base de donnees

### Tables principales
- `regions`, `ville`
- `categories`, `produit`, `unite`
- `besoins` (`quantite` restant, `quantiteInitiale`, `status`: `non_dispatche`, `dispatche`, `achete`)
- `dons`
- `typeParametreAchat`, `parametreAchat`
- `achats`, `achatDons`
- `StockBNGRC`
- `MvtStock` (don/distribution/achat)
- `DistributionVille`

### Scripts SQL (3 fichiers uniquement)

1. `sql/16022026-01-schema.sql`
   - recree la base via `DROP DATABASE IF EXISTS`
   - cree toutes les tables/index
2. `sql/16022026-02-init-data.sql`
   - charge les referentiels de depart
   - charge la configuration frais achat
   - charge les donnees initiales (dons, stock, mouvements, besoins)
   - inclut `Argent` / `Ar`
3. `sql/16022026-03-test-data-distribution.sql`
   - jeu de donnees test (dons nature + argent, besoins, stock, mouvements)

## 8) Routes HTTP

Base: `app/config/routes.php`

- `GET /` -> redirection `/dashboard`

### Dashboard
- `GET /dashboard`

### Referentiels
- `GET /referentiels`
- `POST /referentiels/regions/ajouter`
- `POST /referentiels/regions/modifier`
- `POST /referentiels/regions/supprimer`
- `POST /referentiels/villes/ajouter`
- `POST /referentiels/villes/modifier`
- `POST /referentiels/villes/supprimer`
- `POST /referentiels/categories/ajouter`
- `POST /referentiels/categories/modifier`
- `POST /referentiels/categories/supprimer`
- `POST /referentiels/produits/ajouter`
- `POST /referentiels/produits/modifier`
- `POST /referentiels/produits/supprimer`
- `POST /referentiels/unites/ajouter`
- `POST /referentiels/unites/modifier`
- `POST /referentiels/unites/supprimer`
- `POST /referentiels/types-parametre-achat/ajouter`
- `POST /referentiels/types-parametre-achat/modifier`
- `POST /referentiels/types-parametre-achat/supprimer`
- `POST /referentiels/parametres-achat/ajouter`
- `POST /referentiels/parametres-achat/modifier`
- `POST /referentiels/parametres-achat/supprimer`

### Dons / Besoins
- `GET /dons`
- `POST /dons`
- `GET /besoins`
- `POST /besoins`

### Stock
- `GET /stock/initialisation`
- `POST /stock/initialisation`
- `GET /stock/consultation`

### Distribution manuelle
- `GET /distribution`
- `POST /distribution`

### Simulation / Validation dispatch
- `GET /simulation-dispatch`
- `POST /simulation-dispatch/simuler`
- `POST /simulation-dispatch/valider`
- `POST /simulation-dispatch/reinitialiser`

### Achats
- `GET /achats`
- `POST /achats`

### Recapitulation
- `GET /recapitulation`
- `GET /recapitulation/donnees` (Ajax JSON)

### Erreurs
- notFound -> `app/views/errors/404.php`
- error -> `app/views/errors/500.php`

## 9) Affichage mutualise

Helpers centralises dans `app/config/vue_helpers.php`:
- `vue_echapper(...)`
- `vue_formater_nombre(...)`
- `vue_formater_quantite(..., unite)`
- `vue_formater_montant_ar(...)`
- `vue_formater_prix_unitaire_ar(...)`
- `vue_formater_date_humaine(..., bool $inclureHeure = true)`

Effets:
- quantites avec unite (ex: `20,00 kg`)
- dates humaines (ex: `12 fev 2026 09:30`)
- montants/prix en `Ar`

## 10) Demarrage local

### Prerequis
- PHP 8+
- MySQL/MariaDB
- Composer
- Apache + `mod_rewrite`

### Installation

```bash
composer install
```

Configurer la BD dans `app/config/config.php`.

### Import SQL

```bash
mysql -u root -p < sql/16022026-01-schema.sql
mysql -u root -p < sql/16022026-02-init-data.sql
mysql -u root -p < sql/16022026-03-test-data-distribution.sql
```

### URL locale (exemple)

`http://localhost/l2/examens/fev/examen-finale-s3/`

## 11) Configuration URL (local/FTP)

Fichier: `app/config/constant.php`

- `BASE_URL` auto-calcule via `$_SERVER['SCRIPT_NAME']`
- surcharge possible via variable d'environnement `APP_BASE_URL`

## 12) Workflow git equipe

Regles:
- pas de commit direct sur `main`
- pas de commit direct sur `DEV`
- travail sur `feature/*` -> merge vers `DEV` -> release vers `main`

Script utilitaire: `git.sh`

## 13) Limitations connues

- Le calcul des montants de recapitulation repose sur l'etat actuel des besoins + achats saisis.
- Pas de tests automatises (unitaires/integration) pour le moment.
