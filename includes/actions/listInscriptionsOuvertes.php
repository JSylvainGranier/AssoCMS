<?php
$page->appendBody ( file_get_contents ( "includes/html/listInscriptionsOuvertes.html" ) );
/* @var $instance Produit */
$instance = new Produit ();


/*
$page->asset ( "catName", $cat->nom );
$page->asset ( "catId", $cat->getPrimaryKey () );
$page->append ( $tag, "<li class='listEmptyItem'>Il n'y a aucun rendez-vous prévu pour ce mois-ci. </li>" );
*/



$page->setTitle ( "Inscriptions Ouvertes" );

if (! Roles::isMembre () && ! Roles::isInvite () ) {
    $page->asset("displayCreateAccountParapgraph", "block");
    $page->appendBody ( file_get_contents ( "includes/html/selfCreateAccountForm.html" ) );
} else {
    $page->asset("displayCreateAccountParapgraph", "none");
    
    $user = new Personne ( thisUserId() );
    
    $idFamille = $user->idFamille;
    
    if(Roles::isGestionnaireGlobal() ){
        $page->asset("adminForOtherFamilies", "<a href='index.php?InscriptionsSwitchFamily'>Administrateur, vous faite une inscriptions pour une autre famille ?</a>");
        if(isset($ARGS["forceFamily"])){
            $idFamille = $ARGS["forceFamily"];
        }
    }
    
    $jsonConfig = array();
    
    $jsonConfig["allowSearchInAllPersons"] = Roles::isGestionnaireGlobal();
    
    $jsonConfig["famille"] = array();
    $jsonConfig["famille"]["id"] = $idFamille;
    $jsonConfig["famille"]["members"] = $user->getAllPersonnesInFamily($idFamille);
    
    $jsonConfig["produits"] = array();
    
    forEach($instance->getInscriptionsOuvertesEnCeMoment() as $produit){
              
        $jsonConfig["produits"][$produit->idProduit] = $produit;
        
        $jsonConfig["produits"][$produit->produitRequis] = $instance->findById($produit->produitRequis);
        
    }
    
    $jsonConfig["inscription"] = array();
      
    $inscription = new Inscription();
    $incrPersPro = new InscriptionPersonneProduit();
    
    foreach($inscription->getInscriptionsForFamille($idFamille) as $anInscription){
        $jsonConfig["inscription"][$anInscription->idInscription] = $anInscription;
        
        $jsonConfig["inscription"][$anInscription->idInscription]["souscripteurs"] = $incrPersPro->getAllForInscription($anInscription->idInscription);
    }
    
    if (isPhpUp ()) {
        $page->asset("config", json_encode ( $jsonConfig ) );
    } else {
        $page->asset("config", json_encode ( $jsonConfig, JSON_UNESCAPED_UNICODE ) );
    }
    
    
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
