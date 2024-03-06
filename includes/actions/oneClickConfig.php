<?php
if (array_key_exists ( "class", $ARGS )) {
	
	$class = $ARGS ["class"];
	
	if (! class_exists ( $class ))
		throw new Exception ( "Cette classe n'existe pas." );
	
	$editorFileName = "./includes/actions/oneClickConfig" . $class . ".php";
	
	if(!array_key_exists("param", $ARGS)){
		throw new Exception ( "OneClickConfig doit être appelé avec un paramètre param, clé du paramètre à modifier." );
	}
	
	if (file_exists ( $editorFileName )) {
		include $editorFileName;
	} else {
		throw new Exception ( "OneClickConfig n'a pas de fonctionnement générique, et le fichier d'implémentation pour la classe {$class} n'existe pas." );
	}
} else {
	
	throw new Exception ( "Aucune classe précisée." );
}






