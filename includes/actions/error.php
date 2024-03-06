<?php
$errCode = $ARGS ["error"];

if ($errCode == 500) {
	throw new HttpException ( "Erreur 500 ! Le serveur c'est perdu en cours de route !" );
} else if ($errCode == 403) {
	throw new HttpException ( "Erreur 403 ! Vous n'avez pas le droit d'accéder à la ressource que vous avez demandé." );
} else if ($errCode == 404) {
	$originalPage = $_SERVER["REDIRECT_URL"];
	throw new HttpException ( "Erreur 404 ! La ressource que vous avez demandé ('".$originalPage."') n'existe pas." );
}

?>