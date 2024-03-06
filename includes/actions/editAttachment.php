<?php
$pageToShow = new Page ();
$pageToShow = $pageToShow->findById ( $ARGS ["id"] + 0 );

if (array_key_exists ( "included", $ARGS )) {
	$included = $ARGS ["included"] === 'true' ? true : false;
} else {
	$included = true;
}

if ($pageToShow != null) {
	
	$page->appendBody ( file_get_contents ( "includes/html/editAttachment.html" ) );
	
	$evenement = null;
	if (array_key_exists ( "idEvt", $ARGS )) {
		$evenement = new Evenement ( $ARGS ["idEvt"] );
	}
	
	$idPage = $pageToShow->getPrimaryKey ();
	
	$max_upload = ( int ) (ini_get ( 'upload_max_filesize' ));
	$max_post = ( int ) (ini_get ( 'post_max_size' ));
	$memory_limit = ( int ) (ini_get ( 'memory_limit' ));
	$upload_mb = min ( $max_upload, $max_post, $memory_limit );
	
	$page->asset ( "maxsize", $upload_mb );
	
	$page->asset ( "idPage", $idPage );
	
	$atchList = new Attachment ();
	$atchList = $atchList->listAttachmentsForPage ( $idPage );
	
	if (sizeof ( $atchList ) > 0) {
		foreach ( $atchList as $anAttachment ) {
			$page->append ( "attachmentList", "<div id='{$anAttachment->getPrimaryKey()}-container'>{$anAttachment->getEditionFormHtml()}</div>" );
		}
	} else {
		$page->asset ( "attachmentList", "<h4 id='noAttachmentYet'>Il n'y a pas encore de pièce jointe.</h4>" );
	}
	
	if (! $included) {
		$page->asset ( "intro", " pour '{$pageToShow->titre}' : " );
		$page->asset ( "backButton", "<input type='button' value='Terminé' onclick='history.back();' />" );
	}
} else {
	$page->appendBody ( "<h3>Fichiers joints</h3>Il faut sauvegarder la page ou le rendez-vous au moins une fois pour pouvoir enregistrer des fichiers." );
}

