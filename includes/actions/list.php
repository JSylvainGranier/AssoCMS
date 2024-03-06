<?php
if (array_key_exists ( "class", $ARGS )) {
	
	$class = $ARGS ["class"];
	
	if (! class_exists ( $class ))
		throw new Exception ( "Cette classe n'existe pas." );

	checkClassForAdminRestiction($class);
	
	$editorFileName = "./includes/actions/list" . $class . ".php";
	
	if (file_exists ( $editorFileName )) {
		include $editorFileName;
	} else {
		include "./includes/actions/listGeneric.php";
	}
}

