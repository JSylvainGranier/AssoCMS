<?php


$page->appendBody ( file_get_contents ( "includes/html/deleteConfirmation.html" ) );
		
$page->asset ( "class", $class );
$page->asset ( "id", $id );

$page->asset ( "objectTitle", $class . " n°" . $object->getPrimaryKey () );
$page->asset ( "objectToString", $object->getShortToString () );



