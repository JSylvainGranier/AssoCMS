<?php
$evenementToShow = new Evenement ( $ARGS ["id"] );

if (is_null ( $evenementToShow )) {
	$page->appendBody ( "Vous avez demandé la page n°" . $ARGS ["id"] . ", mais elle n'existe pas !" );
	$page->setTitle ( "Oups ! 404 !" );
} else {
	checkEtat ( $evenementToShow->getPage ()->etat );
	
	$page->setCategorie ( $evenementToShow->getPage ()->getCategorieClassement () );
	
	$page->appendBody ( file_get_contents ( "includes/html/showEvenement.html" ) );
	
	$page->asset ( "catName", $evenementToShow->getPage ()->getCategorieClassement ()->nom );
	$page->asset ( "evtTitle", $evenementToShow->getPage ()->getTitre () );
	
	$dateTime = "";
	
	if ($evenementToShow->annule) {
		$dateTime = "<span class='barre'> {$evenementToShow->formatDates()}</span>&nbsp;<strong> Ce rendez-vous est annulé !</strong>";
	} else {
		$dateTime = $evenementToShow->formatDates ( true );
	}
	
	$page->asset ( "dateTime", $dateTime );
	
	$page->asset ( "intro", $evenementToShow->getPage ()->getIntroduction () );
	
	$page->asset ( "details", $evenementToShow->getPage ()->getSuite () );
	
	$page->setTitle ( $evenementToShow->getPage ()->getTitre () );
	
	$strOrg = "";
	
	$organisateur1 = $evenementToShow->getOrganisateur1 ();
	if (! is_null ( $organisateur1 )) {
		$strOrg = "<p>Rendez-vous organisé par <a href='index.php?show&class=Personne&id={$organisateur1->getPrimaryKey()}'>{$organisateur1->prenom}</a>";
	}
	$organisateur2 = $evenementToShow->getOrganisateur2 ();
	if (! is_null ( $organisateur2 )) {
		$strOrg .= " et <a href='index.php?show&class=Personne&id={$organisateur2->getPrimaryKey()}'>{$organisateur2->prenom}</a>";
	}
	$strOrg .= ".</p>";
	
	$page->asset ( "organisation", $strOrg );
	
	// Liens vers l'évènement avant ou après celui-ci
	$evtCatId = $evenementToShow->getPage ()->getCategorieClassement ()->getPrimaryKey ();
	$etatFilter = Page::getSqlFilterForPagePublicationState ();
	$sql = "(select idEvenement, fkPage, 'before' as 'order' from evenement evt join page on page.idPage = evt.fkPage where dateDebut <= {$evenementToShow->toSql($evenementToShow->dateDebut)} and idEvenement <> {$evenementToShow->getPrimaryKey()} and page.fkCategorie = {$evtCatId} and {$etatFilter} order by dateDebut DESC limit 0,1) 
	union (select idEvenement, fkPage, 'after' as 'order' from evenement evt join page on page.idPage = evt.fkPage  where dateDebut >= {$evenementToShow->toSql($evenementToShow->dateDebut)} and idEvenement <> {$evenementToShow->getPrimaryKey()} and page.fkCategorie = {$evtCatId} and {$etatFilter} order by dateDebut ASC limit 0,1)";
	
	$evtRs = Persistant::getDataFromQuery ( $sql );
	$evtRsCount = count ( $evtRs );
	if ($evtRsCount > 0) {
		
		$evtRightRs = null;
		$evtLeftRs = null;
		
		if ($evtRsCount == 2) {
			$evtLeftRs = $evtRs [0];
			$evtRightRs = $evtRs [1];
		} else {
			if ($evtRs [0] ["order"] == "before") {
				$evtLeftRs = $evtRs [0];
			} else {
				$evtRightRs = $evtRs [0];
			}
		}
		
		if ($evtLeftRs != null) {
			$pageLeft = new Page ( $evtLeftRs ["fkPage"] + 0 );
			$idEvtLeft = $evtLeftRs ["idEvenement"];
			$page->append ( "prevNext", "<div class='pageAroundLink pageAroundLinkLeft'>
					<a href='index.php?show&class=Evenement&id={$idEvtLeft}'>
						<i class='fa fa-arrow-left'></i> {$pageLeft->titre}
					</a></div>" );
		}
		
		if ($evtRightRs != null) {
			$pageRight = new Page ( $evtRightRs ["fkPage"] + 0 );
			$idEvtRight = $evtRightRs ["idEvenement"];
			$page->append ( "prevNext", "<div class='pageAroundLink pageAroundLinkRight'>
					<a href='index.php?show&class=Evenement&id={$idEvtRight}'>
						{$pageRight->titre} <i class='fa fa-arrow-right'></i>
					</a></div>" );
		}
	}
	
	if (Roles::isGestionnaireCategorie ( $evenementToShow->getPage ()->getCategorieClassement () ) || ($evenementToShow->getOrganisateur1 () != null && $evenementToShow->getOrganisateur1 ()->getPrimaryKey () == thisUserId()) || ($evenementToShow->getOrganisateur2 () != null && $evenementToShow->getOrganisateur2 ()->getPrimaryKey () == thisUserId())) {
		$page->appendEditionBar ( "Evenement", $evenementToShow->getPrimaryKey () );
		if (! $evenementToShow->annule) {
			$page->appendActionButton ( "Annuler le rendez-vous", "cancelEvenement&id={$evenementToShow->getPrimaryKey()}&target=cancel" );
		} else {
			$page->appendActionButton ( "Rétablir le rendez-vous", "cancelEvenement&id={$evenementToShow->getPrimaryKey()}&target=restaure" );
		}
	}
	
	$pageToShow = $evenementToShow->getPage ();
	$ACTIONS [] = array (
			"listAttachment" 
	);
	
	if ($evenementToShow->getPage ()->allowReactions && $evenementToShow->getPage ()->etat > PageEtat::$BROUILLON) {
		$ACTIONS [] = array (
				"reaction",
				"id" => $evenementToShow->getPage ()->getPrimaryKey () 
		);
	}
}

?>