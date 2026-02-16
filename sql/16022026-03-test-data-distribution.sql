USE bngrc_db;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE MvtStock;
TRUNCATE TABLE besoins;
TRUNCATE TABLE dons;
TRUNCATE TABLE StockBNGRC;
TRUNCATE TABLE produit;
TRUNCATE TABLE categories;
TRUNCATE TABLE unite;
TRUNCATE TABLE ville;
TRUNCATE TABLE regions;

SET FOREIGN_KEY_CHECKS = 1;

-- Regions (hypothese demandee: 1 et 2)
INSERT INTO regions (idRegion, nom) VALUES
    (1, 'Atsinanana'),
    (2, 'Boeny');

-- Villes (2 zones seulement)
INSERT INTO ville (idVille, idRegion, nom) VALUES
    (1, 1, 'Toamasina'),
    (2, 2, 'Majunga');

-- Referentiels
INSERT INTO categories (idCategorie, nom) VALUES
    (1, 'Alimentaire'),
    (2, 'Materiel');

INSERT INTO unite (idUnite, nom) VALUES
    (1, 'kg'),
    (2, 'litre'),
    (3, 'piece');

INSERT INTO produit (idProduit, nom, idCategorie, prixUnitaire) VALUES
    (1, 'Riz', 1, 3500.00),
    (2, 'Huile', 1, 12000.00),
    (3, 'Couverture', 2, 25000.00);

-- Dons recus
INSERT INTO dons (idDon, idProduit, idUnite, quantite, donateur, dateDon) VALUES
    (1, 1, 1, 120.00, 'UNICEF', '2026-02-16 07:30:00'),
    (2, 2, 2, 10.00, 'Croix-Rouge', '2026-02-16 07:45:00');

-- Stock BNGRC disponible
INSERT INTO StockBNGRC (idStock, idProduit, idUnite, quantite) VALUES
    (1, 1, 1, 120.00),
    (2, 2, 2, 10.00);

-- Mouvements d'entree (don)
INSERT INTO MvtStock (idMvt, typeMvt, idProduit, idUnite, quantite, dateMvt) VALUES
    (1, 'don', 1, 1, 120.00, '2026-02-16 07:30:00'),
    (2, 'don', 2, 2, 10.00, '2026-02-16 07:45:00');

-- Besoins a traiter par ordre de date
INSERT INTO besoins (idBesoin, idVille, idProduit, quantite, idUnite, status, date) VALUES
    -- Cas partiel: besoin huile 20, stock dispo 10
    (1, 1, 2, 20.00, 2, 'non_dispatche', '2026-02-16 08:00:00'),

    -- Cas complet: besoin riz 100, stock dispo 120
    (2, 1, 1, 100.00, 1, 'non_dispatche', '2026-02-16 09:00:00'),

    -- Cas partiel: apres le besoin precedent, il reste 20 riz pour besoin 80
    (3, 2, 1, 80.00, 1, 'non_dispatche', '2026-02-16 10:00:00'),

    -- Cas non servi: aucun stock de couverture/piece
    (4, 2, 3, 30.00, 3, 'non_dispatche', '2026-02-16 11:00:00'),

    -- Deja traite (ignore par la simulation)
    (5, 1, 1, 15.00, 1, 'dispatche', '2026-02-16 06:00:00');

-- Resultat attendu en simulation (avant validation):
-- total_besoins = 4
-- total_dispatcheables = 1
-- total_partiels = 2
-- total_non_servis = 1
