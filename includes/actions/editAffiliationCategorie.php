<?php
$page->appendBody ( file_get_contents ( "includes/html/checkList.html" ) );
$page->asset ( "class", "AffiliationCategorie" );

/* @var $cat Categorie */
/* @var $pers Personne */
$cat = null;
$pers = null;

if (array_key_exists ( "categorie", $ARGS )) {
	$cat = new Categorie ( $ARGS ["categorie"] );
	$page->asset ( "attachmentId", $cat->getPrimaryKey () );
	$page->asset ( "attachmentClass", "Categorie" );
	$page->setCategorie ( $cat );
	$page->asset ( "listTitle", "Personnes affiliées à la section '{$cat->nom}'" );
}

if (array_key_exists ( "personne", $ARGS )) {
	$pers = new Personne ( $ARGS ["personne"] );
	$page->asset ( "attachmentId", $pers->getPrimaryKey () );
	$page->asset ( "attachmentClass", "Personne" );
	$page->asset ( "listTitle", "Catégories auxquelles {$pers->prenom} est affilié(e)" );
}

$rows = array ();

if ($cat != null) {
	
	$persList = new Personne ();
	$persList = $persList->getAll ();
	
	$persAffiliated = $cat->getAffiliatedPersonnes ();
	
	/* @var $aPers Personne */
	foreach ( $persList as $aPers ) {
		
		$value = $aPers->getPrimaryKey ();
		
		$selected = in_array ( $aPers, $persAffiliated ) ? "checked='checked'" : "";
		
		$rows [] = "<td><input type='checkbox' name='affectation[]' value='{$value}' {$selected} id='input{$aPers->getPrimaryKey()}'/></td><td><label for='input{$aPers->getPrimaryKey()}'>{$aPers->nom} {$aPers->prenom}</label></td>";
	}
} else if ($pers != null) {
	
	$catList = new Categorie ();
	$catList = $catList->getAll ();
	
	$catAffiliated = $pers->getCategoriesEffectivesList ();
	
	/* @var $aCat Categorie */
	foreach ( $catList as $aCat ) {
		if ($aCat->autoAffiliate)
			continue;
		
		$selected = in_array ( $aCat, $catAffiliated ) ? "checked='checked'" : "";
		
		$rows [] = "<td><input type='checkbox' name='affectation[]' value='{$aCat->getPrimaryKey()}' {$selected} id='input{$aCat->getPrimaryKey()}'/></td><td><label for='input{$aCat->getPrimaryKey()}'>{$aCat->nom}</label></td>";
	}
}

$html = "";
$zebre = false;
foreach ( $rows as $aRow ) {
	$altColor = "";
	if ($zebre) {
		$altColor = "altColor";
		$zebre = false;
	} else {
		$zebre = true;
	}
	$html .= "<tr class='{$altColor}'>{$aRow}</tr>\n";
}

$page->append ( "rowZone", $html );