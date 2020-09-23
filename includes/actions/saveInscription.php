<?php

$page->setStandardOuputDisabled ( true );

$pers = new Personne(thisUserId());
$prod = new Produit();


// Takes raw data from the request
$json = file_get_contents('php://input');
// Converts it into a PHP object
$data = json_decode($json);

$rep = array ();


$idFamille = $data->famille->id;
$idInscription = $data->currentInscription->idInscription;

$inscription = new Inscription($data->currentInscription->idInscription);

$reglement = new Reglement();

$firstPersonne = null;

//Mettre à jour l'Inscription

$inscription->etat = InscriptionEtat::$SOUMIS;
$inscription->debut = new MyDateTime();
//Faudrait faire quelque chose des champs debut et fin sur Inscriptiuon. 
$inscription->save();

//Créer, purger, mettre à jour les Inscription Personne Produit

$ipp = new InscriptionPersonneProduit();
$ipp->clearForInscription($idInscription);

foreach ($data->inscriptionPersonneProduit->$idInscription as $anIpp){
    
    if($anIpp->quantite <= 0)
        continue;
    
    if(isset($anIpp->idIPP)){
        $ippInstance = new InscriptionPersonneProduit($anIpp->idIPP);
    } else {
        $ippInstance = new InscriptionPersonneProduit();
        $ippInstance->inscription = $inscription;
    }
    
    $ippInstance->personne = new Personne($anIpp->idPersonne);
    $ippInstance->produit = new Produit($anIpp->idProduit);
    $ippInstance->quantite = $anIpp->quantite;
    
    if(is_null($firstPersonne) && !is_null($ippInstance->personne->email)){
        $firstPersonne = $ippInstance->personne;
    }
    
    if(isset($data->conditionsAcceptees[$anIpp->idProduit])){
        $ippInstance->conditionsLegales = $data->conditionsAcceptees[$anIpp->idProduit];
        $ippInstance->dateAcceptationConditionsLegales = new MyDateTime();
        
    }
    
    $ippInstance->save();
    
}

//Supprimer tous les règlements qu'il pourait y avoir sur cette inscription (et qui ne sont pas perçus), et en créer de nouveaux avec $data->Ticket. 

$reglement->clearReglementNonPercusForInscription($idInscription);

foreach ($data->ticket as $tl){
    
    if($tl->quantite <= 0)
        continue;
    
    $r = new Reglement();
    $r->inscription = $inscription;
    $r->libelle = $tl->libelle;
    $r->montant = $tl->quantite * $tl->prixUnitaire;
    $r->modePerception = "debit";
    $r->idFamille = $idFamille;
    $r->produit = new Produit($tl->idProduit);
    if(isset($tl->remise)){
        $r->montant = $tl->remise;
    }
    $r->dateEcheance = MyDateTime::createFromFormat('Y-m-d H:i', substr($tl->dateEcheance, 0, 10 )." 00:00" );
    
    if((is_null($inscription->fin) || $inscription->fin->date < $r->dateEcheance->date) 
        && $r->dateEcheance->date != $inscription->fin->date){
        $inscription->fin = $r->dateEcheance;
    }
    
    $r->save();
}

$inscription->save();


if( ! Roles::isGestionnaireGlobal()){
    $messageConfirm = "<p>Bonjour, </p>";
    $messageConfirm .= "<p>Nous avons bien enregistré votre inscription sur le site de ".SITE_TITLE."</p>";
    $messageConfirm .= "<p>Dès qu'un membre du bureau de l'association aura confirmé votre demande, vous recevrez second message de confirmation avec tous les détails de votre souscription.</p>";
    $messageConfirm .= "<p>Bonne journée.</p>";
    
    sendSimpleMail ( "Prise en compte de votre inscription à " . SITE_TITLE, $messageConfirm, $firstPersonne->email, true );
    
    $rep["emailConfirm"] = $pers->email;
} else {
    $rep["emailConfirm"] = "aucun";
}

if (isPhpUp ()) {
    $jsToReturn = json_encode ( $rep );
} else {
    $jsToReturn = json_encode ( $rep, JSON_UNESCAPED_UNICODE );
}

echo $jsToReturn;




