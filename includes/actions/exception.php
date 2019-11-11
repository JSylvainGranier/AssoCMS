<?php
$page->reset ();

$page->asset ( "body", file_get_contents ( "includes/html/exception.html" ) );

$session = print_r ( $_SESSION, true );
$argsStr = print_r ( $ARGS, true );
$actionsStr = print_r ( $ACTIONS, true );

if (isset ( $maxLoop )) {
	$loop = "Itération dans l'appel des actions : " . $maxLoop;
} else {
	$loop = "";
}

/* @var $exception Exception */

if (! isset ( $exception )) {
	$exception = new Exception ( "L'exception... c'est qu'il n'y a pas d'exception !" );
}

if(get_class ( $exception ) == "NoExistOnDbException"){
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); 
}

$page->asset ( "LOOP", $loop );
$page->asset ( "REFERER", $_SERVER ['HTTP_REFERER'] );
$page->asset ( "ARGS", $argsStr );
$page->asset ( "ACTIONS", $actionsStr );
$page->asset ( "SESSION", $session );
$page->asset ( "STACK", $exception->getTraceAsString () );
$page->asset ( "erreurSimple", $exception->getMessage () );
$page->setTitle ( "Erreur de navigation" );

$erreur = new Erreur();
$erreur->referer = $_SERVER['HTTP_REFERER'];
$erreur->args = $argsStr;
$erreur->actions = $actionsStr;
$erreur->session = $session;
$erreur->userAgent = $_SERVER ['HTTP_USER_AGENT'];
$erreur->ip = $_SERVER ['REMOTE_ADDR'];
$erreur->forwardedFor = $_SERVER ['HTTP_X_FORWARDED_FOR'];
$erreur->exceptionMessage = get_class($exception)." : ".$exception->getMessage ();
$erreur->exceptionStack = "<pre>".$exception->getTraceAsString ()."</pre>";

try {
	$erreur->save();
	
	$errCode = $ARGS ["error"];
	
	if($errCode != 404){
		$erreur->scanAndLock();
	}
	
	if (defined ( "EMAIL_ON_ERROR" ) && get_class ( $exception ) != "HttpException" && get_class ( $exception ) != "BlackListException") {
		$erreur->send();
	}
	
	
} catch (Exception $e){
	//Une exception sur l'exception... c'est con !
}

?>