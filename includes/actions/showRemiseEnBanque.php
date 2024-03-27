<?php
if (! Roles::isGestionnaireGlobal()) {
	throw new RoleException ( "Vous n'êtes pas habilité à naviguer ici." );
}

$rbq = new RemiseEnBanque($ARGS["id"]);

$page->appendBody ( file_get_contents ( "includes/html/showRemiseEnBanque.html" ) );


$page->asset("depositaire", $rbq->getDepositaire()->nom." ".$rbq->getDepositaire()->prenom);
$page->asset("ouverture", $rbq->lastUpdateOn->format("d/m/Y"));


$page->asset ( "raisonSocialeBanque", Param::getValue ( PKeys::$REM_BANQUE_TITULAIRE ) );
$page->asset ( "numCompteBanque", Param::getValue ( PKeys::$REM_BANQUE_NUM_COMPTE ) );

if(isset($rbq->dateRemise)){
    $page->asset("depotEnBanque", $rbq->dateRemise->format("d/m/Y"));
} else {
    $page->asset("depotEnBanque", "<i> Pas encore déposée ! </i>");
}

$rgl = new Reglement();
$reglements = $rgl->findReglementsSurRemise($rbq->idRemiseEnBanque);

$totalCount = 0;
$totalAmount = 0;

$familyLink = new Personne();

foreach($reglements as $reglement){

    $montant = money_format('%i', $reglement->montant);

    $familleMembres = $familyLink->getAllPersonnesInFamily($reglement->idFamille);

    $link = "index.php?show&class=Personne&id=".$familleMembres[0]->idPersonne;

    $row = "<tr>";
    $row .= "<td><a href='{$link}'>{$reglement->libelle}</td>";
    $row .= "<td>{$reglement->refPerception}</td>";
    $row .= "<td>{$montant} €</td>";
    $row .= "</tr>";

    $page->append("listeReglements", $row);

    $totalCount++;
    $totalAmount += $reglement->montant;
}

$page->asset("nbCheques", $totalCount);
$page->asset("stCheques", money_format('%i', $totalAmount)." €");


if(isset($rbq->dateRemise)){
    
} else {
    $dt = new MyDateTime();
    $dt = $dt->format("Ymd");
    $thenArgs = "&thenAction=show&thenClass=RemiseEnBanque&thenIdName=id&thenIdValue={$rbq->idRemiseEnBanque}";

    $page->appendActionButton ( "Clôturer cette remise", "save&class=RemiseEnBanque&dateRemise={$dt}&id=" . $rbq->idRemiseEnBanque.$thenArgs );
}

/*
$page->asset("produitTitre", $produit->libelle);
$page->asset("description", $produit->description);

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

*/