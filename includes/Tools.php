<?php
function secureFormInput($input) {
	return trim ( $input );
}
function protectInputValueApostrophe($inputValue) {
	return str_replace ( "'", "&apos;", $inputValue );
}
function sendSimpleMail($object, $message, $to, $immediately = false) {
	$mail = new Mail ();
	$mail->destinataire = $to;
	$mail->message = $message;
	$mail->objet = $object;
	
	if (! is_null ( $_SESSION ) && array_key_exists ( "userId", $_SESSION )) {
		$personne = new Personne ( thisUserId() );
		$mail->expediteur = $personne->email;
	}
	
	$mail->save ();
	
	/*
	if ($immediately) {
		return $mail->send ();
	}
	*/
}
function isConsistent($str) {
	if (is_null ( $str )) {
		return false;
	}
	
	return strlen ( trim ( $str ) ) > 0;
}
function getSelectHtml($selectName, $optionsValTitle, $selectedOptionValue, $allowNoValue = false) {
	$html = "<select name='{$selectName}'> \n";
	
	if ($allowNoValue) {
		$html .= "<option>-- Aucune valeur --</option>\n";
	}
	
	foreach ( $optionsValTitle as $value => $title ) {
		if ($value == $selectedOptionValue) {
			$selected = "selected='selected'";
		} else {
			$selected = "";
		}
		
		$html .= "<option value='{$value}' {$selected}>{$title}</option>\n";
	}
	
	$html .= "</select>\n";
	
	return $html;
}
class HttpException extends Exception {
	public function __construct($msg = null, $code = null) {
		parent::__construct ( $msg, $code );
	}
}
function startsWith($haystack, $needle) {
	// search backwards starting from haystack length characters from the end
	return $needle === "" || strrpos ( $haystack, $needle, - strlen ( $haystack ) ) !== FALSE;
}
function endsWith($haystack, $needle) {
	// search forward starting from end minus needle length characters
	return $needle === "" || (($temp = strlen ( $haystack ) - strlen ( $needle )) >= 0 && strpos ( $haystack, $needle, $temp ) !== FALSE);
}
function return_bytes($val) {
	$val = trim ( $val );
	$last = strtolower ( $val [strlen ( $val ) - 1] );
	switch ($last) {
		// The 'G' modifier is available since PHP 5.1.0
		case 'g' :
			$val *= 1024;
		case 'm' :
			$val *= 1024;
		case 'k' :
			$val *= 1024;
	}
	
	return $val;
}


/*
 * Vérifie que la classe sur laquelle travaille l'action courante 
 * ne fait pas partie des classes qui sont réservées uniquement 
 * aux administrateurs. 
 * Si c'est le cas et que l'utilisateur courant n'est pas admin, RoleException.
 */
function checkClassForAdminRestiction($class){
	$c = strtolower($class);
	
	$exceptionsAdmin = array("param","mail","erreur");
	$exceptionsGest = array("publipostage","publipostagedestinataire","erreur");
	$exceptionsUser = array("reaction","personne");
	
	if(in_array($c, $exceptionsUser) && !Roles::isMembre()){
		throw new RoleException("Vous n'avez pas accès à ce type d'objet.");
	}
	
	if(in_array($c, $exceptionsGest) && !Roles::isGestionnaireCategorie()){
		throw new RoleException("Vous n'avez pas accès à ce type d'objet.");
	}
	
	if(in_array($c, $exceptionsAdmin) && !Roles::isSuperAdmin()){
		throw new RoleException("Vous n'avez pas accès à ce type d'objet.");
	}
	
}
$GET_ALLOW_EXCEPTION = array ("resumableType","resumableIdentifier", "resumableFilename", "resumableRelativePath", "random");

function secureGet(){
	global $ARGS;
	global $GET_ALLOW_EXCEPTION;
	
	$regex = "/-?[0-9]{1,10}|[A-Z_a-z]{1,30}/";
	
	$HashRegex = "/-?[0-9_abcdef]{1,70}/";
	$hashExceptionKeys = array("reactivateAccount","unsuscribe");
	
	$keys = array_keys($_GET);
	
	for($i = 0; $i < sizeof($keys); $i++){
		$paramValidated = false;
		$key = $keys[$i];
		$value = $_GET[$key];
		
		if(in_array($key, $GET_ALLOW_EXCEPTION)){
			continue;
		}
		
		$safeElt = array();
		
		//Validation de la clef
		preg_match($regex, $key, $safeElt);
		if(array_key_exists(0, $safeElt)){
		
			if ($safeElt[0] == $key){
				$paramValidated = true;
			}
		
		}
		

		if (!is_null($value) && $paramValidated){
				
			$safeElt = array();
				
			
			//Validateur de la valeur
			$regexToUse = "";
			
			if(in_array($key, $hashExceptionKeys)){
				$regexToUse = $HashRegex;
			} else {
				$regexToUse = $regex;
			}
			
			preg_match($regexToUse, $value, $safeElt);
			if(array_key_exists(0, $safeElt)){
					
				if ($safeElt[0] == $value){
					$paramValidated = true;
				} else {
					$paramValidated = false;
					
				}
					
			}
		}
		if(!$paramValidated){
			throw new HttpException ( "Paramètre GET louche : ".$key." = ".$value );
						
		}		
		
	}
	
	
}

function securePost(){
	global $ARGS;
	
	if(count($_POST) > 0){
		if(Roles::isMembre() == false){
			$allowedForLogin = "login|visiblepw|pw";
			$allowedForLoginAndRedirection = "login|visiblepw|pw|requestBeforeLogin";
			$allowedForPostEndOfRegeneration = "reactivateAccount|idPersonne|visiblenpwa|npwa|visiblenpwb|npwb|mailText";
			$allowedForContact = "email|message|g-recaptcha-response";
			if(!array_key_exists_r($allowedForLogin, $_POST) 
			    && !array_key_exists_r($allowedForLoginAndRedirection, $_POST) 
			    && !array_key_exists_r($allowedForPostEndOfRegeneration, $_POST)
			    && !array_key_exists_r($allowedForContact, $_POST)
			    ){
				throw new HttpException ( "Paramètres de login suspicieux : ".print_r($_POST, true) );
			}
		}
	}
}

function array_key_exists_r($keys, $search_r) {
    $keys_r = split('\|',$keys);
    if(count($search_r) == count($keys_r)){
	    foreach($keys_r as $key){
		    if(!array_key_exists($key,$search_r)){
			    return false;
		    } 
	    }
    } else {
    	return false;
    }
    
    return true;
}
