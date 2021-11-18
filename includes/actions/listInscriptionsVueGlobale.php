<?php 
$page->appendBody ( file_get_contents ( "includes/html/inscriptionsVueGlobale.html" ) );
$page->setTitle("Vue globale des inscriptions");


if (! Roles::isGestionnaireCategorie()) {
	header ( "HTTP/1.0 403 Forbidden" );
	echo "403";
	die ();
}

$arr = Persistant::getDataFromQuery("SELECT i.idInscription, i.etat, ipp.quantite, p.idPersonne, p.idFamille, p.nom, p.prenom, p.email, p.telPortable, prt.idProduit, prt.libelle FROM personne p LEFT OUTER JOIN inscription i ON p.idFamille = i.idFamille LEFT OUTER JOIN inscription_personne_produit ipp ON ipp.fkInscription = i.idInscription LEFT OUTER JOIN produit prt ON prt.idProduit = ipp.fkProduit ORDER BY p.nom, p.prenom");
$tb = array();

$regs = Persistant::getDataFromQuery("SELECT * from reglement where dateEcheance < now()");


foreach($arr as $i => $rw){
    $arrPersonne = $tb[$rw["idPersonne"]];
    if($arrPersonne == null){
        $arrPersonne = array();
        $arrPersonne["idPersonne"] = $rw["idPersonne"];
        $arrPersonne["idFamille"] = $rw['idFamille'];
        $arrPersonne["nom"] = $rw["nom"];
        $arrPersonne["prenom"] = $rw["prenom"];
        $arrPersonne["email"] = $rw["email"];
        $arrPersonne["etat"] = $rw["etat"];
        $arrPersonne["quantite"] = $rw["quantite"];
        $arrPersonne["telPortable"] = $rw["telPortable"];
        
        
        $arrPersonne["regs"] = array();
        foreach($regs as $aReg){
            if($aReg["idFamille"] == $rw['idFamille']){
                $arrPersonne["regs"][] = $aReg;
            }
        }
        
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
    $pCells .= "<th>{$ap->libelle}</th>";
    $ap->count = 0;
}
$page->asset("pCells", $pCells);

$now = new MyDateTime();

$page->asset("now", $now->format("d/m/Y"));

$nbRows = 0;

foreach($tb as $i => $rw){
    $pchecks = "";
    foreach($prods as $ap){
        $pchecks .= "<td>";
        $varName = "p".$ap->idProduit;
        if(array_key_exists($varName, $rw) && $rw["etat"] > 0){
            /*
            for($qt = 0; $qt < $rw["quantite"]; $qt++){
                $pchecks .= "X&nbsp;";
                $ap->count += 1;
            }
            */

            $pchecks .= "X (".$rw["quantite"].")";
            $ap->count += 1;
            
        }
        $pchecks .= "</td>";
        
    }
    $nbRows++;
    $solde = 0;
    foreach($rw["regs"] as $aReg){
        if($aReg["modePerception"] == "debit"){
            $solde += $aReg["montant"];
        } else {
            $solde -= $aReg["montant"];
        }
    }
    $soldeClass = "sDiscret"; 
    if($solde > 0) {
        $soldeClass = "sDebiteur";
    }
    if($solde < 0) {
        $soldeClass = "sCrediteur";
    }
    
    
    
    $profil = "<a href='index.php?show&class=Personne&idPersonne={$rw["idPersonne"]}'><i class='fa fa-user' aria-hidden='true'></i></a>";
    $inscription = "<a href='index.php?list&class=InscriptionsOuvertes&forceFamily={$rw["idFamille"]}'><i class='fa fa-check-square-o' aria-hidden='true'></i></a>";
    
    $page->append ("tbody", "<tr><td>{$rw["nom"]}</td><td>{$rw["prenom"]}</td><td>{$rw["telPortable"]}</td><td>{$rw["email"]}</td>{$pchecks}<td class='{$soldeClass}'>{$solde}€</td><td>{$profil}</td><td>{$inscription}</td></tr>" );
    
}

$pCount = "";
foreach($prods as $ap){
    $pCount .= "<td>{$ap->count} / {$nbRows}</td>";    
}
$page->append ("tfoot", "<tr><td colspan='4' ><b>Total</b>{$pCount}<td></td><td></td><td></td></tr>" );


?>