<?php
if (! array_key_exists ( "class", $ARGS )) {
	throw new Exception ( "Aucune classe spécifiée !" );
}

if (! array_key_exists ( "id", $ARGS )) {
	throw new Exception ( "Aucun objet spécifié !" );
}

$class = $ARGS ["class"];

$id = $ARGS ["id"];

/* @var $object Persistant */
/* @var $foreignObject Persistant */

@$object = new $class ( $id );

if (! is_null ( $object )) {

	if (! array_key_exists ( "confirm", $ARGS )) {
		
		$deletorFileName = "./includes/actions/deleteConfirm" . $class . ".php";
		
		if (file_exists ( $deletorFileName )) {
			include $deletorFileName;
		} else {
			include "./includes/actions/deleteConfirmGeneric.php";
		}
		
	} else {
		$deletorFileName = "./includes/actions/delete" . $class . ".php";
		
		if (file_exists ( $deletorFileName )) {
			include $deletorFileName;
		} else {
			include "./includes/actions/deleteGeneric.php";
		}
	}
}

