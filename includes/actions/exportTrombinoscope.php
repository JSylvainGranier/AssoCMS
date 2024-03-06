<?php
$pers = new Personne ();
$pers = $pers->getDataFromQuery ( "select idPersonne from personne" );

$personnesIds = array ();

foreach ( $pers as $aRow ) {
	
	$personnesIds [] = $aRow ["idPersonne"];
}

$ARGS ["id"] = $personnesIds;

include 'includes/actions/exportPersonne.php';

?>