<?php

$iscp = new Inscription($ARGS["id"]);

$page->appendBody($iscp->getConfirmationInscriptionHtml());


if (Roles::isGestionnaireCategorie ()){
    if($iscp->etat != InscriptionEtat::$SUPPRIME){
        $page->appendActionButton ( "Supprimer", "changeInscriptionStatusGet&idInscription={$iscp->idInscription}&status=-15");
    }
    
    if($iscp->etat != InscriptionEtat::$ACCEPTE){
        $page->appendActionButton ( "Accepter", "changeInscriptionStatusGet&idInscription={$iscp->idInscription}&status=50");
    }
    
    if($iscp->etat != InscriptionEtat::$ARCHIVE && $iscp->etat != InscriptionEtat::$SOUMIS){
        $page->appendActionButton ( "Archiver", "changeInscriptionStatusGet&idInscription={$iscp->idInscription}&status=70");
    }
    
}