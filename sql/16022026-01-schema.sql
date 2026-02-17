-- CREATION DE LA BASE

DROP DATABASE IF EXISTS bngrc_db;
CREATE DATABASE bngrc_db;
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
    quantiteInitiale DECIMAL(12,2) NOT NULL CHECK (quantiteInitiale > 0),
    idUnite INT NOT NULL,
    status ENUM('dispatche', 'achete', 'non_dispatche') DEFAULT 'non_dispatche',
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

CREATE INDEX idx_dons_produit_unite_date ON dons(idProduit, idUnite, dateDon, idDon);

-- TABLE: typeParametreAchat

CREATE TABLE typeParametreAchat (
    idTypeParametreAchat INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(100) NOT NULL UNIQUE,
    libelle VARCHAR(150) NOT NULL
);

-- TABLE: parametreAchat

CREATE TABLE parametreAchat (
    idParametreAchat INT AUTO_INCREMENT PRIMARY KEY,
    idTypeParametreAchat INT NOT NULL,
    valeurDecimal DECIMAL(10,4) NOT NULL,
    dateApplication DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    dateMaj DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_parametre_achat_type
        FOREIGN KEY (idTypeParametreAchat)
        REFERENCES typeParametreAchat(idTypeParametreAchat)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE INDEX idx_parametre_achat_type_actif
    ON parametreAchat(idTypeParametreAchat, actif, dateApplication);

-- TABLE: achats

CREATE TABLE achats (
    idAchat INT AUTO_INCREMENT PRIMARY KEY,
    idBesoin INT NOT NULL,
    idVille INT NOT NULL,
    idProduit INT NOT NULL,
    idUnite INT NOT NULL,
    quantite DECIMAL(12,2) NOT NULL CHECK (quantite > 0),
    prixUnitaire DECIMAL(12,2) NOT NULL CHECK (prixUnitaire >= 0),
    tauxFrais DECIMAL(8,4) NOT NULL CHECK (tauxFrais >= 0),
    montantSousTotal DECIMAL(14,2) NOT NULL CHECK (montantSousTotal >= 0),
    montantFrais DECIMAL(14,2) NOT NULL CHECK (montantFrais >= 0),
    montantTotal DECIMAL(14,2) NOT NULL CHECK (montantTotal >= 0),
    dateAchat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('saisi', 'annule') NOT NULL DEFAULT 'saisi',
    commentaire VARCHAR(255) NULL,

    CONSTRAINT fk_achats_besoin
        FOREIGN KEY (idBesoin)
        REFERENCES besoins(idBesoin)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_achats_ville
        FOREIGN KEY (idVille)
        REFERENCES ville(idVille)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_achats_produit
        FOREIGN KEY (idProduit)
        REFERENCES produit(idProduit)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_achats_unite
        FOREIGN KEY (idUnite)
        REFERENCES unite(idUnite)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE INDEX idx_achats_ville_date ON achats(idVille, dateAchat, idAchat);
CREATE INDEX idx_achats_besoin ON achats(idBesoin);
CREATE INDEX idx_achats_produit_unite ON achats(idProduit, idUnite);

-- TABLE: achatDons

CREATE TABLE achatDons (
    idAchatDon INT AUTO_INCREMENT PRIMARY KEY,
    idAchat INT NOT NULL,
    idDon INT NOT NULL,
    montantAffecte DECIMAL(14,2) NOT NULL CHECK (montantAffecte > 0),

    UNIQUE KEY uk_achat_don (idAchat, idDon),

    CONSTRAINT fk_achat_don_achat
        FOREIGN KEY (idAchat)
        REFERENCES achats(idAchat)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_achat_don_don
        FOREIGN KEY (idDon)
        REFERENCES dons(idDon)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE INDEX idx_achat_don_don ON achatDons(idDon, idAchat);

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
    typeMvt ENUM('don', 'distribution', 'achat') NOT NULL,
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

-- TABLE: DistributionVille (distribution manuelle par ville)

CREATE TABLE DistributionVille (
    idDistribution INT AUTO_INCREMENT PRIMARY KEY,
    idVille INT NOT NULL,
    idProduit INT NOT NULL,
    idUnite INT NOT NULL,
    quantite DECIMAL(12,2) NOT NULL CHECK (quantite > 0),
    dateDistribution DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_distribution_ville
        FOREIGN KEY (idVille)
        REFERENCES ville(idVille)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_distribution_produit
        FOREIGN KEY (idProduit)
        REFERENCES produit(idProduit)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_distribution_unite
        FOREIGN KEY (idUnite)
        REFERENCES unite(idUnite)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE INDEX idx_besoins_status_date_id ON besoins(status, date, idBesoin);
CREATE INDEX idx_distribution_ville_date ON DistributionVille(idVille, dateDistribution, idDistribution);
