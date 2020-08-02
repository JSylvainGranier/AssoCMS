create table sessions (
	idSession int(11) primary key AUTO_INCREMENT,
	fkIdPersonne int(11) not null,
    longSessionToken varchar(255),
    nbReUse int(11) default 0,
    lastUpdateOn datetime,
    fkLastUpdateBy int(11)
);

insert into sessions 
select idPersonne, longSessionToken, lastUpdateOn, fkLastUpdateBy from personne where longSessionToken <> '';

alter table personne drop column lastConnexionDate;
alter table personne drop column longSessionToken;
alter table personne add column allowedToConnect tinyint(1) NOT NULL DEFAULT '1';
alter table personne add column dateNaissance datetime;
alter table personne add column idFamille int(11) NOT NULL DEFAULT -1;

update personne set idFamille = idPersonne;
update personne set idFamille = 6 where idPersonne = 5; 
update personne set idFamille = 23 where idPersonne = 24; 
update personne set idFamille = 25 where idPersonne = 134; 
update personne set idFamille = 48 where idPersonne = 139; 
update personne set idFamille = 162 where idPersonne = 168; 
update personne set idFamille = 213 where idPersonne = 214; 


CREATE TABLE inscription (
  idInscription int(11) NOT NULL AUTO_INCREMENT,
  etat tinyint(1) NOT NULL DEFAULT '1',
  commentaire longtext COLLATE latin1_general_ci,
  debut datetime DEFAULT NULL,
  fin datetime DEFAULT NULL,
  lastUpdateOn datetime DEFAULT NULL,
  fkLastUpdateBy int(11) DEFAULT NULL,
  PRIMARY KEY (idInscription),
  KEY fkLastUpdateBy (fkLastUpdateBy)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;


CREATE TABLE inscription_personne_produit (
  idIPP int(11) NOT NULL AUTO_INCREMENT,
  fkInscription int(11) NOT NULL,
  fkPersonne int(11) NOT NULL,
  fkProduit int(11) NOT NULL,
  dateAcceptationConditionsLegales datetime DEFAULT NULL,
  conditionsLegales longtext COLLATE latin1_general_ci,
  lastUpdateOn datetime DEFAULT NULL,
  fkLastUpdateBy int(11) DEFAULT NULL,
  quantite double DEFAULT '1',
  PRIMARY KEY (idIPP),
  KEY fkLastUpdateBy (fkLastUpdateBy),
  KEY fkInscription (fkInscription),
  KEY fkPersonne (fkPersonne),
  KEY fkProduit (fkProduit)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

CREATE TABLE produit (
  idProduit int(11) NOT NULL AUTO_INCREMENT,
  libelle varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  description longtext COLLATE latin1_general_ci,
  politiqueTarifaire longtext COLLATE latin1_general_ci,
  debutDisponibilite datetime DEFAULT NULL,
  finDisponibilite datetime DEFAULT NULL,
  quantiteDisponible int(11) DEFAULT NULL,
  produitOrdre int(11) DEFAULT 0,
  fkProduitRequis int(11),
  accesDirect TINYINT(1) NOT NULL DEFAULT 0,
  conditionsLegales longtext COLLATE latin1_general_ci,
  lastUpdateOn datetime DEFAULT NULL,
  fkLastUpdateBy int(11) DEFAULT NULL,
  PRIMARY KEY (idProduit),
  KEY fkLastUpdateBy (fkLastUpdateBy)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

CREATE TABLE reglement (
  idReglement int(11) NOT NULL AUTO_INCREMENT,
  dateEcheance datetime DEFAULT NULL,
  datePerception datetime DEFAULT NULL,
  modePerception varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  refPerception varchar(120) COLLATE latin1_general_ci DEFAULT NULL,
  libelle varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  montant double DEFAULT NULL,
  fkInscription int(11) NOT NULL,
  lastUpdateOn datetime DEFAULT NULL,
  fkLastUpdateBy int(11) DEFAULT NULL,
  PRIMARY KEY (idReglement),
  KEY fkLastUpdateBy (fkLastUpdateBy),
  KEY fkInscription (fkInscription)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
