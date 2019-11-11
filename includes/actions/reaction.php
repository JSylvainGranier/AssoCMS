<?php
if (Roles::isMembre ()) {
	$page->appendBody ( file_get_contents ( "includes/html/reactionModule.html" ) );
	
	$reacRoot = new Reaction ();
	
	$reacList = $reacRoot->findListForPage ( $ARGS ['id'] + 0 );
	
	$reactionsHtml = "";
	
	$DLimiteDateExacte = 7200; // 5 jours;
	
	if (count ( $reacList ) > 0) {
		/* @var $aReaction Reaction */
		foreach ( $reacList as $aReaction ) {
			$dateMessage = $aReaction->dateRedaction->format ( "U" );
			$dateMaintenant = new MyDateTime ();
			$dateMaintenant = $dateMaintenant->format ( "U" );
			
			$minutes = round ( abs ( $dateMaintenant - $dateMessage ) / 60, 0 );
			$editionBtn = "";
			if ($aReaction->getAuteur ()->getPrimaryKey () == thisUserId() && $minutes <= 15) {
				
				$editionBtn = "<br/><button onclick='javascript:editReaction({$aReaction->getPrimaryKey()});' >Corriger</button>";
			}
			
			if ($minutes > $DLimiteDateExacte) {
				$indicationTemporelle = "le " . $aReaction->dateRedaction->format ( "d/m/y à H:i" );
			} else if ($minutes > 1440) {
				$j = round ( $minutes / 1440, 0 );
				$s = ($j > 1) ? "s" : "";
				$indicationTemporelle = " il y a {$j} jour{$s}";
			} else if ($minutes > 60) {
				$h = round ( $minutes / 60, 0 );
				$s = ($h > 1) ? "s" : "";
				$indicationTemporelle = " il y a {$h} heure{$s}";
			} else if ($minutes > 5) {
				$indicationTemporelle = " il y a {$minutes} minutes";
			} else {
				$indicationTemporelle = " à l'instant";
			}
			$auteur = $aReaction->getAuteur ();
			$html = "<div class='reactionContainer'><span class='reactionHeader'><a href='index.php?show&class=Personne&id={$auteur->getPrimaryKey()}'>{$auteur->prenom}</a>, {$indicationTemporelle} : </span><span id='reactionId{$aReaction->getPrimaryKey()}'>{$aReaction->message}</span>{$editionBtn}</div>\n";
			
			$reactionsHtml .= $html;
		}
	} else {
		$reactionsHtml = "Aucune réaction pour le moment.";
	}
	
	$page->asset ( "reactionList", $reactionsHtml );
	$page->asset ( "idPage", $ARGS ['id'] );
	$page->asset ( "idAuteur", thisUserId() );
}

?>