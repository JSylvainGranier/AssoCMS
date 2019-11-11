<?php
if (array_key_exists ( "class", $ARGS )) {
	
	$class = $ARGS ["class"];
	
	if (! class_exists ( $class ))
		throw new Exception ( "Cette classe n'existe pas." );
	
	$editorFileName = "./includes/actions/save" . $class . ".php";
	
	if (file_exists ( $editorFileName )) {
		include $editorFileName;
	} else {
		include "./includes/actions/saveGeneric.php";
	}
} else {
	
	throw new Exception ( "Aucune classe précisée pour la sauvegarde générique !" );
}






