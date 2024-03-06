<?php
$id = $ARGS ["id"];

if(strlen($ARGS["objet"]) == 0){
	$now = new MyDateTime();
	$ARGS["objet"] = "Message du ".$now->format('d/m/Y');
}

include 'includes/actions/saveGeneric.php';

$ACTIONS [] = array (
		"show",
		"class" => "Publipostage",
		"id" => $ARGS ["id"] 
);



