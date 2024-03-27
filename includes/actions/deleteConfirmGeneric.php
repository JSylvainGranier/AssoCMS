<?php


$page->appendBody ( file_get_contents ( "includes/html/deleteConfirmation.html" ) );
		
$page->asset ( "class", $class );
$page->asset ( "id", $id );

$page->asset ( "objectTitle", $class . " n°" . $object->getPrimaryKey () );
$page->asset ( "objectToString", $object->getShortToString () );

$thenArgs = "";

if(array_key_exists("thenAction", $ARGS)){

    $thenArgs = "&thenAction=".$ARGS["thenAction"]."&thenClass=".$ARGS["thenClass"]."&thenIdName=".$ARGS["thenIdName"]."&thenIdValue=".$ARGS["thenIdValue"];
}

$page->asset ( "thenArgs", $thenArgs);

