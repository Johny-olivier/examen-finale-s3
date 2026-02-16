USE bngrc_db;

INSERT INTO regions (idRegion, nom) VALUES
    (1, 'Atsinanana'),
    (2, 'Boeny')
ON DUPLICATE KEY UPDATE nom = VALUES(nom);

INSERT INTO ville (idRegion, nom) VALUES
    (1, 'Toamasina'),
    (2, 'Majunga')
ON DUPLICATE KEY UPDATE idRegion = VALUES(idRegion);
