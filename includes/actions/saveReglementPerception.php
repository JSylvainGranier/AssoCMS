<?php 
$page->setStandardOuputDisabled ( true );

// Takes raw data from the request
$json = file_get_contents('php://input');
// Converts it into a PHP object
$data = json_decode($json);

$rep = array();

try {
    $reglement = new Reglement($data->idReglement);
    
    $reglement->refPerception = $data->refPerception;
    $reglement->modePerception = $data->modePerception;
    
    if(isset($data->datePerception) && strlen($data->datePerception) > 6){
        try {
            $reglement->datePerception = MyDateTime::createFromFormat("d/m/Y H:i", $data->datePerception." 00:00" );
        } catch (Exception $e){
            $rep["error"] = "Le format de la date n'a pas pu être interprêté. Renseigné : '{$data->datePerception}', attendu au format 'jj/mm/aaaa'.";
        }
    } else {
        $reglement->datePerception = null;
    }
    
    
    
    $reglement->save();
    
    $rep["idReglement"] = $reglement->idReglement;
    
} catch (Exception $e){
    $rep = array();
    $rep["error"] = $e;
}





if (isPhpUp ()) {
    $jsToReturn = json_encode ( $rep );
} else {
    $jsToReturn = json_encode ( $rep, JSON_UNESCAPED_UNICODE );
}

echo $jsToReturn;


?>