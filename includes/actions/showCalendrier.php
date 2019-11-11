<?php
$hideControls = false;
if (array_key_exists ( "hideControls", $ARGS )) {
	$hideControls = ($ARGS ["hideControls"] == "true");
}

$page->appendBody ( file_get_contents ( "includes/html/showCalendar.html" ) );

$currentMonthEnd = mktime ( 23, 59, 0, date ( "n" ), date ( "t" ) );

$dFrom = time ();
$dTo = $currentMonthEnd;

if (array_key_exists ( "from", $ARGS ) && array_key_exists ( "to", $ARGS )) {
	$dFrom = $ARGS ["from"];
	$dTo = $ARGS ["to"];
} else {
	$dFrom = mktime ( 0, 0, 0, date ( "n" ), 1 );
	$dTo = mktime ( 23, 59, 0, date ( "n" ), date ( "t" ) );
}

$format = "d/m/y H:i";
$mdFrom = new MyDateTime ();
$mdFrom->date = $dFrom;
$mdTo = new MyDateTime ();
$mdTo->date = $dTo;
// $page->appendBody($mdFrom->formatLocale("%c")." au ".$mdTo->formatLocale("%c"));

$monthFrom = date ( 'n', $mdFrom->date );
$monthTo = date ( 'n', $mdTo->date );

if ($dTo > $currentMonthEnd) {
	$page->append ( "introduction", "<p>Note : Les rendez-vous sont parfois planifiés longtemps à l’avance, mais ils peuvent être modifiés par la suite. <br /> Pensez à vérifier les informations quelques jours avant la date du rendez-vous.</p>" );
}

/* @var $evenement Evenement */
$evenement = new Evenement ();

$query = "select evt.* from evenement evt join page pg on pg.idPage = evt.fkPage where (( dateDebut BETWEEN '{$mdFrom->format("Y-m-d H:i:s")}' and '{$mdTo->format("Y-m-d H:i:s")}' ) 
	or ( dateFin BETWEEN '{$mdFrom->format("Y-m-d H:i:s")}' and '{$mdTo->format("Y-m-d H:i:s")}' )) ";

$catLink = "";

/* @var $cat Categorie */
if (array_key_exists ( "categorie", $ARGS )) {
	$cat = new Categorie ( $ARGS ["categorie"] );
	$page->setCategorie ( $cat );
	$title = "Calendrier de la section '{$cat->nom}' ";
	$query .= " and fkCategorie = {$ARGS["categorie"]}";
	$catLink = "&categorie=" . $ARGS ["categorie"];
} else {
	
	$title = "Calendrier complet ";
}

$query .= " and " . Page::getSqlFilterForPagePublicationState ();
$query .= " order by dateDebut asc";
$listEvt = $evenement->getObjectListFromQuery ( $query );

if (sizeof ( $listEvt ) > 0) {
	
	$tag = "listeElements";
	
	$displayCategorie = true;
	
	foreach ( $listEvt as $evenement ) {
		/* @var $evenement Evenement */
		
		include 'includes/actions/showEvenementInList.php';
	}
} else {
	
	if ($dTo > time ()) {
		
		if ($monthFrom != $monthTo) {
			$page->append ( "introduction", "<p>Il n'y a rien de prévu de {$mdFrom->formatLocale("%B %Y")} à {$mdTo->formatLocale("%B %Y")}.</p>" );
		} else {
			$page->append ( "introduction", "<p>Il n'y a rien de prévu en {$mdFrom->formatLocale("%B %Y")}.</p>" );
		}
	} else {
		
		if ($monthFrom != $monthTo) {
			$page->append ( "introduction", "<p>Il s'est rien passé de {$mdFrom->formatLocale("%B %Y")} à {$mdTo->formatLocale("%B %Y")}.</p>" );
		} else {
			$page->append ( "introduction", "<p>Il s'est rien passé en {$mdFrom->formatLocale("%B %Y")}.</p>" );
		}
	}
}

if (! $hideControls) {
	
	$title .= " de " . $mdFrom->formatLocale ( "%B %Y" );
	
	if ($monthFrom != $monthTo) {
		$title .= " à " . $mdTo->formatLocale ( "%B %Y" );
	}
	
	$page->setTitle ( $title );
	$page->asset ( "listTitle", $title );
	
	// Préparation des boutons précédant et suivant pour voir ce qu'il y a dans les mois précédants / suivants
	
	$currentMonthNumber = date ( 'm', $dFrom );
	$currentYear = date ( 'y', $dFrom );
	
	$nextMontNumber = $currentMonthNumber + 1;
	$nextYearNumber = $currentYear;
	
	if ($currentMonthNumber == 12) {
		$nextMontNumber = 1;
		$nextYearNumber += 1;
	}
	
	$mdNextMonthFrom = new MyDateTime ();
	$mdNextMonthFrom->date = mktime ( 0, 0, 0, $nextMontNumber, 1, $nextYearNumber );
	;
	
	$mdNextMonthTo = new MyDateTime ();
	$mdNextMonthTo->date = mktime ( 23, 59, 0, $nextMontNumber, date ( 't', $mdNextMonthFrom->date ), $nextYearNumber );
	
	$sqlAfter = "select count(*) as 'count' from evenement evt join page on idPage = fkPage where dateDebut > '{$mdNextMonthFrom->format("Y-m-d H:i:s")}' and " . Page::getSqlFilterForPagePublicationState ();
	$countAfter = $evenement->getDataFromQuery ( $sqlAfter );
	$countAfter = $countAfter [0] ["count"];
	
	// $page->appendBody("Next Month : ".$mdNextMonthFrom->format($format)." au ".$mdNextMonthTo->format($format));
	
	$prevMontNumber = $currentMonthNumber - 1;
	$prevYearNumer = $currentYear;
	
	if ($currentMonthNumber == 1) {
		$prevMontNumber = 12;
		$prevYearNumer -= 1;
	}
	
	$mdPrevMonthFrom = new MyDateTime ();
	$mdPrevMonthFrom->date = mktime ( 0, 0, 0, $prevMontNumber, 1, $prevYearNumer );
	
	$mdPrevMonthTo = new MyDateTime ();
	$mdPrevMonthTo->date = mktime ( 23, 59, 0, $prevMontNumber, date ( 't', $mdPrevMonthFrom->date ), $prevYearNumer );
	
	$sqlPrevious = "select count(*) as 'count' from evenement evt join page on idPage = fkPage where dateFin < '{$mdPrevMonthTo->format("Y-m-d H:i:s")}' and " . Page::getSqlFilterForPagePublicationState ();
	$countPrevious = $evenement->getDataFromQuery ( $sqlPrevious );
	$countPrevious = $countPrevious [0] ["count"];
	// $page->appendBody("Prev Month : ".$mdPrevMonthFrom->format($format)." au ".$mdPrevMonthTo->format($format));
	
	if ($countPrevious > 0) {
		$page->append ( "prevNext", "<div class='pageAroundLink pageAroundLinkLeft'>
				<a href='index.php?show&class=Calendrier&from={$mdPrevMonthFrom->date}&to={$mdPrevMonthTo->date}{$catLink}'>
				<i class='fa fa-arrow-left'></i> {$mdPrevMonthFrom->formatLocale("%B %Y")}
		</a></div>" );
	} else {
		$page->append ( "prevNext", "<div class='pageAroundLink pageAroundLinkLeft'><i>Plus loin, il n'y a rien.</i></div>" );
	}
	
	if ($countAfter > 0) {
		$page->append ( "prevNext", "<div class='pageAroundLink pageAroundLinkRight'>
				<a href='index.php?show&class=Calendrier&from={$mdNextMonthFrom->date}&to={$mdNextMonthTo->date}{$catLink}'>
				{$mdNextMonthFrom->formatLocale("%B %Y")} <i class='fa fa-arrow-right'></i>
		</a></div>" );
	} else {
		$page->append ( "prevNext", "<div class='pageAroundLink pageAroundLinkRight'><i>C'est tout, pour le moment.</i></div>" );
	}
	
	$thisYearSeparation = new MyDateTime ();
	$thisYearSeparation->date = mktime ( 23, 59, 59, 8, 31 );
	
	$sqlStateFilter = Page::getSqlFilterForPagePublicationState ();
	$sql = "SELECT DISTINCT(YEAR(  `dateDebut` ) ) as 'year' FROM evenement join page on fkPage = idPage where {$sqlStateFilter}
			UNION 
			select YEAR(  `dateDebut` )+1 as 'year' from evenement join page on fkPage = idPage 
			where dateDebut > '{$thisYearSeparation->format("Y-m-d H:i:s")}'  
			and {$sqlStateFilter} 
			order by year asc";
	$yearRange = Persistant::getDataFromQuery ( $sql );
	
	$page->append ( "saisonBar", '<div class="calendarYearNavigationItem" style="text-align: center;">
		<p>Navigation par saison : </p>
		$saisonBar$
		</div>' );
	
	foreach ( $yearRange as $aYearRow ) {
		/*
		 * $year = $aYearRow["year"];
		 * $from = new MyDateTime();
		 * $to = new MyDateTime();
		 * $from->date = mktime(0, 0, 0, 1, 1, $year);
		 * $to->date = mktime(23, 59, 59, 12, 31, $year);
		 * $yearLink="index.php?show&class=Calendrier&from={$from->date}&to={$to->date}{$catLink}";
		 * $yearBar.="<a href='{$yearLink}'>{$year}</a> ";
		 */
		
		$year = $aYearRow ["year"];
		$from = new MyDateTime ();
		$to = new MyDateTime ();
		$from->date = mktime ( 0, 0, 0, 9, 1, $year - 1 );
		$to->date = mktime ( 23, 59, 59, 8, 31, $year );
		$yearLink = "index.php?show&class=Calendrier&from={$from->date}&to={$to->date}{$catLink}";
		$yearPrev = $year - 1;
		$page->append ( "saisonBar", "<a href='{$yearLink}' class='calendarYearNavigationItem'>{$yearPrev}/{$year}</a> " );
	}
}

if ($hideControls) {
	$page->appendBody ( "<a href='index.php?show&class=Calendrier'>Voir tous les rendez-vous</a>" );
}


