<?php
if (! Roles::isGestionnaireGlobal ()) {
	throw new RoleException ( "Vous n'avez pas le droit d'accéder à la configuration du site." );
}

if (array_key_exists ( "save", $ARGS )) {
	$paramDao = new Param ();
	
	$title = $paramDao->findById ( PKeys::$HOME_TITLE->key );
	if (is_null ( $title )) {
		$title = new Param ();
		$title->pKey = PKeys::$HOME_TITLE->key;
	}
	$title->pValue = stripslashes ( secureFormInput ( $ARGS ["titre"] ) );
	$title->save ();
	
	$accueil = $paramDao->findById ( PKeys::$HOME_TEXT->key );
	if (is_null ( $accueil )) {
		$accueil = new Param ();
		$accueil->pKey = PKeys::$HOME_TEXT->key;
	}
	$accueil->pValue = stripslashes ( secureFormInput ( $ARGS ["accueil"] ) );
	$accueil->save ();
	
	$activity = $paramDao->findById ( PKeys::$HOME_ACTIVITY_NBJOURS->key );
	if (is_null ( $activity )) {
		$activity = new Param ();
		$activity->pKey = PKeys::$HOME_ACTIVITY_NBJOURS->key;
	}
	$activity->pValue = secureFormInput ( $ARGS ["nbJours"] ) + 0;
	$activity->save ();
	
	$headerPub = $paramDao->findById ( PKeys::$PUBLICATION_HEADER->key );
	if (is_null ( $headerPub )) {
		$headerPub = new Param ();
		$headerPub->pKey = PKeys::$PUBLICATION_HEADER->key;
	}
	$headerPub->pValue = stripslashes ( secureFormInput ( $ARGS ["headerPub"] ) );
	$headerPub->save ();
	
	$page->appendNotification ( "Configuration sauvegardée !" );
}

$page->appendBody ( file_get_contents ( "includes/html/configuration.html" ) );

$page->asset ( "titre", protectInputValueApostrophe ( Param::getValue ( PKeys::$HOME_TITLE ) ) );
$page->asset ( "accueil", Param::getValue ( PKeys::$HOME_TEXT ) );
$page->asset ( "nbJours", Param::getValue ( PKeys::$HOME_ACTIVITY_NBJOURS ) );
$page->asset ( "headerPub", Param::getValue ( PKeys::$PUBLICATION_HEADER ) );

