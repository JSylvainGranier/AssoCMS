<?php

if(!array_key_exists("dateEcheance", $ARGS)){
    $ARGS["dateEcheance"] = $ARGS["datePerception"];
}


$idRemiseBq = $ARGS["remisebq"];
$rbq = new RemiseEnBanque();
if($idRemiseBq < 0){
	$rbq->depositaire = new Personne(thisUserId());
	$now = new MyDateTime();
	$lib = "Remise en banque ouverte le ".$now->format("d/m/Y")." par ".$rbq->depositaire->prenom;
	$rbq->libelle = $lib;
	$rbq->save();
	$idRemiseBq = $rbq->idRemiseEnBanque;
	$ARGS["remisebq"] = $idRemiseBq;
} else if (idRemiseBq == 0){
	//Pas de remise en banque

} else {
	$ARGS["remisebq"] = $idRemiseBq;
}

try {
	include 'includes/actions/saveGeneric.php';
	
	if(array_key_exists("thenAction", $ARGS)){
	    $redirection = array (
	        $ARGS["thenAction"],
	        "class" => $ARGS["thenClass"],
	        $ARGS["thenIdName"] => $ARGS["thenIdValue"]
	    );
	    $ACTIONS[] = $redirection;
	}
	
} catch (Exception $e){
    $page->appendNotification ( "Erreur lors de l'enregistrement : ".$e->getMessage() );
       
}





