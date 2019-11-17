<?php
$page->setStandardOuputDisabled ( true );
$action = "";

if (array_key_exists ( "action", $ARGS )) {
	$action = $ARGS ["action"];
} else {
	throw new Exception ( "Aucune action indiquée !" );
}

if($action == "list"){
	//Retourne la liste des mails non vérouillés pour l'envoie.
	
	$mail = new Mail ();
	$mailList = $mail->getNextSpoolContent ( 1000, $MAX_TENTATIVES );
	
	foreach ( $mailList as $aMail ) {
		$result = $aMail->send () ? "envoyé" : "ERREUR!";
		$retText .= "<p>Mail n°{$aMail->getPrimaryKey()} : {$result}</p>";
	}
	
	
	
} else if($action == "getAndLock"){
	//Retourne les informations pour l'envoi d'un mail, et vérouille ce mail pour qu'un autre processus ne l'envoie pas en prallèle.
	$idMail = $ARGS["idMail"];
} else if($action == "confirm"){
	//Confirme l'envoi du mail.
	$idMail = $ARGS["idMail"];
} else if($action == "unlock"){
	//Enlève le vérou pour l'envoi d'un mail (probablement que l'envoi du mail n'a pas fonctionné)
	$idMail = $ARGS["idMail"];
	
}