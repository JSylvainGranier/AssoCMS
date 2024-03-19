<?php


// Tous les warnings PHP sont stockés ici, puis mis à la fin de la page s'il en exite.
$PHP_ERRORS = array ();
function errHandler($errno, $errstr, $errfile, $errline) {
	global $PHP_ERRORS;
	$PHP_ERRORS [] = array (
			$errno,
			$errstr,
			$errfile,
			$errline 
	);

	
}
set_error_handler ( "errHandler" );
//Et là, on traite les erreurs fatales de PHP.
function fatalErrHandler()
{
	$error = error_get_last();
	//check if it's a core/fatal error, otherwise it's a normal shutdown
	if ($error !== NULL && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING,E_RECOVERABLE_ERROR))) {
		$html = file_get_contents("includes/html/erreurFatale.html");
		
		$html = str_ireplace("--err--", print_r($error, true), $html);
		
		echo $html;
		print_r($error);
		exit;
		
	}
}
register_shutdown_function("fatalErrHandler");


require_once 'includes/includeAll.php';
require_once 'documents/ipexclusion.php';

header ( 'Content-Type: text/html; charset=utf-8' );

$method = $_SERVER ['REQUEST_METHOD'];




$ACTIONS = array (
		array_merge ( $_GET, $_POST ) 
);

$notAthNeedForActions = array (
		"show",
		"list",
		"home",
		"login",
		"exception",
		"error",
        "cron",
        "contact",
		"install",
		"mailSessionSpool",
		"cron",
		"sitemap",
		"unsuscribe",
        "selfCreateAccountCheckEmail",
        "selfCreateAccountSubmitRequest",
    
);

$maxLoop = 5;

try {
	
	$page = new SmartPage ();

	checkIpDeny();
	
	secureGet();
		
	if (strlen ( session_id () ) < 1) {
		session_start ();
	}
	
	securePost();
	
	if (array_key_exists ( "LSTK", $_COOKIE ) && ! Roles::isMembre ()) {
		$longSession = new Session();
		$longSession = $longSession->findByLongSessionToken($_COOKIE ["LSTK"]);
		
		$personne = null;
		
		if(! is_null ( $longSession )){
			$personne = new Personne($longSession->fkIdPersonne+0);
			$longSession->nbReUse++;
			$longSession->save();
		}

		if (! is_null ( $personne ) ) {
			prepareUserSession ( $personne, $longSession );
			if(!Roles::isInvite ()){
    			$page->appendNotification ( "Bon retour parmi nous, " . $_SESSION ["userName"] . " !".getTrombiMessageFor($personne), 15 );
			}
		} else {
			removeLongSessionCookie ();
		}
	}
	
	do {
		
		$ARGS = array_shift ( $ACTIONS );
				
		if (is_null ( $ARGS )) {
			if ($maxLoop == 5) {
			} else {
				break;
			}
		}
		
		
		$urlKeys = array_keys ( $ARGS );
		
		$isIdentifiedUser = Roles::isMembre () || (Roles::isInvite() && $urlKeys [0] == "saveInscription") ;
		
		if (array_key_exists ( 0, $urlKeys )) {
			// On vérifie que le visiteur est bien connecté en fonction de ce qu'il veut faire.
			
			$needAthentification = ! in_array ( $urlKeys [0], $notAthNeedForActions );
			if (! $isIdentifiedUser && $needAthentification) {
				// Sauvegarde de l'ancienne requête pour la restituer après login.
				$_SESSION ["requestBeforeLogin"] = $ARGS;
				
				// Remplacement de la requête pour aller vers le login.
				$ARGS = array ();
				$ARGS ["login"] = "";
				$ARGS ["phase"] = "notLogged";
				$ACTIONS [] = $ARGS;
			} else {
				
				if (file_exists ( 'includes/actions/' . $urlKeys [0] . '.php' )) {
					include 'includes/actions/' . $urlKeys [0] . '.php';
				} else {
					if (file_exists ( 'includes/actions/' . $ARGS [0] . '.php' )) {
						include 'includes/actions/' . $ARGS [0] . '.php';
					} else {
						throw new Exception ( "L'action '{$urlKeys[0]}' n'existe pas ! " );
					}
				}
			}
		} else {
			include 'includes/actions/home.php';
		}
		
		if (array_key_exists ( "redirectAction", $ARGS ) && ! is_null ( $ARGS ["redirectAction"] ) && sizeof ( $ARGS ["redirectAction"] ) > 0) {
			
			$nextActionName = $ARGS ["redirectAction"];
			
			array_shift ( $ARGS );
			unset ( $ARGS ["redirectAction"] );
			array_unshift ( $ARGS, $nextActionName );
			
			$ACTIONS [] = $ARGS;
		}
		
		$maxLoop --;
		
		if ($maxLoop < 0) {
			throw new Exception ( "Trop de redirection d'action. Problème de boucle infinie ?" );
		}
	} while ( count ( $ACTIONS ) > 0 );
	
	if (! $page->isStandardOututDisabled ()) {
		echo $page->buildPage ();
	} else {
		// C'est l'action appelée qui se débrouille !
	}
} catch ( RoleException $exception ) {
	
	if (! $isIdentifiedUser) {
		$_SESSION ["requestBeforeLogin"] = $ARGS;
		
		// Remplacement de la requête pour aller vers le login.
		$ARGS = array ();
		$ARGS ["login"] = "";
		$ARGS ["phase"] = "notLogged";
		$urlKeys = array_keys ( $ARGS );
		include 'includes/actions/login.php';
	} else {
		include 'includes/actions/exception.php';
	}
	echo $page->buildPage ();
} catch ( Exception $exception ) {
	
	include 'includes/actions/exception.php';
	echo $page->buildPage ( false );
}

