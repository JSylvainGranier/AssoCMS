<?php
if (! Roles::isGestionnaireGlobal()) {
	throw new RoleException ( "Vous n'êtes pas habilité à naviguer ici." );
}

$produit = new Produit($ARGS["id"]);

$page->appendBody ( file_get_contents ( "includes/html/showProduit.html" ) );

$page->asset("produitTitre", $produit->libelle);
$page->asset("description", $produit->description);
$page->asset("idProduit", $produit->idProduit);

if($produit->produitRequis > 0){
    $prq = new Produit($produit->produitRequis);
    
    $page->asset("dependance", "Dépends de <a href='index.php?show&class=Produit&id={$prq->idProduit}'>{$prq->libelle}</a>");
} else {
    $page->asset("dependance", "<i>Sans dépendance</i>");
}

$nbNonInscrit = 0;
    $nbEnCours = 0;
    $nbInscrit = 0;
    $nbArchive = 0;

foreach ( $produit->getInscritsOuPasSurCeProduit() as $data ) {
    
    //$li = "<li><a href='index.php?show&class=Personne&id={$data['idPersonne']}'>{$data['nom']} {$data['prenom']}</a>";
    //$li = "<li><a href='index.php?list&class=InscriptionsOuvertes&forceFamily={$data['idFamille']}'>{$data['nom']} {$data['prenom']}</a>";
    
    $qt = "";
    $nbComptabiliser = 1;
    if($data["quantite"] > 1){
        $qt = " (x ".$data["quantite"].")";
        $nbComptabiliser = $data["quantite"];
    }
    
    if(is_null($data["etat"])){
        $target = "nonInscrits";
        $li = "<li><a href='index.php?list&class=InscriptionsOuvertes&forceFamily={$data['idFamille']}'>{$data['nom']} {$data['prenom']}</a> {$qt}";
        $nbNonInscrit += $nbComptabiliser;
        
    } else if($data["etat"] == 20){
        $target = "enCours";
        $li = "<li><a href='index.php?show&class=Personne&id={$data['idPersonne']}'>{$data['nom']} {$data['prenom']}</a> {$qt}";
        $nbEnCours += $nbComptabiliser;
        
    } else if($data["etat"] == 50){
        $target = "inscrits";
        $li = "<li><a href='index.php?show&class=Personne&id={$data['idPersonne']}'>{$data['nom']} {$data['prenom']}</a> {$qt}";
        $nbInscrit += $nbComptabiliser;
    } else if($data["etat"] == 70){
        $target = "archive";
        $li = "<li><a href='index.php?show&class=Personne&id={$data['idPersonne']}'>{$data['nom']} {$data['prenom']}</a> {$qt}";
        $nbArchive += $nbComptabiliser;
    }
    
    $page->append($target, $li);
}

$page->append("nonInscrits", "<li>Total : {$nbNonInscrit} </li>");
$page->append("enCours", "<li>Total : {$nbEnCours} </li>");
$page->append("inscrits", "<li>Total : {$nbInscrit} </li>");
$page->append("archive", "<li>Total : {$nbArchive} </li>");