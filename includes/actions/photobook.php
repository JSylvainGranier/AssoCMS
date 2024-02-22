<?php 

$page->appendBody("<h1>Album Photo</h1>");

$page->appendBody("<p>Cette page regroupe tous les rendez-vous où des photos ont été prises.</p>");
$page->appendBody("<p>Chaque rendez-vous comporte une indication sur le nombre de photo : '33 <i class='fa fa-camera'></i>, 1<i class='fa fa-video-camera'></i>' correspond à 33 photos et une vidéo. </p>");

$page->appendBody("<p>Bon visionnage ;-)</p>");

$page->appendBody("<p><a href='index.php?photobookUpload'>Envoyer mes photos</a></p>");


$page->setTitle("Album Photo");


$evtp = new Evenement();
$evtList = $evtp->getEvenementsForPhotoBook();


$cacheFileName = "documents/cache/photobook.html";
$cacheFileLastUpdate = file_exists ( $cacheFileName ) ? filemtime ( $cacheFileName ) : 0;
$cacheUsable = (time () - $cacheFileLastUpdate <= 60 * 60 * 2);
if ($cacheUsable) {
	$html = file_get_contents ( $cacheFileName );
} else {
	$html = "<ul>";
	
	
	foreach ( $evtList as $evenement ) {
	
		if ($evenement->annule) continue;
	
		if ($evenement->getPage ()->etat < PageEtat::$ACCESS_MEMBRE) continue;
	
		$evtTile = $evenement->getPage ()->titre;
	
		$catTitle = $evenement->getPage ()->getCategorieClassement ()->nom;
		$displayCat = "Section '{$catTitle}'";
	
		$evtTileCompl = ucfirst ( $evenement->formatDates ( true ) );
	
		$html.= "<li class='pageListItem'><h4 >{$displayCat} / <a href='{$evenement->getShowURL()}'>{$evtTile}</a></h4>{$evtTileCompl}</br>" ;
	
		$html.= "{$evenement->getReadMoreLink()}</li>" ;
	}
	
	$html.= "</ul>";
	file_put_contents ( $cacheFileName, $html );
}

$page->appendBody($html);


?>