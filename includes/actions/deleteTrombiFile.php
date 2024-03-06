<?php
$personne = new Personne ( $ARGS ["id"] );

unlink ( $personne->getTrombiFileFileSystemPath () );

$personne->trombiFile = null;

$personne->save ();

$page->appendNotification ( "Votre photo a été supprimée du trombinoscope." );

$ACTIONS [] = array (
		"show",
		"class" => "Personne",
		"id" => $ARGS ["id"] 
);




