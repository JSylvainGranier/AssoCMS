<?php

if(!array_key_exists("dateEcheance", $ARGS)){
    $ARGS["dateEcheance"] = $ARGS["datePerception"];
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





