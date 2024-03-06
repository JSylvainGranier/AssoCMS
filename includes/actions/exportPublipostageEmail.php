<?php
$to = "";
$bcc = "";

$nbMailsSent = 0;

$desaboPreamb = "<p>Vous recevez ce message car vous faites partie de l'association ".SITE_TITLE." et que vous nous avez demandé de recevoir les informations par email.</p>";


if ($isTest) {
	$to = $_SESSION ["userLogin"];
	$nbMailsSent ++;
	$sent = sendSimpleMail ( "[" . SITE_TITLE . "] " . $publipostage->objet, $publipostage->message, $to, false );
	
	$page->appendNotification ( + $nbMailsSent . " eMail de test envoyé. Vous le recevrez sous 5 minutes." );
} else {
	$pubDest = new PublipostageDestinataire ();
	$pubDest = $pubDest->getAllForPublipostage ( $publipostage->getPrimaryKey () );
	
	foreach ( $pubDest as $aDestinataire ) {
		/* @var $aDestinataire PublipostageDestinataire */
		
		$dest = $aDestinataire->getDestinataire();
		
		if(!$dest->allowEmails){
			continue;
		}
		
		$email = $dest->email;
		
		if (strlen ( $email ) > 4) {
			$token = md5($dest->email);
			$link = SITE_ROOT."index.php?unsuscribe=".$token;
			$desabo = "<p>Vous préférez ne plus recevoir d'email ? <a href=".$link.">Cliquez ici.</a> </p>";

			////SELECT email, md5(concat(idPersonne, email, passwordHash)) FROM `personne` WHERE 1
			$direckLink = md5($dest->idPersonne.$dest->email.$dest->passwordHash);
			$direckLink = SITE_ROOT."index.php?login&phase=connectByLink&link=".$direckLink;

			$autolink = "<p style='font-family : italic; background-color : #FFAC24; color : #004B91; padding : 5px;'>Vous retrouverez toutes ces informations sur le site de l'association, y compris la possibilité de vous inscrire lorsque cela est nécessaire. <br/>
			Votre mot de passe personnel vous sera demandé pour accéder à toutes les fonctionnalités du site.  <br />
			<a href='{$direckLink}'>Mais si vous l'avez perdu, cliquez simplement ici. </a> <br /> Vous arriverez directement connecté à votre compte.</p>";
			
			$content = $publipostage->message.$autolink.$desaboPreamb.$desabo;
			
			sendSimpleMail ( "[" . SITE_TITLE . "] " . $publipostage->objet, $content, $email, false );
			
			$nbMailsSent ++;
		}
	}
	
	$to = $_SESSION ["userLogin"];
	$publipostage->sentByEmail = true;
	$publipostage->save ();
	
	if ($nbMailsSent < 2) {
		$page->appendNotification ( + $nbMailsSent . " eMail préparé. Il sera envoyé d'ici 5 minutes." );
	} else {
		$page->appendNotification ( + $nbMailsSent . " eMails préparés. Ils commenceront à être envoyés d'ici 5 minutes." );
	}
}

$ACTIONS [] = array (
		"show",
		"class" => "Publipostage",
		"id" => $publipostage->getPrimaryKey () 
);

?>