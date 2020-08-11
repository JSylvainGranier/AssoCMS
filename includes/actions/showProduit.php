<?php
if (! Roles::isGestionnaireGlobal()) {
	throw new RoleException ( "Vous n'êtes pas habilité à naviguer ici." );
}

$produit = new Produit($ARGS["id"]);

$page->appendBody ( file_get_contents ( "includes/html/showProduit.html" ) );

$page->asset("produitTitre", $produit->libelle);
$page->asset("description", $produit->description);

if($produit->produitRequis > 0){
    $prq = new Produit($produit->produitRequis);
    
    $page->asset("dependance", "Dépends de <a href='index.php?show&class=Produit&id={$prq->idProduit}'>{$prq->libelle}</a>");
} else {
    $page->asset("dependance", "<i>Sans dépendance</i>");
}


foreach ( $produit->getInscritsOuPasSurCeProduit() as $data ) {
    
    //$li = "<li><a href='index.php?show&class=Personne&id={$data['idPersonne']}'>{$data['nom']} {$data['prenom']}</a>";
    //$li = "<li><a href='index.php?list&class=InscriptionsOuvertes&forceFamily={$data['idFamille']}'>{$data['nom']} {$data['prenom']}</a>";
    
    $target = "perdu";
    
    if(is_null($data["etat"])){
        $target = "nonInscrits";
        $li = "<li><a href='index.php?list&class=InscriptionsOuvertes&forceFamily={$data['idFamille']}'>{$data['nom']} {$data['prenom']}</a>";
        
    } else if($data["etat"] == 20){
        $target = "enCours";
        $li = "<li><a href='index.php?show&class=Personne&id={$data['idPersonne']}'>{$data['nom']} {$data['prenom']}</a>";
        
    } else if($data["etat"] == 50){
        $target = "inscrits";
        $li = "<li><a href='index.php?show&class=Personne&id={$data['idPersonne']}'>{$data['nom']} {$data['prenom']}</a>";
        
    } else if($data["etat"] == 70){
        $target = "inscrits";
        $li = "<li><a href='index.php?show&class=Personne&id={$data['idPersonne']}'>{$data['nom']} {$data['prenom']}</a>";
        
    }
    
    $page->append($target, $li);
}