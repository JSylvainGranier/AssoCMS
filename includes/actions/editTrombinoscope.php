<?php
$page->appendBody ( file_get_contents ( "includes/html/editTrombinoscope.html" ) );

$user = new Personne ();

if (array_key_exists ( "idPersonne", $ARGS )) {
	
	if (!Roles::canAdministratePersonne () && $ARGS["idPersonne"] != thisUserId() ) {
		throw new Exception ( "Vous n'êtes pas habilité à créer ou modifier un autre profil utilisateur que le votre." );
	}
	
	$user = new Personne ( $ARGS ["idPersonne"] );
} else {
	$user = new Personne ( thisUserId() );
}

$sameUserAsActor = thisUserId() == $user->getPrimaryKey ();

$max_upload = ( int ) (ini_get ( 'upload_max_filesize' ));
$max_post = ( int ) (ini_get ( 'post_max_size' ));
$memory_limit = ( int ) (ini_get ( 'memory_limit' ));
$upload_mb = min ( $max_upload, $max_post, $memory_limit );

$page->asset ( "fileSize", $upload_mb );
$page->asset ( "maxUploadByte", $upload_mb * 1024 * 1024 );

$page->asset ( "idPersonne", $user->getPrimaryKey () );

if (! is_null ( $user->trombiFile ) && file_exists ( $user->getTrombiFileFileSystemPath () )) {
	$imgHtml = "<img src='{$user->getTrombiFileUrlPath()}' class='tombiFile' id='trombiImage' />";
	$page->asset ( "tombiImgTag", $imgHtml );
	if ($sameUserAsActor || Roles::canAdministratePersonne ()) {
		$page->asset ( "trombiDeleteLink", "<a href='" . SITE_ROOT . "index.php?deleteTrombiFile&id={$user->getPrimaryKey()}'>Supprimer l'image</a>" );
	}
} else {
	$page->asset ( "tombiImgTag", "<img id='trombiImage' class='tombiFile' alt='Aucune photo' />" );
}

$page->asset("emailManuel", EMAIL_ON_ERROR);

$page->setTitle ( "Options Espace Membre de " . $user->prenom . " " . $user->nom );
$page->asset ( "userToEdit", $user->nom );


