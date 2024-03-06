<?php
if (array_key_exists ( "class", $ARGS )) {
	$class = $ARGS ["class"];
	
	if (! class_exists ( $class ))
		throw new Exception ( "Cette classe n'existe pas." );
	
	checkClassForAdminRestiction($class);
	
	$editorFileName = "./includes/actions/show" . $class . ".php";
	
	if (file_exists ( $editorFileName )) {
		include $editorFileName;
	} else {
		include "./includes/actions/showGeneric.php";
	}
} else {
	throw new Exception ( "Aucune classe indiquée !" );
}


