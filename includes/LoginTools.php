<?php
function sendReactivationProcedure($email, $genToken) {
	$message = "<p>Bonjour,</p>";
	
	$message .= "<p>Vous recevez ce message car une demande de génération d’un nouveau mot de passe a été faite depuis le site " . SITE_TITLE . ".</p>";
	
	$message .= "<p><a href='" . SITE_ROOT . "index.php?login&phase=hasRegenerationTk&reactivateAccount=$genToken'>Cliquez ici</a> pour accéder à une page qui vous permettra de saisir un nouveau mot de passe.</p>";
	
	$message .= "<p>Si le lien ne fonctionne pas, copiez l’adresse qui suit, puis collez-la dans la barre d’adresse de votre navigateur internet :</p>";
	
	$message .= "<p>" . SITE_ROOT . "index.php?login&phase=hasRegenerationTk&reactivateAccount=$genToken </p>";
	
	$message .= "<p>Si vous n’êtes pas à l’origine de cette demande, merci de l’indiquer en répondant à ce message.</p>";
	
	$message .= "<p>À très bientôt sur " . SITE_TITLE . ".</p>";
	
	$return = sendSimpleMail ( "Réactivation de votre Espace Membre " . SITE_TITLE, $message, $email, true );
}

/* @var $personne Personne */
function prepareUserSession($personne, $session = null) {
	$_SESSION ["userLogin"] = $personne->email;
	$_SESSION ["userName"] = $personne->prenom;
	$_SESSION ["userId"] = $personne->idPersonne;
	$_SESSION ["userRoles"] = $personne->getRolesArray ();
	
	$categList = $personne->getCategoriesEffectivesList ();
	$categIdList = array ();
	
	foreach ( $categList as $aCatObject ) {
		$categIdList [] = $aCatObject->getPrimaryKey ();
	}
	
	$_SESSION ["userCategories"] = $categIdList;
	
	
	if(is_null($session)){
		$session = new Session();
		$session->fkIdPersonne = $personne->idPersonne;
		$session->longSessionToken = md5 ( $personne->email . date ( "U" ) );
		$session->save();
	}
	
		
	setcookie ( 'LSTK', $session->longSessionToken, time () + 60 * 60 * 24 * 365, SITE_PATH, SITE_DOMAIN );
}
function removeLongSessionCookie() {
	
	$session = new Session();
	$session = $session->findByLongSessionToken($_COOKIE['LSTK']);
	if(! is_null($session)){
		$session->delete();
	}
	
	setcookie ( 'LSTK', null, time () - 60 * 60 * 24 * 5, SITE_PATH, SITE_DOMAIN );
}
function destroyUserSession() {
	$indexUser = array_search ( "USER", $_SESSION );
	unset ( $_SESSION [$indexUser] );
	unset ( $_SESSION ["userLogin"] );
	unset ( $_SESSION ["userName"] );
	unset ( $_SESSION ["userId"] );
	unset ( $_SESSION ["userRoles"] );
	
	removeLongSessionCookie ();
}

/**
 * Si la session de l'utilisateur contient une requête mise en attente le temps du login,
 * restaure cette action.
 */
function restaureActionBeforeLogin() {
	global $ACTIONS;
	if (array_key_exists ( "requestBeforeLogin", $_SESSION )) {
		$ACTIONS [] = $_SESSION ["requestBeforeLogin"];
		unset ( $_SESSION ["requestBeforeLogin"] );
	} else {
		$ACTIONS [] = array (
				"home" 
		);
	}
}
function getUserId() {
	if (session_id () == '') {
		return null;
	}
	
	if (array_key_exists ( "userId", $_SESSION )) {
		return $_SESSION ["userId"];
	}
	
	return null;
}

/**
 * Si l'éta de publication passé en paramètre
 * ne permet pas au visiteur de le consulter, retourne une exception.
 */
function checkEtat($etat) {
	if ($etat < PageEtat::$ACCESS_PUBLIC && ! Roles::isMembre ()) {
		throw new RoleException ( "Vous n'avez pas le droit d'accéder à cette page / cet évèvement." );
	}
}

function getTrombiMessageFor(Personne $personne){
	$trombiInvit = "";
	$alertDesactived = (!is_null($personne->dontWantUseTrombi)) || (!is_null($personne->cantUploadTrombiFile));
	if (
			(is_null ( $personne->trombiFile ) || strlen ( $personne->trombiFile ) == 0) //Actuellement pas d'image
			&&
			!$alertDesactived
		) {
		$trombiInvit = file_get_contents("includes/html/ath-trombiInvitation.html");
	}

	return $trombiInvit;
}

function thisUserId(){
	if(array_key_exists("userId", $_SESSION)){
		return $_SESSION ["userId"];
	} else {
		return -999999;
	}
}