<?php

if(!Roles::isGestionnaireGlobal()){
    throw new RoleException("Pas d'accès cette section si l'on n'est pas gestionnaire.");
}

$page->append ( "body", file_get_contents ( "includes/html/listReglement.html" ) );
$page->setTitle("Règlements");

$reglement = new Reglement();
$inscription = new Inscription();
$personne = new Personne();

$dateReglementAttendu = new MyDateTime();

$saisieReglementFormConfig = array(
    "idFamille" => 0,
    "targetTag" => "formReglement",
    "thenAction" => "list",
    "thenClass" => "Reglement",
    "thenIdName" => "pof",
    "thenIdValue" => "pof"
);

if(isset($ARGS["datePerception"])){
    $dateReglementAttendu = MyDateTime::createFromFormat( "Y-m-d h:i", $ARGS["datePerception"].' 00:00');
}

$page->asset("datePerception", $dateReglementAttendu->format("Y-m-d"));

$result = $reglement->getAllFamillesEnAttenteReglement($dateReglementAttendu);




foreach ($result as $rnum => $cols){
    
    $cols["idFamille"];
    $fam = "";
    $comma = "";
    $idPersonne = 0;
    foreach($personne->getAllPersonnesInFamily($cols["idFamille"]) as $pers){
        $fam .= $comma.$pers->nom." ".$pers->prenom;
        $comma = ", ";
        if($idPersonne == 0){
            $idPersonne = $pers->getPrimaryKey();
        }
        $saisieReglementFormConfig["libelle"] = $pers->nom." ".$pers->prenom;
    }
    $saisieReglementFormConfig["idFamille"] = $cols["idFamille"];
    $saisieReglementFormConfig["targetTag"] = "formReglement".$cols["idFamille"];
    $saisieReglementFormConfig["montant"] = $cols["dete"];
    
    $fam = "<a href='index.php?show&class=personne&id={$idPersonne}'>{$fam}</a>";
    $idFamilleCurrent = $cols["idFamille"];
    $page->append("reglements", "<li><div class='familleTitre'>".$fam."</div> doit ".$cols["dete"]."€<br />\$formReglement{$cols["idFamille"]}\$</li>");
  
    
    $saisieReglementFormConfig["datePerception"] = $dateReglementAttendu->format("Y-m-d");
    
    ici, il faut populer listRemisesBqActives, avec une option selected si on en trouve une qui correspond à l'utilisateur courant. 

    if (Roles::canAdministratePersonne ()) {
        include 'includes/actions/saisieReglementForm.php';
    }
    
}

?>