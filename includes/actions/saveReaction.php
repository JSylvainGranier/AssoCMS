<?php
$idPage = $ARGS ["page"];

/* @var $object Persistant */
/* @var $evenement Evenement */
/* @var $page Page */
/* @var $foreignObject Persistant */

$pageLiee = new Page ( $idPage + 0 );

$ARGS ["message"] = nl2br ( $ARGS ["message"] );

include 'includes/actions/saveGeneric.php';

if ($pageLiee->isSubClass) {
	$evt = new Evenement ();
	
	$evt = $evt->findByPageId ( $idPage );
	
	$ACTIONS [] = array (
			"show",
			"class" => "Evenement",
			"id" => $evt->getPrimaryKey () 
	);
} else {
	$ACTIONS [] = array (
			"show",
			"class" => "Page",
			"id" => $pageLiee->getPrimaryKey () 
	);
}





