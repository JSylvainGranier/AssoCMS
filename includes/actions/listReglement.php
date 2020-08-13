<?php
$page->append ( "body", file_get_contents ( "includes/html/listReglement.html" ) );
$page->setTitle("Règlements");

$reglement = new Reglement();
$inscription = new Inscription();
$personne = new Personne();

$sql = "select distinct rg.*, p.idFamille from reglement rg join inscription i on i.idInscription = rg.fkInscription join personne p on p.idFamille = i.idFamille where i.etat = 50 order by p.nom, rg.dateEcheance";
$result = $reglement->getDataFromQuery ( $sql );


$idFamilleCurrent = -1;
$now = new MyDateTime();

foreach ($result as $rnum => $cols){
    
    if($idFamilleCurrent != $cols["idFamille"]){
        $fam = "";
        $comma = "";
        foreach($personne->getAllPersonnesInFamily($cols["idFamille"]) as $pers){
            $fam .= $comma.$pers->nom." ".$pers->prenom;
            $comma = ", ";
        }
        $idFamilleCurrent = $cols["idFamille"];
        $page->append("reglements", "<li><div class='familleTitre'>".$fam."</div></li>");
    }
    
    
    $de = new MyDateTime($cols["dateEcheance"]);
    
    $optEspeceSelection = $cols["modePerception"] == "Espèces" ? "selected" : "";
    $optChequeSelection = $cols["modePerception"] == "Chèque" ? "selected" : "";

    $datePerceptionObj = null;
    
    if(!is_null($cols["datePerception"])){
        $datePerceptionObj = MyDateTime::createFromFormat("Y-m-d H:i:s", $cols["datePerception"]);
        $dt =  $datePerceptionObj->format("d/m/Y");
    } else {
        $dt = " ";
    }
    
    $refPerception = is_null($cols["refPerception"])  ? " " : $cols["refPerception"];
    $perceptionFiltre = "";
    if (!is_null($datePerceptionObj)) {
        $perceptionFiltre = "Perçu" ;
    } else if  ($de->date > $now->date) {
        $perceptionFiltre = "Plus tard" ; 
    } else {
        $perceptionFiltre =  "À percevoir dès que possible";
    }
    
    $ligne = "<li>
        <span class='reglementFamilleRappel'>{$fam}</span>
        <span class='perceptionFiltre'>{$perceptionFiltre}</span>
        <div class='reglementLigne'><span class='libelleReglement'>{$cols["libelle"]} </span> <br /><span class='reglementMontant'> {$cols["montant"]}€ pour le {$de->format("d/m/Y")}</span>
        <form class='reglementPerceptionForm' onsubmit='saveReglement(this); return false;' action='' >
            <input type='hidden' name='idReglement' value='{$cols["idReglement"]}' />
            &nbsp;&nbsp;&nbsp;&nbsp; Perçu le : <input type='datetime' placeholder='Date perception' name='datePerception' class='reglementDatePerception' value='{$dt}' />
            <select name='modePerception' >
                <option {$optEspeceSelection}>Espèces</option>
                <option {$optChequeSelection}>Chèque</option>
            </select>
             &nbsp; Ref : <input type='text' placeholder='Reférence' class='reglementRefPerception' name='refPerception' value='{$refPerception}' />
            <button type='submit' ref='{$cols["idReglement"]}'><i class='fa fa-floppy-o' aria-hidden='true'></i></button>
        </form>
        </div></li>";
    
    $page->append("reglements", $ligne);
}

?>