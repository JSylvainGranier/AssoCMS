<?php
$page->appendBody ( file_get_contents ( "includes/html/list.html" ) );

/* @var $cat Categorie */
/* @var $pers Personne */
$cat = null;
$pers = null;

if (array_key_exists ( "categorie", $ARGS )) {
	$cat = new Categorie ( $ARGS ["categorie"] );
	$page->setCategorie ( $cat );
}

if (array_key_exists ( "personne", $ARGS )) {
	$pers = new Personne ( $ARGS ["personne"] );
}

if ($cat != null) {
	
	$page->asset ( "listTitle", "Personnes affiliées à la section '{$cat->nom}'" );
	
	if ($cat->autoAffiliate) {
		$page->appendBody ( "Cette section est à 'affiliation automatique', c'est à dire que tout le monde y est affilié, sans exception. " );
	} else {
		$persList = $cat->getAffiliatedPersonnes ();
		
		if (count ( $persList ) > 0) {
			/* @var $aPersonne Personne */
			foreach ( $persList as $aPersonne ) {
				$page->append ( "listElements", "<li>{$aPersonne->nom} {$aPersonne->prenom}</li>" );
			}
		} else {
			$page->appendBody ( "Personne n'est affilié à cette catégorie." );
		}
	}
	
	if (Roles::isGestionnaireOfCategorie ( $cat )) {
		$page->appendActionButton ( "Modifier", "edit&class=AffiliationCategorie&categorie=" . $cat->getPrimaryKey () );
	}
} else if ($pers != null) {
	$page->asset ( "listTitle", "Catégories auxquelles {$pers->prenom} est affilié(e)" );
	
	$catList = $pers->getCategoriesEffectivesList ();
	
	if (count ( $catList ) > 0) {
		/* @var $aCat Categorie */
		foreach ( $catList as $aCat ) {
			$page->append ( "listElements", "<li>{$aCat->nom}</li>" );
		}
	} else {
		$page->appendBody ( "Aucune catéogorie" );
	}
	
	if (Roles::canAdministratePersonne ()) {
		$page->appendActionButton ( "Modifier", "edit&class=AffiliationCategorie&personne=" . $pers->getPrimaryKey () );
	}
}



