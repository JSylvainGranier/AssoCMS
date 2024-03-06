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
		
$ACTIONS [] = $arr;




