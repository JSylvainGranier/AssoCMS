<?php
$page->append ( "body", file_get_contents ( "includes/html/list.html" ) );

$instance = new Inscription();
$personne = new Personne();

$page->asset ( "listTitle", "Inscriptions" );
$page->setTitle("Inscriptions");


$list = $instance->getAll ();

if (count ( $list ) > 0) {
	foreach ( $list as $obj ) {
	    
	    $famMembers = $personne->getAllPersonnesInFamily($obj->idFamille);
	    $famString = $famMembers[0]->nom." ";
	    $comma = "";
	    foreach($famMembers as $pers){
	        $famString .= $comma.$pers->prenom;
	        $comma = ", ";
	    }
	    
	    $style = $obj->etat < InscriptionEtat::$SOUMIS ? "color : gray;" : "";
	    
	    $etat = "(".InscriptionEtat::getEtatLibelle($obj->etat).")";
	    
		$page->append ( "listElements", "<li style='{$style}' ><a href='index.php?show&class={$class}&id={$obj->getPrimaryKey()}'>{$obj->getShortToString()}</a> pour {$famString} {$etat}</li>" );
	}
} else {
	$page->append ( "listElements", "<p>Aucun élément.</p>" );
}

if (Roles::isGestionnaireGlobal ()) {
	$page->appendActionButton ( "Ajouter", "edit&class={$class}" );
}


