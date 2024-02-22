<?php

$page->setTitle ( "Envoyer mes photos" );
$page->appendBody ( file_get_contents ( "includes/html/photobookUpload.html" ) );

$cat = new Categorie();
$evenement = new Evenement();


$listEvenement = "";

foreach($evenement->getAllInPast(6) as $evt){
    $pp = $evt->getPage();
    $pp->getCategorieClassement()->nom;
    $dt = $evt->dateDebut->format("d/m/Y");
    $listEvenement .= "<option value='{$evt->idEvenement}'>{$dt} / {$pp->getCategorieClassement()->nom} / {$pp->getTitre()}</option>";
}


$page->asset("listEvenement", $listEvenement);