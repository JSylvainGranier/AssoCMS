<?php
if (! isset ( $tag )) {
	$tag = "body";
}

$stateClass = "";
if ($obj->etat == PageEtat::$BROUILLON) {
	$stateClass = " notPublished brouillon ";
} else if ($obj->etat == PageEtat::$PROPOSE) {
	$stateClass = " notPublished propose ";
}

$page->append ( $tag, "<li class='pageListItem{$stateClass}'><h4><a href='index.php?show&class={$class}&id={$obj->getPrimaryKey()}'>{$obj->titre}</a></h4>" );
if (! is_null ( $obj->introduction ) && strlen ( $obj->introduction ) > 0) {
	$page->append ( $tag, "<div class='intro'>" . $obj->introduction . "</div>" );
}
$page->append ( $tag, "{$obj->getReadMoreLink()}</li>" );
