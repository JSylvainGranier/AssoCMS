<?php
$publipostage = new Publipostage ( $ARGS ["id"] + 0 );
$pubDest = new PublipostageDestinataire ();
$pubDest = $pubDest->getAllForPublipostage ( $publipostage->getPrimaryKey () );

$personnesIds = array ();

foreach ( $pubDest as $aDestinataire ) {
	/* @var $aDestinataire PublipostageDestinataire */
	
	if ($aDestinataire->getDestinataire () != null)
		$personnesIds [] = $aDestinataire->getDestinataire ()->getPrimaryKey ();
}

$ARGS ["id"] = $personnesIds;
$ARGS ["title"] = "Destinataires publipostage " . $publipostage->objet;

include 'includes/actions/exportPersonne.php';

?>