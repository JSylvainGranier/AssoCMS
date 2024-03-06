<?php
$page->appendBody ( file_get_contents ( "includes/html/editPersonne.html" ) );

$user = new Personne ();

if (array_key_exists ( "createNewUser", $ARGS )) {
	
	if (! Roles::canAdministratePersonne ()) {
		throw new Exception ( "Vous n'êtes pas habilité à créer ou modifier un autre profil utilisateur que le votre." );
	}
	
	// User déjà instancié, et vide.
} else if (array_key_exists ( "idPersonne", $ARGS )) {
	
    if (! Roles::canAdministratePersonne () && $ARGS["idPersonne"] != $_SESSION["userId"]) {
		throw new Exception ( "Vous n'êtes pas habilité à créer ou modifier un autre profil utilisateur que le votre." );
	}
	
	$user = new Personne ( $ARGS ["idPersonne"] );
} else {
	$user = new Personne ( thisUserId() );
}

$sameUserAsActor = thisUserId() == $user->getPrimaryKey ();

$page->asset ( "nom", protectInputValueApostrophe ( $user->nom ) );
$page->asset ( "prenom", protectInputValueApostrophe ( $user->prenom ) );
$page->asset ( "email", protectInputValueApostrophe ( $user->email ) );

$civiliteSelectedField = "nexistepas";

switch ($user->civilite) {
	case "Monsieur" :
		$civiliteSelectedField = "selectedMonsieur";
		break;
	case "Madame" :
		$civiliteSelectedField = "selectedMadame";
		break;
	case "Monsieur et Madame" :
		$civiliteSelectedField = "selectedMandM";
		break;
}

$page->asset ( $civiliteSelectedField, "selected='selected'" );

$page->asset ( "telFixe", protectInputValueApostrophe ( $user->telFixe ) );
$page->asset ( "telPortable", protectInputValueApostrophe ( $user->telPortable ) );
if(! is_null($user->dateNaissance)){
    $page->asset ( "dateNaissance", protectInputValueApostrophe ( $user->dateNaissance->format('d/m/Y') ) );
}
$page->asset ( "adrL1", protectInputValueApostrophe ( $user->adrL1 ) );
$page->asset ( "adrL2", protectInputValueApostrophe ( $user->adrL2 ) );
$page->asset ( "adrL3", protectInputValueApostrophe ( $user->adrL3 ) );
$page->asset ( "adrCP", protectInputValueApostrophe ( $user->adrCP ) );
$page->asset ( "adrVille", protectInputValueApostrophe ( $user->adrVille ) );

$page->asset ( "idPersonne", $user->getPrimaryKey () );

if ($user->allowEmails) {
	$page->asset ( "allowEmailsTrue", "checked" );
	$page->asset ( "allowEmailsFalse", "" );
} else if (! $user->allowEmails) {
	$page->asset ( "allowEmailsTrue", "" );
	$page->asset ( "allowEmailsFalse", "checked" );
}

if ($user->allowMembersVisitProfile) {
	$page->asset ( "allowMembersVisitProfileTrue", "checked" );
} else {
	$page->asset ( "allowMembersVisitProfileFalse", "checked" );
}

if ($user->allowPublishMyFace) {
	$page->asset ( "allowPublishMyFaceTrue", "checked" );
} else {
	$page->asset ( "allowPublishMyFaceFalse", "checked" );
}

if ($user->allowedToConnect) {
    $page->asset ( "allowedToConnectTrue", "checked" );
} else {
    $page->asset ( "allowedToConnectFalse", "checked" );
}


// Récapitulatif papier

if (Roles::canAdministratePersonne ()) {
	$page->asset ( "gestionMemberVisibility", "visible" );
} else {
	$page->asset ( "gestionMemberVisibility", "none" );
}

$rolesSelect = "";
if (Roles::isSuperAdmin ()) {
	$usrRls = $user->getRolesArray ();
	$rolesSelect = "<p><label for='personeRole'>Rôle</label>
	<select id='personeRole' name='roles'>
		<option value=''>Membre de l'association</option>
		<option value='100;' " . (array_search ( 100, $usrRls ) !== false ? "selected='selected'" : "") . ">Gestionnaire d'une catégorie</option>
		<option value='200;' " . (array_search ( 200, $usrRls ) !== false ? "selected='selected'" : "") . ">Gestionnaire global</option>
		<option value='300;' " . (array_search ( 300, $usrRls ) !== false ? "selected='selected'" : "") . ">Comptable</option>
		<option value='450;' " . (array_search ( 450, $usrRls ) !== false ? "selected='selected'" : "") . ">Président</option>
		<option value='500;' " . (array_search ( 500, $usrRls ) !== false ? "selected='selected'" : "") . ">Super Administrateur</option>
	</select></p>";
}
$page->asset ( "rolesSelect", $rolesSelect );

if ($user->wantPaperRecap) {
	$page->asset ( "wantPaperRecapTrue", "checked" );
} else if (! $user->wantPaperRecap) {
	$page->asset ( "wantPaperRecapFalse", "checked" );
}


$page->setTitle ( "Options Espace Membre de " . $user->prenom . " " . $user->nom );
$page->asset ( "userToEdit", $user->nom );



