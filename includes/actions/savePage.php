<?php
$id = $ARGS ["id"];

include 'includes/actions/saveGeneric.php';

$ACTIONS [] = array (
		"show",
		"class" => "Page",
		"id" => $ARGS ["id"] 
);



