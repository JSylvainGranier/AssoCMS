<?php
if (! Roles::isMembre ()) {
	throw new RoleException ( "Vous n'êtes pas habilité à voir le profil d'un membre de l'association." );
}

$page->appendBody ( file_get_contents ( "includes/html/showPersonne.html" ) );

/* @var $user Personne */
$user = new Personne ();

if (array_key_exists ( "idPersonne", $ARGS ) || array_key_exists ( "id", $ARGS )) {
	if (isConsistent ( $ARGS ["idPersonne"] )) {
		$user = $user->findById ( $ARGS ["idPersonne"] );
	} else if (isConsistent ( $ARGS ["id"] )) {
		$user = $user->findById ( $ARGS ["id"] );
	} else {
		throw new Exception ( "Une personne est spécifiée, mais sont id est vide." );
	}
} else {
	$user = $user->findById ( thisUserId() );
}

$sameUserAsActor = thisUserId() == $user->getPrimaryKey ();

$page->setTitle ( "Profil de " . $user->nom . " " . $user->prenom );

$page->asset ( "userToEdit", $user->nom . " " . $user->prenom );

if (Roles::canAdministratePersonne ()) {
	
	$page->asset ( "allowEmail", "Autorise le site " . SITE_TITLE . " à lui envoyer des eMails d'actualité : <b>" . ($user->allowEmails ? "oui" : "non") . "</b>" );
	$page->asset ( "allowMembersVisitProfile", "Autorise les autres membres à voir ses coordonnées dans le trombinoscope : <b>" . ($user->allowMembersVisitProfile ? "oui" : "non") . "</b>" );
	$page->asset ( "paperRecap", "Récapitulatif mensuel envoyé par courrier papier : <b>" . ($user->wantPaperRecap ? "oui" : "non") . "</b>" );
	if($user->dontWantUseTrombi){
		$page->asset ( "trombiPref", "A indiqué ne pas vouloir utiliser le trombinoscope le ".$user->dontWantUseTrombi->formatLocale() );
	} else if($user->cantUploadTrombiFile){
		$page->asset ( "trombiPref", "A indiqué ne pas parvenir à utilisez le trombinoscope le ".$user->cantUploadTrombiFile->formatLocale() );
	}
	if(! is_null($user->dateNaissance)){
    	$page->asset ( "dateNaissance", "Date de naissance : <b>" . $user->dateNaissance->format("d/m/Y") . "</b>" );
	} else {
	    $page->asset ( "dateNaissance", "Date de naissance : <i>non renseigné</i>" );
	}
	$page->asset ( "allowedToConnect", "Peut utiliser ses identifiants pour se connecter : <b>" . ($user->allowedToConnect ? "oui" : "non") . "</b>" );
	
	
}

if (Roles::isGestionnaireCategorie () || (Roles::isMembre () && $user->allowMembersVisitProfile) || $sameUserAsActor) {
	$email = "Pas d'email.";
	if (! is_null ( $user->email )) {
		$email = "<a href='mailto:{$user->email}'>$user->email</a>";
	}
	$page->asset ( "email", $email );
	
	$page->asset ( "adrL1", $user->adrL1 );
	$page->asset ( "adrL2", $user->adrL2 );
	$page->asset ( "adrL3", $user->adrL3 );
	$page->asset ( "cp", $user->adrCP );
	$page->asset ( "ville", $user->adrVille );
	$page->asset ( "allowPublishMyFace", $user->allowPublishMyFace ? "J'autorise" : "Je n'autorise pas" );
	
	$page->asset ( "telFixe", $user->telFixe );
	$page->asset ( "telPortable", $user->telPortable );
} else {
	$page->appendNotification ( "Vous ne pouvez pas accéder aux coordonnées de cette personne.
<br />Soit vous n’avez pas les droits, soit la personne en a explicitement fait la demande." );
}

$updatePhotoLabel = "Modifier la photo";

if (! is_null ( $user->trombiFile ) && file_exists ( $user->getTrombiFileFileSystemPath () )) {
	$page->asset ( "tombiImgTag", "<img src='{$user->getTrombiFileUrlPath()}' class='tombiFile' />" );
} else if (Roles::canAdministratePersonne ()) {
	$page->asset ( "tombiImgTag", "Pas encore de photo." );
	$updatePhotoLabel = "Cliquez ici pour en ajouter une";
}

$changePhotoLink = "<p><a href='index.php?edit&class=Trombinoscope&idPersonne={$user->getPrimaryKey()}'>{$updatePhotoLabel}</a></p>";

if ($sameUserAsActor) {
	$page->appendActionButton ( "Modifier mon profil", "edit&class=Personne", false, false );
	$page->appendActionButton ( "Changer mon mot de passe", "login&phase=changePassword", false, false );
	$page->asset ( 'changePhotoLink', $changePhotoLink );
} else {
	if (Roles::canAdministratePersonne ()) {
		
		$catAffilies = $user->getCategoriesAffiliesList ();
		
		$page->appendActionButton ( "Modifier le profil de " . $user->prenom, "edit&class=Personne&idPersonne=" . $user->getPrimaryKey () );
		$page->appendActionButton ( "Changer le mot de passe de " . $user->prenom, "login&phase=changePassword&idPersonne=" . $user->getPrimaryKey () );
		$page->appendActionButton ( "Affectations aux catégories", "list&class=AffiliationCategorie&personne=" . $user->getPrimaryKey () );
		$page->appendActionButton ( "Supprimer", "delete&class=Personne&id=" . $user->getPrimaryKey () );
		$page->asset ( 'changePhotoLink', $changePhotoLink );
	}
}

