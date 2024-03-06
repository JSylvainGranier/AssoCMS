<?php
$page->append ( "body", file_get_contents ( "includes/html/list.html" ) );
/* @var $instance Persistant */
/* @var $obj Persistant */
@$instance = new $class ();

$page->asset ( "listTitle", get_class ( $instance ) );

$list = $instance->getAll ();

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


