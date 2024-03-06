<?php
if (array_key_exists ( "class", $ARGS )) {
	$class = $ARGS ["class"];
	
	$editorFileName = "./includes/actions/export" . $class . ".php";
	
	if (file_exists ( $editorFileName )) {
		include $editorFileName;
	} else {
		throw new Exception ( "Il n'existe pas d'export pour '{$class}'." );
	}
}

?>