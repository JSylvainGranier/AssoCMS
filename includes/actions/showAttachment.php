<?php
$page->setStandardOuputDisabled ( true );
$attachment = new Attachment ( $ARGS ["id"] + 0 );

if ($attachment == null) {
	throw new Exception ( "Ce fichier joint n'existe plus en BDD !" );
}

if (! Roles::isMembre () && ! $attachment->isPublic) {
	throw new RoleException ( "Ce fichier ne peut être consulté sans être connecté." );
}

$file = $attachment->getServerFilePath ();

if (! file_exists ( $file )) {
	throw new Exception ( "Ce fichier n'est plus sur le serveur !" );
}

header ( 'Content-type: ' . $attachment->typeMime );
$disposition = (array_key_exists ( "forceDownload", $ARGS )) ? "attachment" : "inline";
header ( 'Content-Disposition: ' . $disposition . '; filename="' . $attachment->originalFileName . '"' );
header ( 'Content-Transfer-Encoding: binary' );
header ( 'Content-Length: ' . filesize ( $file ) );
header ( 'Accept-Ranges: bytes' );

@readfile ( $file );
?>