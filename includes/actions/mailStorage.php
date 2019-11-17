<?php
$page->setStandardOuputDisabled ( true );
$action = "";

if (array_key_exists ( "action", $ARGS )) {
	$action = $ARGS ["action"];
} else {
	throw new Exception ( "Aucune action indiquée !" );
}

$MAX_TENTATIVES = Param::getValue ( PKeys::$MAIL_MAX_TENTATIVES, 3 );

if($action == "list"){
	//Retourne la liste des mails non vérouillés pour l'envoie.
	
	$mail = new Mail ();
	$mailList = $mail->getNextSpoolContent ( 1000, $MAX_TENTATIVES );
	
	
	foreach ( $mailList as $aMail ) {
		$destinataire = $aMail->destinataire;
		
		if (defined ( "MAIL_REDIRECTION_TO" )) {
			$destinataire = MAIL_REDIRECTION_TO;
		}
		
		echo "{$aMail->getPrimaryKey()}|{$aMail->expediteur}|{$destinataire}|{$aMail->objet}\r\n";
	}
	
	
	
} else if($action == "getAndLock"){
	//Retourne les informations pour l'envoi d'un mail, et vérouille ce mail pour qu'un autre processus ne l'envoie pas en prallèle.
	$idMail = $ARGS["idMail"];
	$mail = new Mail ($idMail);
	$mail->nbTentatives = $MAX_TENTATIVES+1;
	$mail->save();
	
	$email = new SmartPage ( "emailBody.html" );
		
	$email->appendBody ( $mail->message );
		
	$email->append ( "style", file_get_contents ( "ressources/template/style.css" ) );
		
	$email->append ( "style", file_get_contents ( "ressources/template/emailStyle.css" ) );
		
	$html = $email->buildPage ( false );

	$emailCorpus .= "Content-type: text/html;charset=utf-8\r\n\r\n";
		
	$emailCorpus .= $html;
		
	
	
	echo $emailCorpus;
	
} else if($action == "confirm"){
	//Confirme l'envoi du mail.
	$idMail = $ARGS["idMail"];
	$mail = new Mail ($idMail);
	$mail->sent = true;
	$mail->save();
} else if($action == "unlock"){
	//Enlève le vérou pour l'envoi d'un mail (probablement que l'envoi du mail n'a pas fonctionné)
	$idMail = $ARGS["idMail"];
	$mail = new Mail ($idMail);
	$mail->nbTentatives = 1;
	$mail->save();
	
}