<?php
if (array_key_exists ( "class", $ARGS )) {
	$class = $ARGS ["class"];
	
	if (! class_exists ( $class ))
		throw new Exception ( "Cette classe n'existe pas." );
	
	$editorFileName = "./includes/actions/edit" . $class . ".php";
	
	$page->append ( "linkZone", '<link href="' . SITE_ROOT . 'ressources/pikaday.css" rel="stylesheet">' );
	$page->append ( "linkZone", '<script src="' . SITE_ROOT . 'ressources/moment-with-locales.js"></script>' );
	$page->append ( "linkZone", '<script src="' . SITE_ROOT . 'ressources/pikaday.js"></script>' );
	
	if (file_exists ( $editorFileName )) {
		include $editorFileName;
	} else {
		include "./includes/actions/editGeneric.php";
	}
}


