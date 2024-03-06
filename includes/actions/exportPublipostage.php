<?php
$mode = $ARGS ["mode"];
$isTest = ! array_key_exists ( "realExpedition", $ARGS );
$publipostage = new Publipostage ( $ARGS ["id"] + 0 );

if ($mode == "email") {
	include 'includes/actions/exportPublipostageEmail.php';
} else if ($mode == "mail") {
	include 'includes/actions/exportPublipostageMail.php';
} else {
	throw new Exception ( "Le mode d'expédition {$mode} n'existe pas." );
}

?>