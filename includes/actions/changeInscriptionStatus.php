<?php 

$inscription = new Inscription($ARGS["idInscription"]);
$inscription->etat = $ARGS["status"];
$inscription->save();


$page->appendNotification ( "Inscription passée à ".InscriptionEtat::getEtatLibelle($inscription->etat) );

$ACTIONS [] = array (
    "show",
    "class" => "Personne",
    "idPersonne" => $ARGS ["idPersonne"]
);



?>