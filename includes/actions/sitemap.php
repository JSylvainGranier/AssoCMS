<?php
header ( 'Content-Type: text/xml; charset=utf-8' );
$page->setStandardOuputDisabled ( true );
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"> 

<?php
$locs = array ();
$root = new SiteMapUrl ( "" );
$locs [] = $root;

$catRoot = new Categorie ();
$pageRoot = new Page ();
$evtRoot = new Evenement ();
$catList = $catRoot->getAll ();

foreach ( $catList as $aCat ) {
	/* @var $aCat Categorie */
	$uUrl = new SiteMapUrl ( "?list&amp;class=Page&amp;categorie=" . $aCat->getPrimaryKey () );
	$uUrl->lastmod = $aCat->getLastUpdateOn ();
	$locs [] = $uUrl;
	
	$pageList = $pageRoot->getFilteredInCategorie ( $aCat->getPrimaryKey (), false );
	
	foreach ( $pageList as $aPage ) {
		/* @var $aPage Page */
		$uUrl = new SiteMapUrl ( "?show&amp;class=Page&amp;id=" . $aPage->getPrimaryKey () );
		$uUrl->lastmod = $aPage->getLastUpdateOn ();
		$locs [] = $uUrl;
	}
}
$evtQuery = "select * from evenement evt join page pg on evt.fkPage = pg.idPage where pg.etat >= 50 ";
$evtList = $evtRoot->getObjectListFromQuery ( $evtQuery );

foreach ( $evtList as $aEvt ) {
	/* @var $aEvt Evenement */
	$uUrl = new SiteMapUrl ( "?show&amp;class=Evenement&amp;id=" . $aEvt->getPrimaryKey () );
	$uUrl->lastmod = $aEvt->getLastUpdateOn ();
	$locs [] = $uUrl;
}

foreach ( $locs as $aLoc ) {
	echo $aLoc->format ();
}
?> 
  
</urlset>

