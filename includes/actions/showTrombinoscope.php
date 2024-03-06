<?php
$page->setStandardOuputDisabled ( true );

if (! Roles::isMembre ()) {
	header ( "HTTP/1.0 403 Forbidden" );
	echo "403";
	die ();
}

$file = "documents/trombi/" . $ARGS ["id"].".jpg";

if (! file_exists ( $file )) {
	header ( "HTTP/1.0 404 Not Found" );
	echo "404";
} else {
	header ( 'Content-type: image/jpeg' );
	header ( 'Content-Length: ' . filesize ( $file ) );
	header ( 'Accept-Ranges: bytes' );
	
	@readfile ( $file );
}

?>