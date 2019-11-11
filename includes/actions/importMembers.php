<?php
if (array_key_exists ( "csv", $ARGS )) {
	/* @var $persRoot Personne */
	$persRoot = new Personne ();
	
	$affilRoot = new AffiliationCategorie ();
	
	foreach ( $affilRoot->getAll () as $anAffil ) {
		$anAffil->delete ();
	}
	/*
	 * $persList = $persRoot->getAll();
	 *
	 * foreach ($persList as $aPers){
	 * $aPers->delete();
	 * }
	 */
	
	$lines = explode ( PHP_EOL, $ARGS ["csv"] );
	$data = array ();
	foreach ( $lines as $line ) {
		$data [] = explode ( ";", $line );
	}
	
	$randoId = 1;
	$choraleId = 2;
	$potterieId = 3;
	
	foreach ( $data as $pers ) {
		$p = new Personne ();
		
		if ($pers [0] == "Mme et M.") {
			$p->civilite = "Monsieur et Madame";
		} else if ($pers [0] == "Mme") {
			$p->civilite = "Madame";
		} else if ($pers [0] == "M.") {
			$p->civilite = "Monsieur";
		} else if ($pers [0] == "Mlle") {
			$p->civilite = "Madame";
		}
		
		$p->nom = stripslashes ( $pers [2] );
		$p->prenom = stripslashes ( $pers [1] );
		
		$p->telFixe = $pers [3];
		$p->telPortable = $pers [4];
		
		$p->adrL1 = stripslashes ( $pers [5] );
		$p->adrL2 = stripslashes ( $pers [6] );
		
		$p->adrCP = $pers [8];
		$p->adrVille = stripslashes ( $pers [9] );
		$p->email = $pers [7];
		
		if ($p->email == "jsylvain.granier@gmail.com") {
			$p->addRole ( Roles::$SUPER_ADMIN );
		}
		
		$p->setPassword ( md5 ( "moiCoucou les Shtroupfs!" ) );
		
		$p->save ();
		/*
		 * if($pers[3] == "1"){
		 * $affectation = new AffiliationCategorie();
		 * $affectation->personne = $p;
		 * $affectation->categorie = $randoId;
		 * $affectation->save();
		 * }
		 *
		 * if($pers[4] == "1"){
		 * $affectation = new AffiliationCategorie();
		 * $affectation->personne = $p;
		 * $affectation->categorie = $choraleId;
		 * $affectation->save();
		 * }
		 *
		 * if($pers[5] == "1"){
		 * $affectation = new AffiliationCategorie();
		 * $affectation->personne = $p;
		 * $affectation->categorie = $potterieId;
		 * $affectation->save();
		 * }
		 */
	}
}

$html = "<form method='post'><textarea rows='30' cols='100' name='csv'>{$ARGS["csv"]}</textarea><br /><input type='submit'></form>";
$page->appendBody ( "<h1>Import des membres au format CSV</h1>" );
$page->appendBody ( "Civilité, Prénom, Nom, Tel. Fixe, Tel. Portable, AdrL1, AdrL2, eMail, CP, Ville, Rando, Chorale, Potterie" );
$page->appendBody ( $html );

?>


