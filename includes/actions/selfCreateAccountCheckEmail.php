<?php
$page->setStandardOuputDisabled ( true );

$pers = new Personne();


// Takes raw data from the request
$json = file_get_contents('php://input');
// Converts it into a PHP object
$data = json_decode($json);

$pers = $pers->findByEmail($data->login);



if(is_null($pers)){
    echo '{"available":true}';
} else {
    echo '{"available":false}';
}
