<?php
$page->setTitle ( "Trombinoscope" );

$page->appendBody ( file_get_contents ( "includes/html/trombinoscope.html" ) );

$pers = new Personne ();

if (array_key_exists ( "printable", $ARGS )) {
	
	$page->append ( "trombiContainer", "<table style='width:100%;'>" );
	$page->append ( "trombiContainer", "<tr><th>Nom</th><th>Prénom</th><th>Tél. Fixe</th><th>Tél. Portable</th></tr>" );
	
	foreach ( $pers->getAll () as $personne ) {
		
		$showProfileUrl = SITE_ROOT . "index.php?show&class=Personne&idPersonne=" . $personne->getPrimaryKey ();
		if (! Roles::canAdministratePersonne () && ! $personne->allowMembersVisitProfile) {
			$personne->telFixe = "";
			$personne->telPortable = "";
		}
		$html = "<tr><td><a href='{$showProfileUrl}'>{$personne->nom}</a></td><td>{$personne->prenom}</td><td>{$personne->telFixe}</td><td>{$personne->telPortable}</td></tr>";
		
		$page->append ( "trombiContainer", $html );
	}
	
	$page->append ( "trombiContainer", "</table>" );
	
	$page->appendActionButton ( "Version normale", "trombinoscope" );
} else {
	foreach ( $pers->getAll () as $personne ) {
		
		if (is_null ( $personne->trombiFile ) || ! file_exists ( "documents/trombi/" . $personne->trombiFile )) {
			$imageUrl = "";
		} else {
			$imageUrl = $personne->getTrombiFileUrlPath ();
		}
		
		$showProfileUrl = SITE_ROOT . "index.php?show&class=Personne&idPersonne=" . $personne->getPrimaryKey ();
		
		
		$html = "<div class='trombiCell'><table>
			<tr><td><a href='{$showProfileUrl}'><img class='trombiImage' src='ressources/template/nobody.png' data-src='$imageUrl' /></a></td></tr>
			<tr><td style='height : 1em;'><a class='trombiLink' href='{$showProfileUrl}'>{$personne->nom} {$personne->prenom}</a></td></tr>
			</table></div>";
		//$html = "<div class='trombiCell'><a href='{$showProfileUrl}'><img class='trombiImage' src='ressources/template/nobody.png' data-src='$imageUrl' /></a><a class='trombiLink' href='{$showProfileUrl}'>{$personne->nom} {$personne->prenom}</a></div>";
		
		$page->append ( "trombiContainer", $html );
	}
	
	$page->appendActionButton ( "Version imprimable", "trombinoscope&printable", false, false );
}

if (Roles::isGestionnaireGlobal ()) {
	$page->appendActionButton ( "Ajouter une personne", "edit&class=Personne&createNewUser" );
}

if (Roles::canAdministratePersonne ()) {
	$page->appendActionButton ( "Exporter", "export&class=Trombinoscope" );
}

