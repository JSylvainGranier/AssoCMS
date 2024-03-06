<?php
$page->appendBody ( file_get_contents ( "includes/html/listCategorie.html" ) );
$page->setTitle("Activités de l'association");

$cat = new Categorie();

$s = "";

foreach ($cat->getAll() as $categorie){
    $link = "index.php?list&class=Page&categorie=".$categorie->idCategorie;
    $s.= "<div class='catItem' onclick=\"openLink('{$link}');\"'>";
    $s.= "<p class='catTitle'><i class='".$categorie->iconClass."'></i>&nbsp;".$categorie->nom."</p>";
    $s.= "<div class='txtPresentation'>".$categorie->textePresentation."</div>";
    $s.= "<div class='catItemBottom' ><button onclick=\"openLink('{$link}');\">Plus de détails...</button></div>";
    $s.= "</div>";
    
}
$page->asset ( "catList", $s );

