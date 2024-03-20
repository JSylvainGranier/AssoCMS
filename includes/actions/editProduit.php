<?php
$page->appendBody ( file_get_contents ( "includes/html/editProduit.html" ) );

$produit = new Produit();

$idToEdit = -1;

if(array_key_exists("id", $ARGS)){
    $produit = new Produit($ARGS["id"]);
    $idToEdit = $produit->idProduit;
} else if (array_key_exists("cloneId", $ARGS)) {
    $pc = new Produit($ARGS["cloneId"]);
    $produit->libelle = $pc->libelle . " Cloné ";
    $produit->description = $pc->description;
    $produit->politiqueTarifaire = $pc->politiqueTarifaire;
    $produit->conditionsLegales = $pc->conditionsLegales;
    $produit->quantiteDisponible = $pc->quantiteDisponible;
    $produit->produitOrdre = $pc->produitOrdre;

    $produit->accesDirect = $pc->accesDirect;
    $produit->quantiteLibre = $pc->quantiteLibre;
    $produit->produitRequis = $pc->produitRequis;

    $produit->archive = false;

    $produit->debutDisponibilite = new MyDateTime();
    $produit->finDisponibilite = new MyDateTime();

} else {
    $produit->debutDisponibilite = new MyDateTime();
    $produit->finDisponibilite = new MyDateTime();
}



$page->asset('idProduit', $idToEdit);

$page->asset("libelle", $produit->libelle);


$page->asset("description", $produit->description);
$page->asset("politiqueTarifaire", $produit->politiqueTarifaire);
$page->asset("conditionsLegales", $produit->conditionsLegales);


$page->asset("quantiteDisponible", $produit->quantiteDisponible);
$page->asset("produitOrdre", $produit->produitOrdre);



$page->asset("debutDisponibilite", $produit->debutDisponibilite->format('d/m/Y'));
$page->asset("finDisponibilite", $produit->finDisponibilite->format('d/m/Y'));


$autoUtilisableOui = "";
$autoUtilisableNon = "";
if($produit->accesDirect){
    $autoUtilisableOui = "checked='checked'";
} else {
    $autoUtilisableNon = "checked='checked'";
}

$autoQuantifiableOui = "";
$autoQuantifiableNon = "";
if($produit->quantiteLibre){
    $autoQuantifiableOui = "checked='checked'";
} else {
    $autoQuantifiableNon = "checked='checked'";
}

$archiveOui = "";
$archiveNon = "";
if($produit->archive){
    $archiveOui = "checked='checked'";
} else {
    $archiveNon = "checked='checked'";
}


$page->asset("autoUtilisableOui", $autoUtilisableOui);
$page->asset("autoUtilisableNon", $autoUtilisableNon);
$page->asset("autoQuantifiableOui", $autoQuantifiableOui);
$page->asset("autoQuantifiableNon", $autoQuantifiableNon);
$page->asset("archiveOui", $archiveOui);
$page->asset("archiveNon", $archiveNon);


$optionsProduitRequis = "";
foreach($produit->getAll() as $p){
    if($p->idProduit == $produit->idProduit){
        continue;
    }

    $checked = ($p->idProduit === $produit->produitRequis) ? "selected" : "";

    $optionsProduitRequis .= "<option {$checked} value='{$p->idProduit}'>{$p->libelle} ({$p->idProduit}, Archivé = {$p->archive})</option>";
}

$page->asset("optionsProduitRequis", $optionsProduitRequis);