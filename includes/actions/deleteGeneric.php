<?php


$object->delete ();
$arr = array (
		"list",
		"class" => $class 
);





if ($object instanceof Page) {
	/* @var $oldPage Page */
	$oldPage = $object;
	$arr = array_merge ( $arr, array (
			"categorie" => $oldPage->getCategorieClassement ()->getPrimaryKey () 
	) );
}
		


if(array_key_exists("thenAction", $ARGS)){
	$redirection = array (
		$ARGS["thenAction"],
		"class" => $ARGS["thenClass"],
		$ARGS["thenIdName"] => $ARGS["thenIdValue"]
	);
	$ACTIONS[] = $redirection;
} else {
	$ACTIONS [] = $arr;
}




