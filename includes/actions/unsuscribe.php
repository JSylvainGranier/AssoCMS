<?php

$sql = "select * from personne where md5(email) = '{$ARGS["unsuscribe"]}'";
$pers = new Personne();
$personnes = $pers->getObjectListFromQuery($sql);

if(count($personnes) == 1){
	if($ARGS["confirm"] == "true"){
	
		$pers = $personnes[0];
		$pers->allowEmails = false;
		$pers->save();
		$txt = "Très bien {$pers->prenom}, vous ne recevrez plus d'email pour vous informer de l'actualité de ".SITE_TITLE;
		$txt .= "<br />Si vous changez d'avis, <a href='index.php?edit&class=Personne'>modifiez les options de votre profil.</a>";
	} else {
		$url = "index.php?unsuscribe=".$ARGS["unsuscribe"]."&confirm=true";
		$url = "<br/><a href='{$url}'>Oui</a>&nbsp;&nbsp;&nbsp;<a href='index.php'>Non</a>";
		$txt = "Souhaitez-vous vraiment ne plus recevoir d'eMails d'information de la part de l'association ?".$url ;
	}
	
} else {
	$txt = "Impossible de trouver une correspondance...";
	$txt .= "<br />Mais vous pouvez faire la même chose en <a href='index.php?edit&class=Personne'>modifiant vous-même les options de votre profil.</a>";
	
}


$page->appendNotification ( $txt );

$ARGS ["redirectAction"] = "home";