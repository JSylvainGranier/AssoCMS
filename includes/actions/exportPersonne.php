<?php
if (! Roles::isGestionnaireCategorie ()) {
	throw new Exception ( "Il faut être au minimum gestionnaire pour pouvoir exporter les données des membres." );
}

// error_reporting(0);

$page->setStandardOuputDisabled ( true );

$id = $ARGS ["id"];

$inIdList = "";

if (is_array ( $id )) {
	foreach ( $id as $anId ) {
		$inIdList .= "{$anId},";
	}
} else {
	$inIdList = $id;
}

$inIdList .= "-1";

$sql = "select * from personne where idPersonne in ({$inIdList}) order by nom, prenom";
$sqlAffiliate = "select * from personne_categorie where fkPersonne in ({$inIdList})";

$personneDao = new Personne ();
$personnesList = $personneDao->getObjectListFromQuery ( $sql );

$affiliationsDao = new AffiliationCategorie ();
$affiliations = $affiliationsDao->getObjectListFromQuery ( $sqlAffiliate );
$affPersonneCat = array ();
foreach ( $affiliations as $anAffiliation ) {
	/* @var $anAffiliation AffiliationCategorie */
	$catArray = array ();
	if (array_key_exists ( $anAffiliation->personne, $affPersonneCat )) {
		$catArray = $affPersonneCat [$anAffiliation->personne];
	}
	$catArray [] = $anAffiliation->categorie;
	$affPersonneCat [$anAffiliation->personne] = $catArray;
}

$categories = new Categorie ();
$categories = $categories->getAll ();

$fileName = "Membres " . SITE_TITLE;

if (array_key_exists ( "title", $ARGS )) {
	$fileName = $ARGS ["title"];
}

header ( "Content-Type: application/csv; name=\"{$fileName}.csv\"" );
header ( "Content-Disposition: inline; filename=\"{$fileName}.csv\"" );

$sep = ";";

$headings = array (
		'Profil VISA',
		utf8_decode ( 'Civilité' ),
		'Nom',
		utf8_decode ( 'Prénom' ),
		'Adresse L1',
		'Adresse L2',
		'Adresse L3',
		'CP',
		'Ville',
		'eMail',
		'Papier',
		'Droit Image',
		utf8_decode ( 'Téléphone Fixe' ),
		utf8_decode ( 'Téléphone Port.' ) 
);

foreach ( $categories as $aCategorie ) {
	/* @var $aCategorie Categorie */
	$headings [] = utf8_decode ( $aCategorie->nom );
}

foreach ( $headings as $aCell ) {
	echo $aCell . $sep;
}

echo "\n";

foreach ( $personnesList as $aPersonne ) {
	/* @var $aPersonne Personne */
	
	$url = SITE_ROOT . "index.php?show&class=Personne&id=" . $aPersonne->getPrimaryKey ();
	
	$rowValue = array (
			'"=LIEN_HYPERTEXTE(""' . $url . '"";""Profil"")"',
			$aPersonne->civilite . "",
			utf8_decode ( $aPersonne->nom . "" ),
			utf8_decode ( $aPersonne->prenom . "" ),
			utf8_decode ( $aPersonne->adrL1 . "" ),
			utf8_decode ( $aPersonne->adrL2 . "" ),
			utf8_decode ( $aPersonne->adrL3 . "" ),
			utf8_decode ( $aPersonne->adrCP . "" ),
			utf8_decode ( $aPersonne->adrVille . "" ),
			utf8_decode ( $aPersonne->email ),
			$aPersonne->wantPaperRecap ? "1" : "0",
			$aPersonne->allowPublishMyFace ? "Accordé" : "Refusé",
			utf8_decode ( $aPersonne->telFixe ),
			utf8_decode ( $aPersonne->telPortable ) 
	);
	
	$aPersCategories = $affPersonneCat [$aPersonne->getPrimaryKey ()];
	$catOfPersonne = array ();
	foreach ( $categories as $aCategorie ) {
		$catValue = "";
		if (in_array ( $aCategorie->getPrimaryKey (), $aPersCategories )) {
			switch ($aPersonne->civilite) {
				case "Monsieur" :
				case "Madame" :
					$catValue = "1";
					break;
				case "Monsieur et Madame" :
					$catValue = "2";
			}
		}
		$catOfPersonne [] = $catValue;
	}
	
	$rowValue = array_merge ( $rowValue, $catOfPersonne );
	
	foreach ( $rowValue as $aCell ) {
		echo $aCell . $sep;
	}
	
	echo "\n";
}

?>