<?php 

$page->appendBody ( "Vue Globale" );

if (! Roles::isGestionnaireCategorie()) {
	header ( "HTTP/1.0 403 Forbidden" );
	echo "403";
	die ();
}
$page->appendBody ( "Passe" );
$arr = Persistant::getDataFromQuery("select i.idInscription, i.etat, p.idPersonne, p.nom, p.prenom, prt.idProduit, prt.libelle from inscription i join personne p on p.idFamille = i.idFamille join inscription_personne_produit ipp on ipp.fkPersonne = p.idPersonne join produit prt on prt.idProduit = ipp.fkProduit where i.etat in (20,50) order by p.nom, p.prenom");
$page->appendBody ( "Query" );
$page->appendBody ( print_r($arr, true) );
$page->appendBody ( "Print" );
?>