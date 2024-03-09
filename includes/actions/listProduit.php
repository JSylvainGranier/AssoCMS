<?php
$page->append ( "body", file_get_contents ( "includes/html/listProduit.html" ) );

$prod = new Produit();

$listeProduitsActifs = "";
$listeProduitsInactifs = "";

foreach($prod->getAllActive() as $p){
    $lien = "index.php?edit&class=Produit&id=".$p->idProduit;

    $listeProduitsActifs .= "<li><a href='{$lien}'>{$p->libelle}</a></li>";
}

foreach($prod->getAllInactive() as $p){
    $lien = "index.php?edit&class=Produit&id=".$p->idProduit;

    $listeProduitsInactifs .= "<li><a href='{$lien}'>{$p->libelle}</a></li>";
}


$page->asset("listeProduitsActifs", $listeProduitsActifs);
$page->asset("listeProduitsInactifs", $listeProduitsInactifs);