<?php 

class BlackListException extends Exception{
	
}

function checkIpDeny(){
	global $IP_DENY;
	$remoteIp = $_SERVER['REMOTE_ADDR'];
	$fowardedFor = null;
	if(array_key_exists("HTTP_X_FORWARDED_FOR", $_SERVER)){
		$fowardedFor = $_SERVER['REMOTE_ADDR'];
	}
	
	if (in_array($remoteIp, $IP_DENY) || in_array($fowardedFor, $IP_DENY) ){
			$msg = "<b>Un certain nombre d'erreur fait que l'accès vous est reffusé. <br />Contactez l'administrateur de ce site.</b>";
			throw new BlackListException($msg);
	}
}

function blackListIp($newIp){
	
	global $IP_DENY;
	
	$exceptions = array(
			"66.249.66.172" //Google Bot.
			
	);
	
	if(in_array($newIp, $exceptions)){
		return;
	}
	
	if(in_array($newIp, $IP_DENY)){
		return;
	}
	
	$IP_DENY[] = $newIp;
	
	$strNewFile = '<?php '."\n";
	$strNewFile .= '$IP_DENY = array ( '."\n";
	
	
	
	
	for($i = 0; $i<count($IP_DENY); $i++){
		$strNewFile .= "		'".$IP_DENY[$i]."', \n";
	}
	
	$strNewFile .= ' ); '."\n";
	$strNewFile .= '?>';
	
	
	file_put_contents("documents/ipexclusion.php", $strNewFile);
	
	$msg = "<p>Cette adresse ip vient d'être rajoutée à la blacklist : {$newIp} </p><ul> ";
	
	$error = new Erreur();
	$listOfErrors = $error->getAllErrorsFromIp($newIp);
	
	foreach ($listOfErrors as $anError){
		$msg .= "<li>Le {$anError->lastUpdateOn->format("d/m/Y à H:i:s")} : {$anError->exceptionMessage} <br/>";
		$msg .= "<pre>{$anError->exceptionStack}</pre>";
		$msg .= "</li>";
	}
	
	$msg .= "</ul>";
	
	sendSimpleMail ( "[" . SITE_TITLE . "] Ajout à la black list " . SITE_ROOT, $msg, EMAIL_ON_ERROR, false );
	
	
}

?>