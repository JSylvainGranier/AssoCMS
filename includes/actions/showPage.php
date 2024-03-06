<?php
try {
	$pageToShow = new Page ( $ARGS ["id"] );
	
	checkEtat ( $pageToShow->etat );
	
	$page->setCategorie ( $pageToShow->getCategorieClassement () );
	
	$page->appendBody ( file_get_contents ( "includes/html/showPage.html" ) );
	
	$page->asset ( "catName", $pageToShow->getCategorieClassement ()->nom );
	$page->asset ( "pageTitle", $pageToShow->getTitre () );
	
	$page->asset ( "intro", $pageToShow->getIntroduction () );
	$page->asset ( "details", $pageToShow->getSuite () );
	
	$page->setTitle ( $pageToShow->getTitre () );
	
	// Liens vers l'évènement avant ou après celui-ci
	$pageCatId = $pageToShow->getCategorieClassement ()->getPrimaryKey ();
	$etatFilter = Page::getSqlFilterForPagePublicationState ();
	$pageOrdreBefore = " ( 1 = 1 )";
	$pageOrdreAfter = $pageOrdreBefore;
	if (! is_null ( $pageToShow->ordre )) {
		$pageOrdreBefore = " ordre < {$pageToShow->ordre}";
		$pageOrdreAfter = " ordre > {$pageToShow->ordre}";
	}
	
	$sql = "(select idPage, 'before' as 'sequ' from page where isSubClass = false and idPage <> {$pageToShow->getPrimaryKey()} and fkCategorie = {$pageCatId} and {$pageOrdreBefore} and {$etatFilter} order by ordre, lastUpdateOn DESC limit 0,1)
	union (select idPage, 'after' as 'sequ' from page where isSubClass = false and idPage <> {$pageToShow->getPrimaryKey()} and fkCategorie = {$pageCatId} and {$pageOrdreAfter} and {$etatFilter} order by ordre, lastUpdateOn ASC limit 0,1)";
	
	$pageRs = Persistant::getDataFromQuery ( $sql );
	$pageRsCount = count ( $pageRs );
	
	if ($pageRsCount > 0) {
		
		$pageRightRs = null;
		$pageLeftRs = null;
		
		if ($pageRsCount == 2) {
			$pageLeftRs = $pageRs [0];
			$pageRightRs = $pageRs [1];
		} else {
			if ($pageRs [0] ["sequ"] == "before") {
				$pageLeftRs = $pageRs [0];
			} else {
				$pageRightRs = $pageRs [0];
			}
		}
		
		if ($pageLeftRs != null) {
			$pageLeft = new Page ( $pageLeftRs ["idPage"] + 0 );
			$page->append ( "prevNext", "<div class='pageAroundLink pageAroundLinkLeft'>
				<a href='index.php?show&class=Page&id={$pageLeft->getPrimaryKey()}'>
				<i class='fa fa-arrow-left'></i> {$pageLeft->titre}
				</a></div>" );
		}
		
		if ($pageRightRs != null) {
			$pageRight = new Page ( $pageRightRs ["idPage"] + 0 );
			$page->append ( "prevNext", "<div class='pageAroundLink pageAroundLinkRight'>
				<a href='index.php?show&class=Page&id={$pageRight->getPrimaryKey()}'>
				{$pageRight->titre} <i class='fa fa-arrow-right'></i>
				</a> </div>" );
		}
	}
	
	if (Roles::isGestionnaireCategorie ( $pageToShow->getCategorieClassement () ) || $pageToShow->getAuteur ()->getPrimaryKey () == getUserId ()) {
		$page->appendEditionBar ( "Page", $pageToShow->getPrimaryKey () );
	}
	
	$ACTIONS [] = array (
			"listAttachment" 
	);
	
	if ($pageToShow->allowReactions && $pageToShow->etat > PageEtat::$BROUILLON) {
		$ACTIONS [] = array (
				"reaction",
				"id" => $pageToShow->getPrimaryKey () 
		);
	}
} catch ( NoExistOnDbException $e ) {
	$page->appendBody ( "Vous avez demandé la page n°" . $ARGS ["id"] . ", mais elle n'existe pas !" );
	$page->setTitle ( "Oups ! 404 !" );
}

?>