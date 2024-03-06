<?php
$action = "";

if (array_key_exists ( "action", $ARGS )) {
	$action = $ARGS ["action"];
} else {
	throw new Exception ( "Aucune action indiquée !" );
}

if ($action == "runLocal") {
	$retText = "";
	$inConsoleMode = array_key_exists ( "mode", $ARGS ) && $ARGS ["mode"] == "console";
	
	$SPOOL_SIZE = Param::getValue ( PKeys::$MAIL_SPOOL_SIZE, 0 );
	$MAX_TENTATIVES = Param::getValue ( PKeys::$MAIL_MAX_TENTATIVES, 3 );
	
	if ($SPOOL_SIZE <= 0) {
		$retText .= "MailSpool désactivé.";
	} else {
		$mail = new Mail ();
		$p = new Param();
		$spoolRunningParam = $p->findById(PKeys::$MAIL_SPOOL_RUNNING->key);
		if($spoolRunningParam == null){
			$spoolRunningParam = new Param();
			$spoolRunningParam->pKey = PKeys::$MAIL_SPOOL_RUNNING->key;
			$spoolRunningParam->pValue = false;
		}
		
		if(false == $spoolRunningParam->pValue){
			$spoolRunningParam->pValue = true;
			$spoolRunningParam->save();
			try {
				$mailList = $mail->getNextSpoolContent ( $SPOOL_SIZE, $MAX_TENTATIVES );
				
				foreach ( $mailList as $aMail ) {
					$result = $aMail->send () ? "envoyé" : "ERREUR!";
					$retText .= "<p>Mail n°{$aMail->getPrimaryKey()} : {$result}</p>";
				}
				
				$mail->sendPropositionAlert ();
			} catch (Exception $e){
				
			}
			$spoolRunningParam->pValue = false;
			$spoolRunningParam->save();
		} else {
			$retText = "<p>Merci, mais je tourne déjà ;-)</p>";
		}
		
	}
	
	if ($inConsoleMode) {
		$page->setStandardOuputDisabled ( true );
		echo $retText;
		return;
	} else {
		$page->appendBody ( $retText );
	}
} else if ($action == "purge") {
	$scope = $ARGS ["scope"];
	$actionConfirmed = array_key_exists ( "confirm", $ARGS );
	
	if ($actionConfirmed) {
		
		$mail = new Mail ();
		
		if ($scope == "all") {
			$mail->cleanAll ();
		} else if ($scope == "sent") {
			$mail->cleanSent ();
		} else if ($scope == "error") {
			$mail->cleanError ();
		}
		
		$page->appendNotification ( "Suppression effectuée" );
	} else {
		
		$page->appendBody ( "<p>Confirmez-vous la suppression " );
		if ($scope == "all") {
			$page->appendBody ( "de <b>TOUS</b> les emails du spool ? </p> " );
		} else if ($scope == "sent") {
			$page->appendBody ( "des emails envoyés avec succes ? </p> " );
		} else if ($scope == "error") {
			$page->appendBody ( "de qui ont fait l'objet de plusieurs tentatives d'envoie sans que cela fonctionne ? </p> " );
		}
		$page->appendBody ( "<a href='index.php?mailSpool&action={$action}&scope={$scope}&confirm'>Oui</a>" );
	}
} else {
	throw new Exception ( "L'action {$action} n'est pas programmée !" );
}

$ACTIONS [] = array (
		"list",
		"class" => "mail" 
);

?>