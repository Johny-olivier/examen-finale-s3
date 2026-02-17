USE bngrc_db;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE achatDons;
TRUNCATE TABLE achats;
TRUNCATE TABLE parametreAchat;
TRUNCATE TABLE typeParametreAchat;
TRUNCATE TABLE DistributionVille;
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

-- Regions reelles des villes du fichier cyclone S3
INSERT INTO regions (idRegion, nom) VALUES
    (1, 'Atsinanana'),
    (2, 'Vatovavy'),
    (3, 'Atsimo-Atsinanana'),
    (4, 'Diana'),
    (5, 'Menabe');

-- Villes
INSERT INTO ville (idVille, idRegion, nom) VALUES
    (1, 1, 'Toamasina'),
    (2, 2, 'Mananjary'),
    (3, 3, 'Farafangana'),
    (4, 4, 'Nosy Be'),
    (5, 5, 'Morondava');

-- Referentiels categories / unites / produits
INSERT INTO categories (idCategorie, nom) VALUES
    (1, 'nature'),
    (2, 'materiel'),
    (3, 'argent');

INSERT INTO unite (idUnite, nom) VALUES
    (1, 'kg'),
    (2, 'L'),
    (3, 'piece'),
    (4, 'Ar');

INSERT INTO produit (idProduit, nom, idCategorie, prixUnitaire) VALUES
    (1, 'Riz', 1, 3000.00),
    (2, 'Eau', 1, 1000.00),
    (3, 'Tole', 2, 25000.00),
    (4, 'Bache', 2, 15000.00),
    (5, 'Argent', 3, 1.00),
    (6, 'Huile', 1, 6000.00),
    (7, 'Clous', 2, 8000.00),
    (8, 'Haricots', 1, 4000.00),
    (9, 'Bois', 2, 10000.00),
    (10, 'Groupe electrogene', 2, 6750000.00);

-- Parametre de frais d'achat (x%)
INSERT INTO typeParametreAchat (idTypeParametreAchat, code, libelle) VALUES
    (1, 'frais_achat_pourcentage', 'Taux de frais d''achat (%)');

INSERT INTO parametreAchat (
    idParametreAchat,
    idTypeParametreAchat,
    valeurDecimal,
    dateApplication,
    actif
) VALUES
    (1, 1, 10.0000, '2026-02-17 00:00:00', 1);

-- Dons de depart (fichier cyclone S3)
INSERT INTO dons (idDon, idProduit, idUnite, quantite, donateur, dateDon) VALUES
    (1, 5, 4, 5000000.00, 'Donateur anonyme', '2026-02-16 08:01:00'),
    (2, 5, 4, 3000000.00, 'Donateur anonyme', '2026-02-16 08:02:00'),
    (3, 5, 4, 4000000.00, 'Donateur anonyme', '2026-02-17 08:01:00'),
    (4, 5, 4, 1500000.00, 'Donateur anonyme', '2026-02-17 08:02:00'),
    (5, 5, 4, 6000000.00, 'Donateur anonyme', '2026-02-17 08:03:00'),
    (6, 1, 1, 400.00, 'Donateur anonyme', '2026-02-16 08:03:00'),
    (7, 2, 2, 600.00, 'Donateur anonyme', '2026-02-16 08:04:00'),
    (8, 3, 3, 50.00, 'Donateur anonyme', '2026-02-17 08:04:00'),
    (9, 4, 3, 70.00, 'Donateur anonyme', '2026-02-17 08:05:00'),
    (10, 8, 1, 100.00, 'Donateur anonyme', '2026-02-17 08:06:00'),
    (11, 1, 1, 2000.00, 'Donateur anonyme', '2026-02-18 08:01:00'),
    (12, 3, 3, 300.00, 'Donateur anonyme', '2026-02-18 08:02:00'),
    (13, 2, 2, 5000.00, 'Donateur anonyme', '2026-02-18 08:03:00'),
    (14, 5, 4, 20000000.00, 'Donateur anonyme', '2026-02-19 08:01:00'),
    (15, 4, 3, 500.00, 'Donateur anonyme', '2026-02-19 08:02:00'),
    (16, 8, 1, 88.00, 'Donateur anonyme', '2026-02-17 08:07:00');

-- Stock de depart 
INSERT INTO StockBNGRC (idStock, idProduit, idUnite, quantite) VALUES
    (1, 1, 1, 2400.00), -- Riz (kg)
    (2, 2, 2, 5600.00), -- Eau (L)
    (3, 3, 3, 350.00), -- Tole (piece)
    (4, 4, 3, 570.00), -- Bache (piece)
    (5, 5, 4, 39500000.00), -- Argent (Ar)
    (6, 8, 1, 188.00); -- Haricots (kg)

-- Mouvements d'entree (dons)
INSERT INTO MvtStock (idMvt, typeMvt, idProduit, idUnite, quantite, dateMvt) VALUES
    (1, 'don', 5, 4, 5000000.00, '2026-02-16 08:01:00'),
    (2, 'don', 5, 4, 3000000.00, '2026-02-16 08:02:00'),
    (3, 'don', 5, 4, 4000000.00, '2026-02-17 08:01:00'),
    (4, 'don', 5, 4, 1500000.00, '2026-02-17 08:02:00'),
    (5, 'don', 5, 4, 6000000.00, '2026-02-17 08:03:00'),
    (6, 'don', 1, 1, 400.00, '2026-02-16 08:03:00'),
    (7, 'don', 2, 2, 600.00, '2026-02-16 08:04:00'),
    (8, 'don', 3, 3, 50.00, '2026-02-17 08:04:00'),
    (9, 'don', 4, 3, 70.00, '2026-02-17 08:05:00'),
    (10, 'don', 8, 1, 100.00, '2026-02-17 08:06:00'),
    (11, 'don', 1, 1, 2000.00, '2026-02-18 08:01:00'),
    (12, 'don', 3, 3, 300.00, '2026-02-18 08:02:00'),
    (13, 'don', 2, 2, 5000.00, '2026-02-18 08:03:00'),
    (14, 'don', 5, 4, 20000000.00, '2026-02-19 08:01:00'),
    (15, 'don', 4, 3, 500.00, '2026-02-19 08:02:00'),
    (16, 'don', 8, 1, 88.00, '2026-02-17 08:07:00');

-- Besoins de depart
INSERT INTO besoins (idVille, idProduit, quantite, quantiteInitiale, idUnite, status, date) VALUES
    -- 2026-02-15 (ordre: 1,2,3,4,5,6,7,9,14,16,20,22,26)
    (1, 4, 200.00, 200.00, 3, 'non_dispatche', '2026-02-15 06:01:00'), -- Toamasina, Bache
    (4, 3, 40.00, 40.00, 3, 'non_dispatche', '2026-02-15 06:02:00'), -- Nosy Be, Tole
    (2, 5, 6000000.00, 6000000.00, 4, 'non_dispatche', '2026-02-15 06:03:00'), -- Mananjary, Argent
    (1, 2, 1500.00, 1500.00, 2, 'non_dispatche', '2026-02-15 06:04:00'), -- Toamasina, Eau
    (4, 1, 300.00, 300.00, 1, 'non_dispatche', '2026-02-15 06:05:00'), -- Nosy Be, Riz
    (2, 3, 80.00, 80.00, 3, 'non_dispatche', '2026-02-15 06:06:00'), -- Mananjary, Tole
    (4, 5, 4000000.00, 4000000.00, 4, 'non_dispatche', '2026-02-15 06:07:00'), -- Nosy Be, Argent
    (2, 1, 500.00, 500.00, 1, 'non_dispatche', '2026-02-15 06:09:00'), -- Mananjary, Riz
    (3, 2, 1000.00, 1000.00, 2, 'non_dispatche', '2026-02-15 06:14:00'), -- Farafangana, Eau
    (1, 10, 3.00, 3.00, 3, 'non_dispatche', '2026-02-15 06:16:00'), -- Toamasina, Groupe electrogene
    (5, 2, 1200.00, 1200.00, 2, 'non_dispatche', '2026-02-15 06:20:00'), -- Morondava, Eau
    (5, 9, 150.00, 150.00, 3, 'non_dispatche', '2026-02-15 06:22:00'), -- Morondava, Bois
    (3, 9, 100.00, 100.00, 3, 'non_dispatche', '2026-02-15 06:26:00'), -- Farafangana, Bois

    -- 2026-02-16 (ordre: 8,10,11,12,13,15,17,18,19,21,23,24,25)
    (3, 4, 150.00, 150.00, 3, 'non_dispatche', '2026-02-16 06:08:00'), -- Farafangana, Bache
    (3, 5, 8000000.00, 8000000.00, 4, 'non_dispatche', '2026-02-16 06:10:00'), -- Farafangana, Argent
    (5, 1, 700.00, 700.00, 1, 'non_dispatche', '2026-02-16 06:11:00'), -- Morondava, Riz
    (1, 5, 12000000.00, 12000000.00, 4, 'non_dispatche', '2026-02-16 06:12:00'), -- Toamasina, Argent
    (5, 5, 10000000.00, 10000000.00, 4, 'non_dispatche', '2026-02-16 06:13:00'), -- Morondava, Argent
    (5, 4, 180.00, 180.00, 3, 'non_dispatche', '2026-02-16 06:15:00'), -- Morondava, Bache
    (1, 1, 800.00, 800.00, 1, 'non_dispatche', '2026-02-16 06:17:00'), -- Toamasina, Riz
    (4, 8, 200.00, 200.00, 1, 'non_dispatche', '2026-02-16 06:18:00'), -- Nosy Be, Haricots
    (2, 7, 60.00, 60.00, 1, 'non_dispatche', '2026-02-16 06:19:00'), -- Mananjary, Clous
    (3, 1, 600.00, 600.00, 1, 'non_dispatche', '2026-02-16 06:21:00'), -- Farafangana, Riz
    (1, 3, 120.00, 120.00, 3, 'non_dispatche', '2026-02-16 06:23:00'), -- Toamasina, Tole
    (4, 7, 30.00, 30.00, 1, 'non_dispatche', '2026-02-16 06:24:00'), -- Nosy Be, Clous
    (2, 6, 120.00, 120.00, 2, 'non_dispatche', '2026-02-16 06:25:00'); -- Mananjary, Huile
