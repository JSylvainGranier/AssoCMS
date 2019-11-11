<?php
$results = array ();
class ReturnRow {
	public $id;
	public $class;
	public $listTitle;
	public $title;
	public $longDate;
	public $htmlTitle;
	public $introduction;
}

$kind = $ARGS ["kind"];
$cat = $ARGS ["cat"];

if ($cat > 0) {
	$cat = " and fkCategorie = {$cat} ";
} else {
	$cat = "";
}
if (strlen ( $kind ) > 0) {
	if (is_int ( strpos ( $kind, "R" ) )) {
		$sqlEvenement = "select * from evenement join page on fkPage = idPage where dateDebut >= CURDATE( ) {$cat} order by dateDebut asc ";
		// echo $sqlEvenement;
		$evts = new Evenement ();
		$evts = $evts->getObjectListFromQuery ( $sqlEvenement );
		
		foreach ( $evts as $aEvt ) {
			/* @var $aEvt Evenement */
			
			$aRow = new ReturnRow ();
			$aRow->class = "Evenement";
			$aRow->id = $aEvt->getPrimaryKey ();
			$aRow->listTitle = $aEvt->formatDates () . " : " . $aEvt->getPage ()->getTitre ();
			$aRow->title = $aEvt->getPage ()->getTitre ();
			$aRow->introduction = $aEvt->getPage ()->introduction;
			$aRow->longDate = "<u>" . $aEvt->formatDates ( true, false ) . "</u>";
			
			$evtTile = $aEvt->getPage ()->titre;
			
			$evtTileCompl = "";
			$evtTileCompl = strip_tags ( $aRow->introduction ) . "<br />";
			
			if (! is_null ( $aEvt->getOrganisateur1 () )) {
				$evtTileCompl .= "Contact : " . $aEvt->getOrganisateur1 ()->getPrenomAndTels ();
			}
			
			if (! is_null ( $aEvt->getOrganisateur2 () )) {
				$evtTileCompl .= " et " . $aEvt->getOrganisateur2 ()->getPrenomAndTels ();
			}
			
			if (strlen ( $evtTileCompl ) > 0) {
				$evtTileCompl .= ".";
			}
			
			if ($aEvt->annule) {
				$aRow->htmlTitle = "<span style='text-decoration : line-through;'>{$aRow->title}</span> Rendez-vous annulé !";
			} else {
				$aRow->htmlTitle = $aRow->title;
			}
			
			$url = SITE_ROOT . "index.php?show&class=Evenement&id=" . $aEvt->getPrimaryKey ();
			$aRow->htmlTitle = "{$aEvt->getPage()->getCategorieClassement()->nom} : <a href='{$url}'>{$aRow->htmlTitle}</a>";
			
			$aRow->introduction = $evtTileCompl;
			
			$results [] = $aRow;
		}
	}
	
	if (is_int ( strpos ( $kind, "P" ) )) {
		$sqlPages = "select * from page where isSubClass = false {$cat} order by lastUpdateOn desc ";
		// echo $sqlPages;
		$pages = new Page ();
		$pages = $pages->getObjectListFromQuery ( $sqlPages );
		foreach ( $pages as $aPage ) {
			/* @var $aPage Page */
			$aRow = new ReturnRow ();
			$aRow->class = "Page";
			$aRow->id = $aPage->getPrimaryKey ();
			$aRow->listTitle = $aPage->titre;
			$aRow->title = $aPage->titre;
			$aRow->introduction = strip_tags ( $aPage->introduction );
			
			$url = SITE_ROOT . "index.php?show&class=Page&id=" . $aPage->getPrimaryKey ();
			$aRow->htmlTitle = "<a href='{$url}'>{$aRow->title}</a>";
			
			$results [] = $aRow;
		}
	}
}

$page->setStandardOuputDisabled ( true );
if (isPhpUp ()) {
	echo json_encode ( $results );
} else {
	echo json_encode ( $results, JSON_UNESCAPED_UNICODE );
}

?>