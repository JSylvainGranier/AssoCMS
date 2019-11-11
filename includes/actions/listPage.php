<?php
$page->appendBody ( file_get_contents ( "includes/html/showCategorie.html" ) );
/* @var $instance Page */
/* @var $obj Page */
$instance = new Page ();

/* @var $cat Categorie */
$cat = new Categorie ( $ARGS ["categorie"] );
$page->setCategorie ( $cat );

$page->asset ( "catName", $cat->nom );
$page->asset ( "catId", $cat->getPrimaryKey () );
$page->setTitle ( $cat->nom );

$list = $instance->getFilteredInCategorie ( $cat->getPrimaryKey (), false );

if (count ( $list ) > 0) {
	$tag = "pagesList";
	foreach ( $list as $obj ) {
		include 'includes/actions/showPageInList.php';
	}
}

$page->asset ( "introduction", $cat->textePresentation );

/* @var $evenement Evenement */
$evenement = new Evenement ();
$listEvt = $evenement->getCurrentMonthEvents ( $cat->getPrimaryKey () );

$tag = "evtList";
$curMonth = new MyDateTime ();
$page->asset ( "rdvLeft", $curMonth->formatLocale ( "%B %Y" ) );

if (sizeof ( $listEvt ) > 0) {
	
	foreach ( $listEvt as $evenement ) {
		include 'includes/actions/showEvenementInList.php';
	}
} else {
	$page->append ( $tag, "<li class='listEmptyItem'>Il n'y a aucun rendez-vous prévu pour ce mois-ci. </li>" );
}

// Coordonnées du gestionnaite de la catégorie.
$pers = $cat->getPersonneGestionnaire ();
if (! is_null ( $pers )) {
	$tel1 = ! is_null ( $pers->telFixe ) ? $pers->telFixe : "";
	$tel2 = ! is_null ( $pers->telPortable ) ? strlen ( $tel1 ) > 0 ? ", " . $pers->telPortable : $pers->telPortable : "";
	$page->append ( "gestionnaireCat", "<p class='contactSection'>Cette section est gérée par <a href='index.php?show&class=Personne&id={$pers->getPrimaryKey()}'>{$pers->prenom}</a> ({$tel1}{$tel2})</p>" );
}

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

