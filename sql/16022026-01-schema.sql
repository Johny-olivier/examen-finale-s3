
-- CREATION DE LA BASE

CREATE DATABASE IF NOT EXISTS bngrc_db;
USE bngrc_db;

-- TABLE: regions

CREATE TABLE regions (
    idRegion INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE
);

-- TABLE: ville

CREATE TABLE ville (
    idVille INT AUTO_INCREMENT PRIMARY KEY,
    idRegion INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    UNIQUE KEY uk_ville_region_nom (idRegion, nom),
    CONSTRAINT fk_ville_region
        FOREIGN KEY (idRegion)
        REFERENCES regions(idRegion)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- TABLE: categories

CREATE TABLE categories (
    idCategorie INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE
);

-- TABLE: produit

CREATE TABLE produit (
    idProduit INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    idCategorie INT NOT NULL,
    prixUnitaire DECIMAL(12,2) DEFAULT 0,
    CONSTRAINT fk_produit_categorie
        FOREIGN KEY (idCategorie)
        REFERENCES categories(idCategorie)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- TABLE: unite

CREATE TABLE unite (
    idUnite INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE
);

-- TABLE: besoins

CREATE TABLE besoins (
    idBesoin INT AUTO_INCREMENT PRIMARY KEY,
    idVille INT NOT NULL,
    idProduit INT NOT NULL,
    quantite DECIMAL(12,2) NOT NULL CHECK (quantite > 0),
    idUnite INT NOT NULL,
    status ENUM('dispatche', 'non_dispatche') DEFAULT 'non_dispatche',
    date DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_besoins_ville
        FOREIGN KEY (idVille)
        REFERENCES ville(idVille)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_besoins_produit
        FOREIGN KEY (idProduit)
        REFERENCES produit(idProduit)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_besoins_unite
        FOREIGN KEY (idUnite)
        REFERENCES unite(idUnite)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- TABLE: dons

CREATE TABLE dons (
    idDon INT AUTO_INCREMENT PRIMARY KEY,
    idProduit INT NOT NULL,
    idUnite INT NOT NULL,
    quantite DECIMAL(12,2) NOT NULL CHECK (quantite > 0),
    donateur VARCHAR(150),
    dateDon DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_dons_produit
        FOREIGN KEY (idProduit)
        REFERENCES produit(idProduit)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_dons_unite
        FOREIGN KEY (idUnite)
        REFERENCES unite(idUnite)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- TABLE: StockBNGRC

CREATE TABLE StockBNGRC (
    idStock INT AUTO_INCREMENT PRIMARY KEY,
    idProduit INT NOT NULL,
    idUnite INT NOT NULL,
    quantite DECIMAL(12,2) NOT NULL DEFAULT 0 CHECK (quantite >= 0),
    UNIQUE KEY uk_stock_produit_unite (idProduit, idUnite),

    CONSTRAINT fk_stock_produit
        FOREIGN KEY (idProduit)
        REFERENCES produit(idProduit)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_stock_unite
        FOREIGN KEY (idUnite)
        REFERENCES unite(idUnite)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- TABLE: MvtStock

CREATE TABLE MvtStock (
    idMvt INT AUTO_INCREMENT PRIMARY KEY,
    typeMvt ENUM('don', 'distribution') NOT NULL,
    idProduit INT NOT NULL,
    idUnite INT NOT NULL,
    quantite DECIMAL(12,2) NOT NULL CHECK (quantite > 0),
    dateMvt DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_mvt_produit
        FOREIGN KEY (idProduit)
        REFERENCES produit(idProduit)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_mvt_unite
        FOREIGN KEY (idUnite)
        REFERENCES unite(idUnite)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE INDEX idx_besoins_status_date_id ON besoins(status, date, idBesoin);