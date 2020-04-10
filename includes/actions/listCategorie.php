<?php
$page->appendBody ( file_get_contents ( "includes/html/listCategorie.html" ) );
$page->setTitle("Activités de l'association");

$cat = new Categorie();

$s = "";

foreach ($cat->getAll() as $categorie){
    $s.= "<a href='index.php?list&class=Page&categorie=".$categorie->idCategorie."'>";
    $s.= "<li class='catItem'>";
    $s.= "<p class='catTitle'><i class='".$categorie->iconClass."'></i>&nbsp;".$categorie->nom."</p>";
    $s.= "<p class='txtPresentation'>".$categorie->textePresentation."</p>";
    $s.= "</li></a>";
    
}
$page->asset ( "catList", $s );

