<article>

<h1>Cliquez sur la famille pour laquelle vous souhaitez saisir une inscription : </h1>

<ul>

<?php 

$pers = new Personne();

forEach($pers->getAllPersonnesByFamille() as $aFamille){
    echo "<li><a href='index.php?list&class=InscriptionsOuvertes&forceFamily={$aFamille["idFamille"]}'>{$aFamille["name"]}</a></li>";
}



?>

</ul>

</article>