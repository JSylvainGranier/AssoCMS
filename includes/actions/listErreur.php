<?php

$err = new Erreur();

if(array_key_exists("action", $ARGS)){
	switch ($ARGS["action"]){
		case "purge":
			$err->dropAll();
			$page->appendBody ( "<p>Oups ! J'ai tout supprimé !</p>" );
			break;
		default:
			$page->appendBody ( "<p>J'ai pas compris !</p>" );
	}
} else {
	$page->appendBody ( "<p><a href='index.php?list&class=Erreur&action=purge'>Purger les erreurs</a></p>" );
	
}



$sql = "select * from erreur order by lastUpdateOn DESC limit 200";



$errors = $err->getObjectListFromQuery($sql);

$s = "<ul>";

if(count($errors) > 0){
	foreach ($errors as $anError){
		$s .= "<li><a href='index.php?show&class=Erreur&id={$anError->getPrimaryKey()}'>".$anError->getShortToString()."</a><br /><br /></li>";
	}
} else {
	$s.= "<li>Y-en a pas...</li>";
}




$s .="</ul>";
$page->appendBody($s);
