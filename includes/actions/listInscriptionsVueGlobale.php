<?php 

$page->append ( "Vue Globale" );

if (! Roles::isGestionnaire()) {
	header ( "HTTP/1.0 403 Forbidden" );
	echo "403";
	die ();
}


?>