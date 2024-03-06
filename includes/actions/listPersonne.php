<?php
$page->append ( "body", file_get_contents ( "includes/html/list.html" ) );
/* @var $instance Persistant */
/* @var $obj Persistant */
$pers = new Personne ();



if(isset($ARGS["notAllowedToConnect"])){
    $page->asset ( "listTitle", "Nouveaux comptes à valider" );
    $list = $pers->getAllNoAllowedToConnect ();
} else if(isset($ARGS["notAllowPublishMyFace"])){
    $page->asset ( "listTitle", "Personnes refusant que l'on publie des photos où ils sont présrnts" );
    $list = $pers->getAllNotAllowPublishMyFace ();
} else {
    $page->asset ( "listTitle", get_class ( $pers ) );
    $list = $pers->getAll ();
}



if (count ( $list ) > 0) {
	foreach ( $list as $obj ) {
		$page->append ( "listElements", "<li><a href='index.php?show&class={$class}&id={$obj->getPrimaryKey()}'>{$obj->getShortToString()}</a></li>" );
	}
} else {
	$page->append ( "listElements", "<p>Aucun élément.</p>" );
}

if (Roles::isGestionnaireGlobal ()) {
	$page->appendActionButton ( "Ajouter", "edit&class={$class}" );
}


