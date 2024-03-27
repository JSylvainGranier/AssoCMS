<?php
$page->appendBody ( file_get_contents ( "includes/html/editEvenement.html" ) );

$evt = new Evenement ();
$pEvt = new Page ();
$pers = new Personne ();
$cat = new Categorie ();

/* @var $evt Evenement */
/* @var $cat Categorie */

if (array_key_exists ( "id", $ARGS )) {
	$evt = $evt->findById ( $ARGS ["id"] );
	$pEvt = $evt->getPage ();
	$cat = $pEvt->getCategorieClassement ();
	$page->asset ( "id", $evt->getPrimaryKey () );
	
	$page->setTitle ( "Modification d'un rendez-vous" );
	$page->asset ( "titrePage", "Modification d'un rendez-vous" );
} else {
	$page->setTitle ( "Création d'un nouveau rendez-vous" );
	$page->asset ( "titrePage", "Création d'un nouveau rendez-vous" );
	
	if(array_key_exists ( "fkCategorie", $ARGS )){
		$cat = $cat->findById ( $ARGS ["fkCategorie"] );
	} else {
		$cat = $cat->findById ( 1 );
	}
}

/*
'$categorie$' <input type='hidden'
			name="categorieClassement" value="$idCategorie$">
			$page->setCategorie ( $cat );
*/

$catList = array ();
$catId = 1;
foreach ( $cat->getAll () as $aCat ) {
	$catList [$aCat->getPrimaryKey ()] = $aCat->nom;
}

if (is_null ( $pEvt->getCategorieClassement () )) {
	$catId = $cat->getPrimaryKey ();
} else {
	$catId = $pEvt->getCategorieClassement ()->getPrimaryKey ();
}

$page->asset ( "categorieSelect", getSelectHtml ( "categorieClassement", $catList, $catId, true ) );

$now = new MyDateTime();

$page->asset ( "titre", protectInputValueApostrophe ( $pEvt->titre ) );
$page->asset ( "emplacement", protectInputValueApostrophe ( $evt->emplacement ) );
if (! is_null ( $evt->dateDebut )) {
	$page->asset ( "dateDebut", $evt->dateDebut->format ( "d/m/Y H:i" ) );
} else {
	$page->asset ( "dateDebut", $now->format ( "d/m/Y 00:00" ) );
}
if (! is_null ( $evt->dateFin )) {
	$page->asset ( "dateFin", $evt->dateFin->format ( "d/m/Y H:i" ) );
} else {
	$page->asset ( "dateFin", $now->format ( "d/m/Y 00:00" ) );
}

$page->asset ( "categorie", $cat->nom );
$page->asset ( "idCategorie", $cat->getPrimaryKey () );
$page->asset ( "intro", $pEvt->introduction );
$page->asset ( "suite", $pEvt->suite );

if ($pEvt->allowReactions) {
	$page->asset ( "allowReactionsTrue", "checked='checked'" );
} else {
	$page->asset ( "allowReactionsFalse", "checked='checked'" );
}

if ($pEvt->allowMemberAttachments) {
	$page->asset ( "allowMemberAttachmentsTrue", "checked='checked'" );
} else {
	$page->asset ( "allowMemberAttachmentsFalse", "checked='checked'" );
}

$orgList = array ();
foreach ( $pers->getAll () as $aPers ) {
	$orgList [$aPers->getPrimaryKey ()] = $aPers->nom . " " . $aPers->prenom;
}

if (is_null ( $evt->getOrganisateur1 () )) {
	$org1Id = - 1;
} else {
	$org1Id = $evt->getOrganisateur1 ()->getPrimaryKey ();
}

$page->asset ( "orangisateur1Select", getSelectHtml ( "organisateur1", $orgList, $org1Id, true ) );

if (is_null ( $evt->getOrganisateur2 () )) {
	$org2Id = - 2;
} else {
	$org2Id = $evt->getOrganisateur2 ()->getPrimaryKey ();
}

$page->asset ( "orangisateur2Select", getSelectHtml ( "organisateur2", $orgList, $org2Id, true ) );

if (Roles::canPublishPage ()) {
	$page->asset ( "sendButtonLabel", "Enregistrer" );
	
	if (is_null ( $pEvt->etat )) {
		$curState = PageEtat::$BROUILLON;
	} else {
		$curState = $pEvt->etat;
	}
	
	$options = array ();
	// $options["-15"] = "Supprimé";
	
	if ($pEvt->etat == PageEtat::$PROPOSE) {
		$options ["-10"] = "Reffuser la proposition";
	}
	
	$options ["0"] = "Brouillon";
	
	if (! $cat->autoAffiliate) {
		$options ["15"] = "Accès aux membres de '{$cat->nom}'";
	}
	
	$options ["25"] = "Accès aux membres quand ils sont identifiés";
	$options ["50"] = "Accès public";
	
	$pubState = getSelectHtml ( "etat", $options, $curState );
	
	$page->asset ( "publicationState", $pubState );
} else {
	
	if ($cat->getPersonneGestionnaire () != null) {
		$pers = $cat->getPersonneGestionnaire ();
		$gest = " ( <a href='index.php?show&class=Personne&id={$pers->getPrimaryKey()}' target='_blank'>{$per->prenom}</a> )";
	} else {
		$gest = "";
	}
	
	$proposeState = PageEtat::$PROPOSE;
	$hint = "Vous vous apprêtez à proposer un rendez-vous. Après validation
	par le gestionnaire de la catégorie{$gest}, votre proposition sera visible 
	par tout le monde. <input type='hidden' name='etat' value='{$proposeState}' />";
	
	$page->asset ( "publicationState", $hint );
	
	$page->asset ( "sendButtonLabel", "Enregistrer et proposer mes modifications." );
}

$ACTIONS [] = array (
		"edit",
		"class" => "Attachment",
		"id" => $pEvt->getPrimaryKey () 
);