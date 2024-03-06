<?php
$attachmentClass = $ARGS ["attachmentClass"];
$attachmentId = $ARGS ["attachmentId"];

$affectations = $ARGS ["affectation"];
if (is_null ( $affectations )) {
	$affectations = array ();
}

if ($attachmentClass == "Categorie") {
	$cat = new Categorie ( $attachmentId );
	
	$existingAffiliations = $cat->getAffiliationCategorieList ();
	
	// On fait le tris entre ce qui existe déjà et les éléments supprimés.
	foreach ( $existingAffiliations as $aAffiliation ) {
		if (in_array ( $aAffiliation->personne, $affectations )) {
			$idx = array_search ( $aAffiliation->personne, $affectations );
			unset ( $affectations [$idx] );
		} else {
			$aAffiliation->delete ();
		}
	}
	
	// A ce stade, il ne reste que les nouvelles affectations
	
	foreach ( $affectations as $idPersonne ) {
		$affCat = new AffiliationCategorie ();
		
		$affCat->categorie = $cat;
		$affCat->personne = $idPersonne + 0;
		$affCat->save ();
	}
	
	$ACTIONS [] = array (
			"list",
			"class" => "AffiliationCategorie",
			"categorie" => $cat->getPrimaryKey () 
	);
} else if ($attachmentClass == "Personne") {
	$pers = new Personne ( $attachmentId );
	
	$existingAffiliations = $pers->getCategoriesAffiliesList ();
	
	// On fait le tris entre ce qui existe déjà et les éléments supprimés.
	foreach ( $existingAffiliations as $aAffiliation ) {
		if (in_array ( $aAffiliation->categorie, $affectations )) {
			$idx = array_search ( $aAffiliation->categorie, $affectations );
			unset ( $affectations [$idx] );
		} else {
			$aAffiliation->delete ();
		}
	}
	
	// A ce stade, il ne reste que les nouvelles affectations
	
	foreach ( $affectations as $idCategorie ) {
		$affCat = new AffiliationCategorie ();
		
		$affCat->categorie = $idCategorie + 0;
		$affCat->personne = $pers;
		$affCat->save ();
	}
	
	$ACTIONS [] = array (
			"list",
			"class" => "AffiliationCategorie",
			"personne" => $pers->getPrimaryKey () 
	);
}

$page->appendNotification ( "Affectations sauvegardées." );
