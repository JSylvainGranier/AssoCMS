<?php

$email = $ARGS["email"];
$stopSave = false;

if(strlen($email) > 0){
	if (! @eregi ( "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $ARGS ["email"] )) {
		
		$message = "Vérifiez le format de ce que vous avez écrit dans le champ 'Adresse eMail'. <br /> Car '{$ARGS["email"]}' ne ressemble pas à une adresse email normale.";

		$page->appendNotification ( $message );
		$stopSave = true;
		
	}
}

if(!$stopSave){
	include 'includes/actions/saveGeneric.php';
} else {
	$page->appendNotification ( "Les changements n'ont pas été enregistrés à cause du problème de format de l'adresse email." );
	
	unset($ARGS["redirectAction"]);
	
	if(array_key_exists("idPersonne", $ARGS)){
		$redirection = array (
					"edit",
					"class" => "Personne",
					"idPersonne" => $ARGS ["idPersonne"]
				);
	} else {
		$redirection = array (
				"edit",
				"class" => "Personne"
		);
	}
	
	$ACTIONS [] = $redirection;
	
}





