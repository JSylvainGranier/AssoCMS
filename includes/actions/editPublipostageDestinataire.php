<?php
if (! array_key_exists ( "id", $ARGS )) {
	throw new Exception ( "Ce publipostage n'existe pas !" );
}

$publipostage = new Publipostage ( $ARGS ["id"] + 0 );

$page->setTitle ( "Destinataires pour le publipostage '{$publipostage->objet}'" );

$listPersonnes = null;

// echo print_r($ARGS);

if (array_key_exists ( "addSection", $ARGS )) {
	if ($ARGS ["section"] > 0) {
		$cat = new Categorie ( $ARGS ["section"] + 0 );
		$listPersonnes = $cat->getAffiliatedPersonnes ();
	} else {
		$pers = new Personne ();
		$listPersonnes = $pers->getAll ();
	}
} else if (array_key_exists ( "addNobody", $ARGS )) {
	$pubDest = new PublipostageDestinataire ();
	$pubDest->publipostage = $publipostage;
	$pubDest->save ();
} else if (array_key_exists ( "addPersonne", $ARGS )) {
	$personnesToAdd = $ARGS ["addPersonne"];
	foreach ( $personnesToAdd as $aPersonneId ) {
		$aPersonne = new Personne ( $aPersonneId + 0 );
		$listPersonnes [] = $aPersonne;
	}
} else if (array_key_exists ( "all", $ARGS )) {
	$pers = new Personne ();
	$listPersonnes = $pers->getAll ();
} else if (array_key_exists ( "delete", $ARGS )) {
	$pubDestListToDelete = $ARGS ["deletePubDest"];
	foreach ( $pubDestListToDelete as $aPubliDestId ) {
		$pubDestToDelete = new PublipostageDestinataire ( $aPubliDestId + 0 );
		$pubDestToDelete->delete ();
	}
}
if ($listPersonnes != null) {
	$pubDestDoa = new PublipostageDestinataire ();
	
	foreach ( $listPersonnes as $aPersonne ) {
		if($aPersonne->allowEmails || $aPersonne->wantPaperRecap){
			$pubDest = new PublipostageDestinataire ();
			$pubDest->publipostage = $publipostage;
			$pubDest->destinataire = $aPersonne;
			$pubDest->save ();
			
		}
	}
	
	$pubDestDoa->cleanDoublons ();
}

$page->appendBody ( file_get_contents ( "includes/html/editPublipostageDestinataire.html" ) );

$page->asset ( "objet", $publipostage->objet );
$page->asset ( "id", $publipostage->getPrimaryKey () );

$sections = new Categorie ();
$sections = $sections->getAll ();

$opt = "<option></option>";
$page->append ( "sectionsOptions", $opt );

foreach ( $sections as $aSection ) {
	/* @var $aSection Categorie */
	if ($aSection->autoAffiliate) {
		continue;
	}
	$opt = "<option value='{$aSection->getPrimaryKey()}'>{$aSection->nom}</option>";
	$page->append ( "sectionsOptions", $opt );
}

$pubDestDao = new PublipostageDestinataire ();
$destinataires = $pubDestDao->getAllForPublipostage ( $publipostage->getPrimaryKey () );

foreach ( $destinataires as $aDestinataire ) {
	/* @var $aDestinataire PublipostageDestinataire */
	if (is_null ( $aDestinataire->getDestinataire () )) {
		$opt = "<option value='{$aDestinataire->getPrimaryKey()}'>Sans destinataire</option>";
	} else {
		$opt = "<option value='{$aDestinataire->getPrimaryKey()}'>{$aDestinataire->getDestinataire()->getNomPrenom()}</option>";
	}
	$page->append ( "destinatiresOptions", $opt );
}

$page->asset ( "nbSelectedPersonnes", count ( $destinataires ) );

$page->appendActionButton ( "Revenir au publipostage", "show&class=Publipostage&id={$publipostage->getPrimaryKey()}" );
