<?php
$id = $ARGS ["id"];

if(strlen($ARGS["objet"]) == 0){
	$ARGS["objet"] = "!! Vous avez oublié l'objet du message !!";
}

include 'includes/actions/saveGeneric.php';

$ACTIONS [] = array (
		"show",
		"class" => "Publipostage",
		"id" => $ARGS ["id"] 
);



