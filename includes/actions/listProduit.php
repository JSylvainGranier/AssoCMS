<?php
$page->append ( "body", file_get_contents ( "includes/html/listProduit.html" ) );

$prod = new Produit();

$listeProduitsActifs = "";
$listeProduitsInactifs = "";

foreach($prod->getAllActive() as $p){
    $lien = "index.php?edit&class=Produit&id=".$p->idProduit;

    $lienClone = "index.php?edit&class=Produit&cloneId=".$p->idProduit;

    $listeProduitsActifs .= "<li><a href='{$lien}'>{$p->libelle}</a> <span style='font-size : 0.8em'>( <a href='{$lienClone}'>cloner</a> )</span></li>";
}

foreach($prod->getAllInactive() as $p){
    $lien = "index.php?edit&class=Produit&id=".$p->idProduit;

    $lienClone = "index.php?edit&class=Produit&cloneId=".$p->idProduit;

    $listeProduitsInactifs .= "<li><a href='{$lien}'>{$p->libelle}</a> <span style='font-size : 0.8em'>( <a href='{$lienClone}'>cloner</a> )</span></li>";
}


$page->asset("listeProduitsActifs", $listeProduitsActifs);
$page->asset("listeProduitsInactifs", $listeProduitsInactifs);