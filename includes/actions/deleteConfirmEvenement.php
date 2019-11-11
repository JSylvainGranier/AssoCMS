<?php


$page->appendBody ( file_get_contents ( "includes/html/deleteConfirmationEvenement.html" ) );
		
$page->asset ( "class", $class );
$page->asset ( "id", $id );

$page->asset ( "objectTitle", $class . " n°" . $object->getPrimaryKey () );
$page->asset ( "objectToString", $object->getShortToString () );

$evenement = $object;
$tag = "evtDescription";

include 'includes/actions/showEvenementInList.php';

if($evenement->getPage()->etat >= PageEtat::$ACCESS_CATEGORIE){
	
	$now = time(); // or your date as well
	$your_date = $evenement->dateDebut->date;
	$datediff = $now - $your_date;
	$datediff = floor($datediff / (60 * 60 * 24));
	
	if ($datediff < 1 && $datediff > -80){
		$datediff = abs($datediff);
		
		if($datediff == 0){
			$msg = "Ce rendez-vous débute aujourd'hui.";
		} else {
			$msg = "Ce rendez-vous début dans $datediff jours.";
		}
		
		$msg = "<b>Attention !</b> <br />".$msg." <br /> Les membres de l'association seront certainement perturbées de voir ce RDV disparaître sans explication. <br /> ";
		$msg .= "Il se peut même qu'ils ne réalisent pas la suppression du RDV. <br />Vous devriez donc envisager d'utiliser la fonction d'annulation.";
		$msg .= "<br/>Pour se faire, revenez à la page précédente, et utilisez le bouton 'Annuler le RDV'.";
		
		$msg = "<p style='border: solid 2px red; padding : 5px;'>$msg</p>";
		
		$page->asset("warning", $msg);
	}
	
	
	
	
}




