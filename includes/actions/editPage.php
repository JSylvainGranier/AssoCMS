
<?php
$page->appendBody ( file_get_contents ( "includes/html/editPage.html" ) );

$pageToEdit = new Page ();
$pers = new Personne ();
$cat = new Categorie ();

/* @var $cat Categorie */

if (array_key_exists ( "id", $ARGS ) && strlen ( $ARGS ["id"] ) > 0) {
	$pageToEdit = new Page ( $ARGS ["id"] );
	$cat = $pageToEdit->getCategorieClassement ();
	$page->asset ( "id", $pageToEdit->getPrimaryKey () );
	
	$page->setTitle ( "Modification d'une page" );
	$page->asset ( "titrePage", "Modification d'une page" );
} else {
	$page->setTitle ( "Création d'une nouvelle page" );
	$page->asset ( "titrePage", "Création d'une nouvelle page" );
	$cat = $cat->findById ( $ARGS ["fkCategorie"] );
}

$page->setCategorie ( $cat );

$page->asset ( "titre", protectInputValueApostrophe ( $pageToEdit->titre ) );

$page->asset ( "categorie", $cat->nom );
$page->asset ( "idCategorie", $cat->getPrimaryKey () );
$page->asset ( "intro", $pageToEdit->introduction );
$page->asset ( "suite", $pageToEdit->suite );

if ($pageToEdit->allowReactions) {
	$page->asset ( "allowReactionsTrue", "checked='checked'" );
} else {
	$page->asset ( "allowReactionsFalse", "checked='checked'" );
}

if ($pageToEdit->allowMemberAttachments) {
	$page->asset ( "allowMemberAttachmentsTrue", "checked='checked'" );
} else {
	$page->asset ( "allowMemberAttachmentsFalse", "checked='checked'" );
}

if (Roles::canPublishPage ()) {
	$page->asset ( "sendButtonLabel", "Enregistrer" );
	
	if (is_null ( $pageToEdit->etat )) {
		$curState = PageEtat::$BROUILLON;
	} else {
		$curState = $pageToEdit->etat;
	}
	
	$options = array ();
	// $options["-15"] = "Supprimé";
	
	if ($pageToEdit->etat == PageEtat::$PROPOSE) {
		$options ["-10"] = "Reffuser la proposition";
	}
	
	$options ["0"] = "Brouillon";
	
	if (! $cat->autoAffiliate) {
		$options ["15"] = "Accès aux membres de '{$cat->nom}'";
	}
	
	$options ["25"] = "Accès aux membres quand ils sont identifiés";
	$options ["50"] = "Accès public";
	
	$pubState = getSelectHtml ( "etat", $options, $curState );
	
	$page->asset ( "publicationState", $pubState );
	
	$order = "<p><label for='ordre'>Ordre dans les autres pages de la catégorie : </label>
		<input name='ordre' id='ordre' type='number' value='{$pageToEdit->ordre}' /></p>";
	$page->asset ( "order", $order );
	
	$selectedYes = ($pageToEdit->displayInMenu) ? "checked='checked'" : "";
	$selectedNo = (! $pageToEdit->displayInMenu) ? "checked='checked'" : "";
	$displayInMenu = "<p><label for='displayInMenu'>Afficher un lien dans le menu : </label>
		<label for='displayInMenuYES' >Oui : </label><input type='radio' value='1' name='displayInMenu' id='displayInMenuYES' {$selectedYes} />
		<label for='displayInMenuNO' >Non : </label><input type='radio' value='0' name='displayInMenu' id='displayInMenuNO' {$selectedNo}/>
	</p>";
	
	$page->asset ( "displayInMenu", $displayInMenu );
	
	if ($pageToEdit->getAuteur () == null) {
		$defaultAutor = thisUserId();
	} else {
		$defaultAutor = $pageToEdit->getAuteur ()->getPrimaryKey ();
	}
	
	$autorList = new Personne ();
	$autorList = $autorList->getAll ();
	$optionsValues = array ();
	foreach ( $autorList as $aPersonne ) {
		$optionsValues [$aPersonne->getPrimaryKey ()] = $aPersonne->nom . " " . $aPersonne->prenom;
	}
	$autorSelect = getSelectHtml ( "auteur", $optionsValues, $defaultAutor );
	
	$autorInput = "<p><label for='autor'>Auteur de la page : </label>{$autorSelect}</p>";
	
	$page->asset ( "autor", $autorInput );
} else {
	
	if ($cat->getPersonneGestionnaire () != null) {
		$pers = $cat->getPersonneGestionnaire ();
		$gest = " ( <a href='index.php?show&class=Personne&id={$pers->getPrimaryKey()}' target='_blank'>{$per->prenom}</a> )";
	} else {
		$gest = "";
	}
	
	$proposeState = PageEtat::$PROPOSE;
	$hint = "Vous vous apprêtez à proposer une page. Après validation
	par le gestionnaire de la catégorie{$gest}, votre proposition sera visible 
	par tout le monde. <input type='hidden' name='etat' value='{$proposeState}' />";
	
	$page->asset ( "publicationState", $hint );
	
	$page->asset ( "sendButtonLabel", "Proposer" );
}

$ACTIONS [] = array (
		"edit",
		"class" => "Attachment",
		"id" => $pageToEdit->getPrimaryKey () 
);

//$page->appendActionButton("Fichiers Joints", "edit&class=Attachment&id=".$pageToEdit->getPrimaryKey());