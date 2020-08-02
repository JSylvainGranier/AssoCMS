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
    
    $jsonConfig = array();
    
    $jsonConfig["allowSearchInAllPersons"] = Roles::isGestionnaireGlobal();
    
    $user = new Personne ( thisUserId() );
    $jsonConfig["famille"] = array();
    $jsonConfig["famille"]["id"] = $user->idFamille;
    $jsonConfig["famille"]["members"] = $user->getAllPersonnesInFamily($user->idFamille);
    
    $jsonConfig["produits"] = array();
    
    forEach($instance->getInscriptionsOuvertesEnCeMoment() as $produit){
        
        $divProduit = "<div class='produitSection' idproduit='{$produit->idProduit}'>";
        $divProduit .= "<p class='produitTitle'>{$produit->libelle}</p>";
        $divProduit .= "<div class='produitDescription'>{$produit->description}</div>";
        $divProduit .= "<div class='produitInscriptionContainer'>Inscire...</div>";
        $divProduit .= "</div>";
        
        $page->append("listProduit", $divProduit);
        
        $jsonConfig["produits"][$produit->idProduit] = $produit;
        
        $jsonConfig["produits"][$produit->produitRequis] = $instance->findById($produit->produitRequis);
        
    }
    
    $jsonConfig["inscriptionsExistantes"] = array();
      
    
    
    
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
