<?php 


$rep = array();


$inscription = new Inscription($ARGS["idInscription"]+0);
$newStatus = $ARGS["status"];

$rep = $inscription->changerStatut($newStatus);

foreach($rep as $r){
    $page->appendNotification ( $r );
}

$ACTIONS [] = array("list",
    "class" => "Inscription"
);


?>