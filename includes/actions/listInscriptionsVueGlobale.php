<?php 

$page->appendBody ( "Vue Globale" );

if (! Roles::isGestionnaireCategorie()) {
	header ( "HTTP/1.0 403 Forbidden" );
	echo "403";
	die ();
}

$arr = Persistant::getDataFromQuery("SELECT i.idInscription, i.etat, p.idPersonne, p.nom, p.prenom, prt.idProduit, prt.libelle FROM personne p LEFT OUTER JOIN inscription i ON p.idFamille = i.idFamille LEFT OUTER JOIN inscription_personne_produit ipp ON ipp.fkPersonne = p.idPersonne LEFT OUTER JOIN produit prt ON prt.idProduit = ipp.fkProduit WHERE i.etat IS NULL  OR i.etat IN ( 20, 50 )  ORDER BY p.nom, p.prenom LIMIT 30 , 30");
$tb = array();

foreach($arr as $i => $rw){
    $arrPersonne = $tb[$rw["idPersonne"]];
    if($arrPersonne == null){
        $arrPersonne = array();
        $arrPersonne["idPersonne"] = $rw["idPersonne"];
        $arrPersonne["nom"] = $rw["nom"];
        $arrPersonne["prenom"] = $rw["prenom"];
    }
    if(isset($rw["idProduit"])){
        $arrPersonne["p".$rw["idProduit"]] = true;
    } else {
        
    }
    
    $tb[$rw["idPersonne"]] = $arrPersonne;
}

$p = new Produit();
$tmpProds = $p->getAll();
$prods = array();
foreach($tmpProds as $ap){
    $prods[$ap->idProduit] = $ap;
}

$pCells = "";

foreach($prods as $ap){
    $pCells .= "<td>{$ap->libelle}</td>";
}

$page->appendBody("<table>");
$page->appendBody("<tr><td>Nom</td><td>Prénom</td>".$pCells."<td>Solde</td></tr>");

foreach($tb as $i => $rw){
    $pchecks = "";
    foreach($prods as $ap){
        $pchecks .= "<td>";
        if($ap->idProduit == $rw["idProduit"]){
            $pchecks .= "X";
            //$ap["count"] += 1;
        }
        $pchecks .= "</td>";
        
    }
    
    $solde = 0.365;
    
    $page->appendBody ( "<tr><td>{$rw["nom"]}</td><td>{$rw["prenom"]}</td>{$pchecks}<td>{$solde}</td></tr>" );
    
}

$page->appendBody("</table>");

$page->appendBody("<pre>".print_r($tb, true)."</pre>");
$page->appendBody("<pre>".print_r($arr)."</pre>");


?>