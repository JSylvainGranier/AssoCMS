<?php
if (! isset ( $tag )) {
	$tag = "body";
}

if(!$evenement->getPage()->canBeShown() && $evenement->getPage ()->etat > PageEtat::$PROPOSE){
    $page->append ( $tag, "<li class='pageListItem mustLoginToViewThis'><a href='index.php?login&phase=notLogged'>Vous devez vous connecter pour voir ce rendez-vous...</a></li>" );
    return;
}

$evtTile = $evenement->getPage ()->titre;

if (isset ( $displayCategorie ) && $displayCategorie) {
	$catTitle = $evenement->getPage ()->getCategorieClassement ()->nom;
	$displayCat = "<span class='headerCatName'>Section '{$catTitle}'</span>";
} else {
	$displayCat = "";
}

$evtTileCompl = "<b>" . ucfirst ( $evenement->formatDates ( true ) ) . "</b>";

$hasOraganisateur = false;
if (! is_null ( $evenement->getOrganisateur1 () )) {
	$tel = Roles::isMembre () ? "<br />(" . $evenement->getOrganisateur1 ()->getTels () . ")" : "";
	$evtTileCompl .= ", rendez-vous organisé par <a href='index.php?show&class=Personne&id={$evenement->getOrganisateur1()->getPrimaryKey()}'> " . $evenement->getOrganisateur1 ()->prenom . "</a> " . $tel;
	$hasOraganisateur = true;
}

if (! is_null ( $evenement->getOrganisateur2 () )) {
	$tel = Roles::isMembre () ? "<br />(" . $evenement->getOrganisateur2 ()->getTels () . ")" : "";
	$evtTileCompl .= " et <a href='index.php?show&class=Personne&id={$evenement->getOrganisateur2()->getPrimaryKey()}'>" . $evenement->getOrganisateur2 ()->prenom . "</a> " . $tel;
	$hasOraganisateur = true;
}
if ($hasOraganisateur && ! Roles::isMembre ()) {
	$evtTileCompl .= "<span class='clicForCoordinates'>&lt; Cliquez pour voir les coordonnées</span>";
}

if (strlen ( $evtTileCompl ) > 0) {
	$evtTileCompl .= ".";
}

$annulationClass = $evenement->annule ? " barre" : "";
if ($evenement->annule) {
	$annulationText = "<div class='inListAnnulation'>Rendez-vous <br /> Annulé!</div>";
} else {
	$annulationText = "";
}

$stateClass = "";
if ($evenement->getPage ()->etat == PageEtat::$BROUILLON) {
	$stateClass = " notPublished brouillon ";
} else if ($evenement->getPage ()->etat == PageEtat::$PROPOSE) {
	$stateClass = " notPublished propose ";
}

$page->append ( $tag, "<li class='pageListItem{$stateClass}'><span class='{$annulationClass}'>{$annulationText}<h4 ><a href='{$evenement->getShowURL()}'>{$evtTile}</a>{$displayCat}</h4>{$evtTileCompl}" );
if (! is_null ( $evenement->getPage ()->introduction ) && strlen ( $evenement->getPage ()->introduction ) > 0) {
	$page->append ( $tag, "<div class='intro'>" . $evenement->getPage ()->introduction . "</div>" );
} else {
	$page->append ( $tag, "<br />" );
}
$page->append ( $tag, "{$evenement->getReadMoreLink()}</li>" );