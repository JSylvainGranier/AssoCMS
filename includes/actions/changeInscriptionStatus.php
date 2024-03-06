<?php 
$page->setStandardOuputDisabled ( true );

// Takes raw data from the request
$json = file_get_contents('php://input');
// Converts it into a PHP object
$data = json_decode($json);



$rep = array();


$inscription = new Inscription($data->idInscription);
$newStatus = $data->statutCible;

$rep = $inscription->changerStatut($newStatus);

if (isPhpUp ()) {
    echo json_encode ( $rep );
} else {
    echo json_encode ( $rep, JSON_UNESCAPED_UNICODE );
}


?>