<?php
$page->appendBody ( file_get_contents ( "includes/html/listInscriptionsOuvertes.html" ) );
/* @var $instance Produit */
$instance = new Produit ();
/* @var $inscription Inscription */
$inscription = new Inscription();

/*
$page->asset ( "catName", $cat->nom );
$page->asset ( "catId", $cat->getPrimaryKey () );
$page->append ( $tag, "<li class='listEmptyItem'>Il n'y a aucun rendez-vous prévu pour ce mois-ci. </li>" );
*/



$page->setTitle ( "Inscriptions Ouvertes" );

if (! Roles::isMembre () && ! Roles::isInvite () ) {
    $page->asset("displayCreateAccountParapgraph", "block");
    $page->asset("visibilityOnRequirementFullfilled", "none");
    $page->appendBody ( file_get_contents ( "includes/html/selfCreateAccountForm.html" ) );
    
    $page->asset("config", "{}" );
    $page->asset("runJsInit", "false");
    
    
} else {
    $page->asset("displayCreateAccountParapgraph", "none");
    
    $user = new Personne ( thisUserId() );
    
    $idFamille = $user->idFamille;
    
    $editInscription = -1;
    if(isset($ARGS["editInscription"])){
        $editInscription = parseInt($ARGS["editInscription"]);
    }
    
    
    
    if(Roles::isGestionnaireGlobal() ){
        $page->asset("adminForOtherFamilies", "<a href='index.php?InscriptionsSwitchFamily'>Administrateur, vous faite une inscriptions pour une autre famille ?</a>");
        if(isset($ARGS["forceFamily"])){
            $idFamille = $ARGS["forceFamily"];
        }
    }
    
    if($editInscription < 0 ){
        $possibleInscriptionAEditer = $inscription->findRecentInscriptionForFamille($idFamille);
        if(!is_null($possibleInscriptionAEditer)){
            $editInscription = $possibleInscriptionAEditer->idInscription;
        }
    }
    
    $jsonConfig = array();
    
    $jsonConfig["allowSearchInAllPersons"] = Roles::isGestionnaireGlobal();
    
    $jsonConfig["famille"] = array();
    $jsonConfig["famille"]["id"] = $idFamille;
    $jsonConfig["famille"]["members"] = $user->getAllPersonnesInFamily($idFamille);
    
    $compteJointBreak = false;
    $dateNaissanceBreak = false;
    
    forEach($jsonConfig["famille"]["members"] as $personne){
        if(is_null($personne->dateNaissance) ){
            $dateNaissanceBreak = true;
            
            $page->reset();
            
            if(thisUserId() == $personne->idPersonne){
                $page->appendNotification ("Avant de poursuivre, vous devez renseigner votre date de naissance dans votre compte." );
            } else {
                $page->appendNotification ("Avant de poursuivre, le profil de {$personne->prenom} {$personne->nom} doit être complété avec sa date de naissance." );
            }
            
            $redirection = array (
                "edit",
                "class" => "Personne",
                "idPersonne" => $personne->idPersonne
            );
            $ACTIONS [] = $redirection;
            
            break;
            
        } else {
            $personne->dateNaissance = $personne->dateNaissance->format("Y-m-d");
        }
        
        if($personne->civilite == "Monsieur et Madame"){
            $compteJointBreak = true;
            $page->append("requirementNoFullFilled", "<p>Vous utilisez un compte joint pour vous connecter sur le site de VISA30. <br />Le système des inscriptions en ligne ne prends pas en charge ce cas.  <br />Adressez nous un email en nous indiquant une adresse électronique pour chaque personne, et nous résoudrons le problème. <br />Désolé pour ce contre-temps :-( </p>");
            
        }
        
        
        
        
    }
    
    if($compteJointBreak || $dateNaissanceBreak){
        $page->asset("visibilityOnRequirementFullfilled", "none");
        return;
    } else {
        $page->asset("visibilityOnRequirementFullfilled", "block");
    }
    
    $jsonConfig["produits"] = array();
    
    forEach($instance->getInscriptionsOuvertesEnCeMoment(Roles::isGestionnaireGlobal()) as $produit){
              
        $jsonConfig["produits"][$produit->idProduit] = $produit;
        
        $jsonConfig["produits"][$produit->produitRequis] = $instance->findById($produit->produitRequis);
        
    }
    
    $jsonConfig["inscription"] = array();
      
    $inscription = new Inscription();
    $inscription->etat = InscriptionEtat::$BROUILLON;
    $inscription->debut = new MyDateTime();
    $incrPersPro = new InscriptionPersonneProduit();
    
    if($editInscription > 0){
        $inscription = $inscription->findById($editInscription);
    } else {
        $inscription->idFamille = $idFamille;
    }
    $inscription->save();
    
    
    
    $jsonConfig["currentInscription"] = $inscription;
    
    foreach($inscription->getInscriptionsForFamille($idFamille) as $anInscription){
        $jsonConfig["inscription"][$anInscription->idInscription] = $anInscription;
        
        $jsonConfig["inscriptionPersonneProduit"][$anInscription->idInscription] = $incrPersPro->getAllForInscription($anInscription->idInscription);
    }
    
    if (isPhpUp ()) {
        $page->asset("config", json_encode ( $jsonConfig ) );
    } else {
        $page->asset("config", json_encode ( $jsonConfig, JSON_UNESCAPED_UNICODE ) );
    }
    
    $page->asset("runJsInit", "true");
    
    
}

/*
if (Roles::isGestionnaireGlobal () || Roles::isGestionnaireOfCategorie ( $cat )) {
	$page->appendActionButton ( "Ajouter une page", "edit&class=Page&fkCategorie=" . $cat->getPrimaryKey () );
	$page->appendActionButton ( "Ajouter un rendez-vous", "edit&class=Evenement&fkCategorie=" . $cat->getPrimaryKey () );
	$page->appendActionButton ( "Modifier la section", "edit&class=Categorie&id=" . $cat->getPrimaryKey () );
	$page->appendActionButton ( "Affectations à '{$cat->nom}'", "list&class=AffiliationCategorie&categorie=" . $cat->getPrimaryKey () );
} else if (Roles::canAdministratePersonne ()) {
	$page->appendActionButton ( "Affectations à '{$cat->nom}'", "list&class=AffiliationCategorie&categorie=" . $cat->getPrimaryKey () );
} else if (Roles::isMembre ()) {
	$page->appendActionButton ( "Proposer une page dans '{$cat->nom}'", "edit&class=Page&fkCategorie=" . $cat->getPrimaryKey (), false, false );
	$page->appendActionButton ( "Proposer un rendez-vous dans '{$cat->nom}'", "edit&class=Evenement&fkCategorie=" . $cat->getPrimaryKey (), false, false );
}
*/
