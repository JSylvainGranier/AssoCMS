<?php

$idPersonne = thisUserId();
$pers = new Personne($idPersonne+0);

switch ($ARGS["param"]){
	case "cantUploadTrombiFile":
		$pers->cantUploadTrombiFile = new MyDateTime();
		
		$page->appendNotification (file_get_contents("includes/html/ath-trombiCantUploadTrombiFile.html"));
		
		break;
	case "dontWantUseTrombi":
		$pers->dontWantUseTrombi = new MyDateTime();
		$page->appendNotification (file_get_contents("includes/html/ath-trombiDontWantUseTrombi.html"));
		break;
	default :
		throw new Exception ( "Le paramètre ".$ARGS["param"]." n'est pas géré." );
}

$pers->save();

$ACTIONS [] = array ("home");





