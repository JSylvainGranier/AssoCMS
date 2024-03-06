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
    $listEvenement .= "<option value='{$pp->idPage}'>{$dt} / {$pp->getCategorieClassement()->nom} / {$pp->getTitre()}</option>";
}


$page->asset("listEvenement", $listEvenement);
$page->asset("fkUserId", thisUserId());


$pers = new Personne();
$persListDroitImage = "";
$comma = "";
foreach($pers->getAllNotAllowPublishMyFace() as $perSansPhoto){
    $persListDroitImage .= $comma.$perSansPhoto->prenom." ".$perSansPhoto->nom;
    $comma = ", ";
}

if(strlen($persListDroitImage) > 0){
    $persListDroitImage = "Les personnes suivantes ont refusé que des photos où elles sont présentes soient publiées. Merci de respecter leur choix. <br />".$persListDroitImage;
}

$page->asset("persDroitImage", $persListDroitImage);
