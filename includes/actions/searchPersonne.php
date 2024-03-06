<?php 

$page->setStandardOuputDisabled ( true );

$q = $ARGS["q"];

$personne = new Personne();

$jsonConfig = array();

foreach($personne->search($q) as $aPersonne){
    $jsonConfig[] = array (
        "nom" => $aPersonne->nom,
        "prenom" => $aPersonne->prenom,
        "idPersonne" => $aPersonne->idPersonne,
        "email" => $aPersonne->email,
        "ville" => $aPersonne->adrVille
    );
}



if (isPhpUp ()) {
    echo json_encode ( $jsonConfig ) ;
} else {
    echo json_encode ( $jsonConfig, JSON_UNESCAPED_UNICODE ) ;
}

?>