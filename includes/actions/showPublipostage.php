<?php
try {
	$publipostage = new Publipostage ( $ARGS ["id"] );
	
	$page->appendBody ( file_get_contents ( "includes/html/showPublipostage.html" ) );
	
	$page->asset ( "objet", $publipostage->objet );
	$page->asset ( "message", $publipostage->message );
	
	$page->setTitle ( $publipostage->objet );
	
	$pubDestDao = new PublipostageDestinataire ();
	$destinataires = $pubDestDao->getAllForPublipostage ( $publipostage->getPrimaryKey () );
	
	$total = 0;
	$totalEmail = 0;
	$totalCourrier = 0;
	$totalBoth = 0;
	$listNothing = array ();
	
	foreach ( $destinataires as $aDestinataire ) {
		/* @var $aDestinataire PublipostageDestinataire */
		$total ++;
		
		if (is_null ( $aDestinataire->getDestinataire () )) {
			$totalCourrier ++;
			continue;
		}
		
		$paper = false;
		$mail = false;
		
		if ($aDestinataire->getDestinataire ()->wantPaperRecap) {
			$paper = true;
		}
		
		if (strlen ( $aDestinataire->getDestinataire ()->email ) > 4) {
			$mail = true;
		}
		
		if ($paper && $mail) {
			$totalBoth ++;
		} else if ($paper) {
			$totalCourrier ++;
		} else if ($mail) {
			$totalEmail ++;
		} else {
			$listNothing [] = $aDestinataire;
		}
	}
	
	$page->asset ( "total", $total );
	$page->asset ( "totalEmail", $totalEmail );
	$page->asset ( "totalMail", $totalCourrier );
	$page->asset ( "totalBoth", $totalBoth );
	
	$countListNothing = count ( $listNothing );
	
	if ($countListNothing > 0) {
		$html = "<li> {$countListNothing} personnes dont l'adresse email n'est pas renseignée et qui n'ont pas demandé de récapitulatif papier :<ul>";
		
		foreach ( $listNothing as $aDestinataire ) {
			
			if (is_null ( $aDestinataire->getDestinataire () )) {
				$html .= "<li><i>Sans destinataire</i></li>";
			} else {
				$html .= "<li><a href='index.php?show&class=Personne&id={$aDestinataire->getDestinataire()->getPrimaryKey()}'>{$aDestinataire->getDestinataire()->getNomPrenom()}</a></li>";
			}
		}
		
		$html .= "</ul></li>";
		
		$page->asset ( "noContactMode", $html );
	}
	
	$page->asset ( "id", $publipostage->getPrimaryKey () );
	$page->asset ( "sentByMail", $publipostage->sentByMail ? "Oui" : "Non" );
	$page->asset ( "sentByEmail", $publipostage->sentByEmail ? "Oui" : "Non" );
	
	$page->appendActionButton ( "Supprimer", "delete&class=Publipostage&id=" . $publipostage->getPrimaryKey () );
} catch ( NoExistOnDbException $e ) {
	$page->appendBody ( "Vous avez demandé le publipostage n°" . $ARGS ["id"] . ", mais il n'existe pas !" );
	$page->setTitle ( "Oups ! 404 !" );
}

?>