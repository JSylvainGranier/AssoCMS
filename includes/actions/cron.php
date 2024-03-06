<?php 

$cronMinute = $ARGS["minute"];


if($cronMinute == "30"){
    $iscp = new Inscription();
    $iscp->clearOldBrouillons();
    
}

?>