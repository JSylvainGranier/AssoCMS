<?php
$page->appendBody ( file_get_contents ( "includes/html/listRemiseEnBanque.html" ) );

$page->setTitle ( "Remises en banque");

$rbqDao = new RemiseEnBanque();

$list = $rbqDao->getAllActivesForActiveUser();

if (count ( $list ) > 0) {
	$tag = "remisesActives";
	foreach ( $list as $rbq ) {
		include 'includes/actions/showRemiseEnBanqueInList.php';
	}
}

$list = $rbqDao->getLastInactivated();

if (count ( $list ) > 0) {
	$tag = "remisesInactives";
	foreach ( $list as $rbq ) {
		include 'includes/actions/showRemiseEnBanqueInList.php';
	}
}


/*
if (Roles::isGestionnaireGlobal () || Roles::isGestionnaireOfCategorie ( $cat )) {
	$page->appendActionButton ( "Ajouter une page", "edit&class=Page&fkCategorie=" . $cat->getPrimaryKey () );
	$page->appendActionButton ( "Ajouter un rendez-vous", "edit&class=Evenement&fkCategorie=" . $cat->getPrimaryKey () );
	$page->appendActionButton ( "Modifier la section", "edit&class=Categorie&id=" . $cat->getPrimaryKey () );
	$page->appendActionButton ( "Affectations à '{$cat->nom}'", "list&class=AffiliationCategorie&categorie=" . $cat->getPrimaryKey () );
} else if (Roles::canAdministratePersonne ()) {
	$page->appendActionButton ( "Affectations à '{$cat->nom}'", "list&class=AffiliationCategorie&categorie=" . $cat->getPrimaryKey () );
} else if (Roles::isMembre ()) {
	$page->appendActionButton ( "Proposer une page dans '{$cat->nom}'", "edit&class=Page&fkCategorie=" . $cat->getPrimaryKey (), false, false );
	$page->appendActionButton ( "Proposer un rendez-vous dans '{$cat->nom}'", "edit&class=Evenement&fkCategorie=" . $cat->getPrimaryKey (), false, false );
}
*/

