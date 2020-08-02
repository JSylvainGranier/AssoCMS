<?php

/* @var $personne Personne */

// http://en.wikibooks.org/wiki/PHP_Programming/User_login_systems
$page->setTitle ( "Connexion Espace Membre" );

if (! array_key_exists ( "phase", $ARGS )) {
	throw new Exception ( "Erreur dans le processus de connexion : il manque la phase." );
}

$message = "";
$personne = new Personne ();

$stopScript = false;

// Dans certains cas, on vérifie dès le départ si l'email est valide;
switch ($ARGS ["phase"]) {
	
	case "loggingIn" :
	case "askingRegeneration" :
		if (! @eregi ( "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $ARGS ["login"] )) {
			// Pour laisser passer l'usurpation d'identité
			if (substr_count ( $ARGS ["login"], "@" ) === 3) {
				$stopScript = false;
			} else {
				$message = "Vérifiez le format de ce que vous avez écrit dans le champ 'Adresse eMail'. <br /> Car '{$ARGS["login"]}' ne ressemble pas à une adresse email.";
				
				$page->appendBody ( file_get_contents ( "includes/html/ath-notLogged.html" ) );
				$page->asset ( "login", $ARGS ["login"] );
				$page->appendNotification ( $message );
				$stopScript = true;
				sleep(5);
			}
		}
		
		break;
}

if (! $stopScript) {
	
	if (array_key_exists ( "requestBeforeLogin", $ARGS )) {
		
		$arr = array ();
		parse_str ( urldecode ( $ARGS ["requestBeforeLogin"] ), $arr );
		$_SESSION ["requestBeforeLogin"] = $arr;
	}
	
	switch ($ARGS ["phase"]) {
		
		case "notlogged" :
		case "notLogged" :
			
			$page->appendBody ( file_get_contents ( "includes/html/ath-notLogged.html" ) );
			
			break;
		
		case "loggingIn" :
			
			$personne = $personne->matchLogin ( $ARGS ["login"], $ARGS ["pw"] );
			
			if (! is_null ( $personne )) {
			    
			    if($personne->allowedToConnect){
			        prepareUserSession ( $personne );
			        
			        // Maintenant que l'utilisateur est loggué, s'il essayais de faire quelque chose avant, on le fait maintenant.
			        restaureActionBeforeLogin ();
			        
			        $page->appendNotification ( "Bonjour " . $_SESSION ["userName"] . " !" . getTrombiMessageFor($personne), 15 );   
			    } else {
			        $page->appendBody ( file_get_contents ( "includes/html/ath-notLogged.html" ) );
			        $page->asset ( "login", $ARGS ["login"] );
			        $page->appendNotification ( "Vos identifiants sont corrects, mais votre compte n'est pas encore activé. <br />Votre compte sera actif dès que votre inscription sera validée." );
			    }
				
				
			} else {
				
				$page->appendBody ( file_get_contents ( "includes/html/ath-notLogged.html" ) );
				$page->asset ( "login", $ARGS ["login"] );
				$page->appendNotification ( "Erreur d'adresse email et/ou de mot de passe" );
				sleep(5);
			}
			
			break;
		
		case "changePassword" :
			
			if (! Roles::isMembre ()) {
				throw new Exception ( "Impossible de changer un mot de passe sans s'être connecté au préalable." );
			}
			
			if (array_key_exists ( "idPersonne", $ARGS )) {
				if (! Roles::canAdministratePersonne () && getUserId () != $ARGS ["idPersonne"]) {
					throw new Exception ( "Vous n'avez pas le droit de changer le mot de passe d'autres personnes." );
				}
				
				$personne = $personne->findById ( $ARGS ["idPersonne"] );
			} else {
				
				$personne = $personne->findById ( thisUserId() );
			}
			
			if (! is_null ( $personne ) && (array_key_exists ( "npwa", $ARGS ) || array_key_exists ( "npwb", $ARGS ))) {
				
				if (! array_key_exists ( "npwa", $ARGS ) || ! array_key_exists ( "npwb", $ARGS ) || $ARGS ["npwa"] != $ARGS ["npwb"]) {
					
					$message = "Le nouveau mot de passe et la confirmation du nouveau mot de passe ne correspondent pas.<br /> Réessayez.";
					
					$page->appendBody ( file_get_contents ( "includes/html/ath-changePassword.html" ) );
					$page->asset ( "idPersonne", $personne->getPrimaryKey () );
					$page->asset ( "formPhase", "changePassword" );
					$page->appendNotification ( $message );
					
					$page->asset ( "personnePrenom", $personne->prenom );
					
					if (Roles::canAdministratePersonne () && $personne->getPrimaryKey () != thisUserId()) {
						$page->asset ( "sendMailNotificationDisplay", "block" );
					} else {
						$page->asset ( "sendMailNotificationDisplay", "none" );
					}
				} else {
					$personne->setPassword ( $ARGS ["npwa"] );
					
					$personne->generationToken = null;
					$personne->save ();
					
					if (array_key_exists ( "sendEmail", $ARGS ) && $ARGS ["sendEmail"] == "1") {
						$msg = $ARGS ["mailText"];
						$msg = nl2br($msg);
						$return = sendSimpleMail ( "Nouveau mot de passe sur " . SITE_TITLE, $msg, $personne->email, true );
						
						$page->appendNotification ( "Le mot de passe est changé, et un email va être envoyé à {$personne->prenom} avec son nouveau mot de passe. " );
					} else {
						
						$page->appendNotification ( "Le mot de passe est changé !" );
					}
					
					$ACTIONS [] = array (
							"show",
							"class" => "Personne",
							"id" => $personne->getPrimaryKey () 
					);
				}
			} else {
				
				$page->appendBody ( file_get_contents ( "includes/html/ath-changePassword.html" ) );
				$page->asset ( "idPersonne", $personne->getPrimaryKey () );
				$page->asset ( "formPhase", "changePassword" );
				$page->asset ( "personnePrenom", $personne->prenom );
				
				if (Roles::canAdministratePersonne () && $personne->getPrimaryKey () != thisUserId()) {
					$page->asset ( "sendMailNotificationDisplay", "block" );
				} else {
					$page->asset ( "sendMailNotificationDisplay", "none" );
				}
			}
			
			break;
		
		case "loggingOff" :
			
			$page->appendNotification ( "À bientôt {$_SESSION["userName"]} !", 15 );
			
			destroyUserSession ();
			
			$ARGS ["redirectAction"] = "home";
			
			break;
		
		case "askingRegeneration" :
			// La personne à cliqué sur le bouton "Réccupérer mon espace membre" dans le form de connexion
			
			$personne = $personne->findByEmail ( $ARGS ["login"] );
			
			// Une correspondance est trouvée :
			if (! is_null ( $personne )) {

			    if($personne->allowedToConnect){
			        // Génération d'un token à envoyer par email
			        $generationTk = "Oups !";
			        if(is_null($personne->generationToken)){
			            $generationTk = $personne->clearPasswordAndGetGenerationToken ();
			            $personne->save ();
			        } else {
			            $generationTk = $personne->generationToken;
			        }
			        sendReactivationProcedure ( $personne->email, $generationTk );
			        
			        $message = "Un eMail vous à été envoyé à l'adresse {$ARGS["login"]}. <br />";
			        $message .= "Consultez votre boite aux lettres (et les SPAMS). <br />";
			        $message .= "Vous y trouverez toutes les instructions pour récupérer de votre Espace Membre.";
			        
			        $page->appendNotification ( $message );
			        
			        $ARGS ["redirectAction"] = "home";
			    } else {
			        
			        $page->appendNotification ( "Votre compte est tout nouveau, mais pas encore activé. <br />Votre compte sera actif dès que votre inscription sera validée." );
			        
			        
			        $ARGS ["redirectAction"] = "home";
			    }
			    
				
			} else {
				
				$message = "L'adresse eMail '{$ARGS["login"]}' n'existe pas dans le site. <br />Vérifiez l'orthographe de l'adresse que vous avez saisie, ou essayez avec une autre adresse si vous en possédez plusieurs.";
				
				$page->appendBody ( file_get_contents ( "includes/html/ath-notLogged.html" ) );
				$page->asset ( "login", $ARGS ["login"] );
				$page->appendNotification ( $message );
			}
			
			break;
		
		case "hasRegenerationTk" :
			// Seconde phase de la réccupération d'un compte.
			
			$personne = $personne->findByGenerationToken ( $ARGS ["reactivateAccount"] );
			
			if (! is_null ( $personne )) {
				
				if (array_key_exists ( "npwa", $ARGS ) || array_key_exists ( "npwb", $ARGS )) {
					// L'utilisateur à déjà cliqué sur le lien dans l'email, et arrive avec le nouveau mot de passe.
					if (! array_key_exists ( "npwa", $ARGS ) || ! array_key_exists ( "npwb", $ARGS ) || $ARGS ["npwa"] != $ARGS ["npwb"]) {
						
						$message = "Le nouveau mot de passe et la confirmation du nouveau mot de passe ne correspondent pas.<br /> Réessayez.";
						
						$page->appendBody ( file_get_contents ( "includes/html/ath-changePassword.html" ) );
						$page->asset ( "reactivationToken", $ARGS ["reactivateAccount"] );
						$page->asset ( "formPhase", "hasRegenerationTk" );
						$page->appendNotification ( $message );
						
						if (Roles::canAdministratePersonne () && $personne->getPrimaryKey () != thisUserId()) {
							$page->asset ( "sendMailNotificationDisplay", "block" );
						} else {
							$page->asset ( "sendMailNotificationDisplay", "none" );
						}
					} else {
						$personne->setPassword ( $ARGS ["npwa"] );
						
						$personne->generationToken = null;
						$personne->save ();
						
						prepareUserSession ( $personne );
						
						$message = "Le mot de passe est changé !";
						
						$page->appendNotification ( $message );
						$page->appendNotification ( "Ravis de vous revoir, {$_SESSION["userName"]} !" );
						
						$ARGS ["redirectAction"] = "home";
					}
				} else {
					
					// L'utilisateur vient de cliquer sur le lien envoyé par email
					
					$page->appendBody ( file_get_contents ( "includes/html/ath-changePassword.html" ) );
					$page->asset ( "reactivationToken", $ARGS ["reactivateAccount"] );
					$page->asset ( "formPhase", "hasRegenerationTk" );
					
					if (Roles::canAdministratePersonne () && $personne->getPrimaryKey () != thisUserId()) {
						$page->asset ( "sendMailNotificationDisplay", "block" );
					} else {
						$page->asset ( "sendMailNotificationDisplay", "none" );
					}
				}
			} else {
				
				$message = "Le lien sur lequel vous avez cliqué est à usage unique.<br />Il se trouve qu'il a déjà été utilisé.";
				
				$page->appendNotification ( $message );
				
				$ARGS ["redirectAction"] = "home";
			}
			
			break;
		
		default :
			throw new Exception ( "Erreur dans le processus de connexion : la phase '{$ARGS["phase"]}' n'existe pas." );
			break;
	}
}

?>