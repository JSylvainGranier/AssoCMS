<?php
$page->setTitle ( "Accueil" );
$page->appendBody ( file_get_contents ( "includes/html/home.html" ) );

$defaultText = "<p>Ce site est en construction.</p><p><i>Pensez à créer un les paramètres HOME_TITLE et HOME_TEXT pour configurer la page d'accueil.</i></p>";

$homeTitle = Param::getValue ( PKeys::$HOME_TITLE, "Bienvenue sur le site de l'association " . SITE_TITLE . " !" );
$homeText = Param::getValue ( PKeys::$HOME_TEXT, $defaultText );
$homeActivityDays = Param::getValue ( PKeys::$HOME_ACTIVITY_NBJOURS, 5 );

if (Roles::isGestionnaireGlobal ()) {
    $homeText .= "<a class='penEditor' href='index.php?edit&class=param&id=HOME_TEXT'><i class='fa fa-pencil' aria-hidden='true'></i></a> ";
    $homeTitle .= "<a class='penEditor' href='index.php?edit&class=param&id=HOME_TITLE'><i class='fa fa-pencil' aria-hidden='true'></i></a> ";
}


$page->asset ( "title", $homeTitle );
$page->asset ( "text", $homeText );
$page->asset ( "days", $homeActivityDays );

$dateObj = new MyDateTime ();
$dateObj->date = time () - 60 * 60 * 24 * $homeActivityDays;
$date = $dateObj->format ( "Y-m-d" );
$sql = "select * from ( 
				select idEvenement as \"id\", 'evenement' as \"class\", evt.lastUpdateOn from evenement evt join page on page.idPage = evt.fkPage where evt.lastUpdateOn > '{$date}' and etat > 14 and evt.dateFin >= now()
				union
				select idPage as \"id\", 'page' as \"class\", lastUpdateOn from page where lastUpdateOn > '{$date}' and isSubClass = false and etat > 14
				union
				select idReaction as \"id\", 'reaction' as \"class\", lastUpdateOn from reaction where lastUpdateOn  > '{$date}' group by fkPage
			) as dummy
			order by dummy.lastUpdateOn desc limit 10";

$array = Persistant::getDataFromQuery ( $sql );
$count = count ( $array );

$cacheFileName = "documents/cache/home/" . $count;
$cacheFileLastUpdate = file_exists ( $cacheFileName ) ? filemtime ( $cacheFileName ) : 0;
$cacheUsable = (time () - $cacheFileLastUpdate <= 60 * 60 * 24);
if ($cacheUsable) {
	$html = file_get_contents ( $cacheFileName );
} else {
	$html = "";
	
	if ($count > 0) {
		$html .= "<ul>";
		
		$evt = new Evenement ();
		$reac = new Reaction ();
		$pg = new Page ();
		$attchment = new Attachment ();
		
		foreach ( $array as $aLine ) {
			$class = $aLine ["class"];
			$id = $aLine ["id"];
			
			$lim = "";
			
			switch ($class) {
				case "evenement" :
					$evt = $evt->findById ( $id );
					$files = $evt->getPage()->getAttachmentPreciInLink();
					$lim = "Mise à jour de '<a href='index.php?show&class=Evenement&id={$id}'>{$evt->getPage()->titre}{$files}</a>'";
					if ($evt->annule) {
						$lim .= " : <strong>CE RENDEZ-VOUS EST ANNULÉ !</strong>";
					}
					break;
				case "page" :
					$pg = $pg->findById ( $id );
					$files = $pg->getAttachmentPreciInLink();
					$lim = "Mise à jour de '<a href='index.php?show&class=Page&id={$id}'>{$pg->titre}{$files}</a>'";
					break;
				case "reaction" :
					$reac = $reac->findById ( $id );
					if ($reac->getPage ()->isSubClass) {
						$evt = $evt->findByPageId ( $reac->getPage ()->getPrimaryKey () );
						$lim = "Réaction(s) au sujet de '<a href='index.php?show&class=Evenement&id={$evt->getPrimaryKey()}'>{$reac->getPage()->titre}</a>'";
					} else {
						$lim = "Réaction(s) au sujet de '<a href='index.php?show&class=Page&id={$reac->getPage()->getPrimaryKey()}'>{$reac->getPage()->titre}</a>'";
					}
					break;
			}
			
			$html .= "<li>{$lim}</li>\n";
		}
		$html .= "</ul>";
	} else {
		$html = "<p>Rien. Le Désert.</p>";
	}
	
	$oldCacheFiles = glob ( 'documents/cache/home/*' );
	if (is_array ( $oldCacheFiles )) {
		foreach ( $oldCacheFiles as $file ) {
			if (is_file ( $file ))
				unlink ( $file );
		}
	}
	
	file_put_contents ( $cacheFileName, $html );
}

$page->appendBody ( $html );


$page->appendBody ( "<h2>Les rendez-vous des {$homeActivityDays} jours à venir </h2>" );
$dFrom = strtotime("today");
$dTo = strtotime("+{$homeActivityDays} day");

$ACTIONS [] = array (
		"show",
		"class" => "Calendrier",
		"hideControls" => "true",
		"from" => $dFrom,
		"to" => $dTo
);

?>