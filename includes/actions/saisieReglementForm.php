<?php

if(isset($saisieReglementFormConfig) && array_key_exists("targetTag", $saisieReglementFormConfig)){
    $page->append( $saisieReglementFormConfig["targetTag"], file_get_contents ( "includes/html/saisieReglementForm.html" ) );
} else {
    $page->appendBody ( file_get_contents ( "includes/html/saisieReglementForm.html" ) );
}

foreach($saisieReglementFormConfig as $key => $value){
    $page->asset($key, $value);
}


//idFamille
//$saisieReglementFormConfig["montant"] = $montantADate;
//                $saisieReglementFormConfig["datePerception"] = $kDate;




?>