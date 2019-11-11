<?php
$page->appendBody ( file_get_contents ( "includes/html/editPublipostage.html" ) );

$publipostage = new Publipostage ();

if (array_key_exists ( "id", $ARGS )) {
	$publipostage = $publipostage->findById ( $ARGS ["id"] );
}

$page->asset ( "id", $publipostage->getPrimaryKey () );
$page->asset ( "objet", protectInputValueApostrophe ( $publipostage->objet ) );
$page->asset ( "message", $publipostage->message );

$wizOptions = "<option value='-1'>-- Toutes --</option>";
$cats = new Categorie ();
$cats = $cats->getAll ();
foreach ( $cats as $aCat ) {
	/* @var $aCat Categorie */
	$wizOptions .= "<option value='{$aCat->getPrimaryKey()}'>{$aCat->nom}</option>";
}

$page->asset ( "wizOptions", $wizOptions );