<?php 

$idFamille = $ARGS["idFamille"];

$page->appendBody ( file_get_contents ( "includes/html/editFamille.html" ) );

$personne = new Personne();

$pList = "";

foreach ($personne->getAll() as $pers){
    $checked = $pers->idFamille == $idFamille ? " checked='checked' " : "";
    
    $pList .= "<li><label><input type='checkbox' value='{$pers->idPersonne}' {$checked} name='idPersonne[]'/>{$pers->nom} {$pers->prenom}</label></li>";
}

$page->asset("persList", $pList);
$page->asset("idFamille", $idFamille);


?>