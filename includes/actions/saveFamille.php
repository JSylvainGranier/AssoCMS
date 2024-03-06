<?php

$idFamille = $ARGS["idFamille"];

$idPersonnes = array();

foreach($ARGS["idPersonne"] as $persId){
    $idPersonnes[] = $persId;
}

$personne = new Personne();

$personne->updatesPersonnesForFamily($idFamille, $idPersonnes);


$page->appendNotification ( "Famille sauvegardées." );


$ACTIONS [] = array (
    "show",
    "class" => "Personne",
    "personne" => $idPersonnes[0]
);