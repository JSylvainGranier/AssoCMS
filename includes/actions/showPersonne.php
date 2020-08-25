<?php
if (! Roles::isMembre ()) {
	throw new RoleException ( "Vous n'êtes pas habilité à voir le profil d'un membre de l'association." );
}

$page->appendBody ( file_get_contents ( "includes/html/showPersonne.html" ) );

/* @var $user Personne */
$user = new Personne ();

if (array_key_exists ( "idPersonne", $ARGS ) || array_key_exists ( "id", $ARGS )) {
	if (isConsistent ( $ARGS ["idPersonne"] )) {
		$user = $user->findById ( $ARGS ["idPersonne"] );
	} else if (isConsistent ( $ARGS ["id"] )) {
		$user = $user->findById ( $ARGS ["id"] );
	} else {
		throw new Exception ( "Une personne est spécifiée, mais sont id est vide." );
	}
} else {
	$user = $user->findById ( thisUserId() );
}

$sameUserAsActor = thisUserId() == $user->getPrimaryKey ();

$page->setTitle ( "Profil de " . $user->nom . " " . $user->prenom );

$page->asset ( "userToEdit", $user->nom . " " . $user->prenom );

if (Roles::canAdministratePersonne ()) {
	
	$page->asset ( "allowEmail", "Autorise le site " . SITE_TITLE . " à lui envoyer des eMails d'actualité : <b>" . ($user->allowEmails ? "oui" : "non") . "</b>" );
	$page->asset ( "allowMembersVisitProfile", "Autorise les autres membres à voir ses coordonnées dans le trombinoscope : <b>" . ($user->allowMembersVisitProfile ? "oui" : "non") . "</b>" );
	$page->asset ( "paperRecap", "Récapitulatif mensuel envoyé par courrier papier : <b>" . ($user->wantPaperRecap ? "oui" : "non") . "</b>" );
	if($user->dontWantUseTrombi){
		$page->asset ( "trombiPref", "A indiqué ne pas vouloir utiliser le trombinoscope le ".$user->dontWantUseTrombi->formatLocale() );
	} else if($user->cantUploadTrombiFile){
		$page->asset ( "trombiPref", "A indiqué ne pas parvenir à utilisez le trombinoscope le ".$user->cantUploadTrombiFile->formatLocale() );
	}
	if(! is_null($user->dateNaissance)){
    	$page->asset ( "dateNaissance", "Date de naissance : <b>" . $user->dateNaissance->format("d/m/Y") . "</b>" );
	} else {
	    $page->asset ( "dateNaissance", "Date de naissance : <i>non renseignée</i>" );
	}
	$page->asset ( "allowedToConnect", "Peut utiliser ses identifiants pour se connecter : <b>" . ($user->allowedToConnect ? "oui" : "non") . "</b>" );
	
	
}

if (Roles::isGestionnaireCategorie () || (Roles::isMembre () && $user->allowMembersVisitProfile) || $sameUserAsActor) {
	$email = "Pas d'email.";
	if (! is_null ( $user->email )) {
		$email = "<a href='mailto:{$user->email}'>$user->email</a>";
	}
	$page->asset ( "email", $email );
	
	$page->asset ( "adrL1", $user->adrL1 );
	$page->asset ( "adrL2", $user->adrL2 );
	$page->asset ( "adrL3", $user->adrL3 );
	$page->asset ( "cp", $user->adrCP );
	$page->asset ( "ville", $user->adrVille );
	$page->asset ( "allowPublishMyFace", $user->allowPublishMyFace ? "J'autorise" : "Je n'autorise pas" );
	
	$page->asset ( "telFixe", $user->telFixe );
	$page->asset ( "telPortable", $user->telPortable );
} else {
	$page->appendNotification ( "Vous ne pouvez pas accéder aux coordonnées de cette personne.
<br />Soit vous n’avez pas les droits, soit la personne en a explicitement fait la demande." );
}

$updatePhotoLabel = "Modifier la photo";

if (! is_null ( $user->trombiFile ) && file_exists ( $user->getTrombiFileFileSystemPath () )) {
	$page->asset ( "tombiImgTag", "<img src='{$user->getTrombiFileUrlPath()}' class='tombiFile' />" );
} else if (Roles::canAdministratePersonne ()) {
	$page->asset ( "tombiImgTag", "Pas encore de photo." );
	$updatePhotoLabel = "Cliquez ici pour en ajouter une";
}

$changePhotoLink = "<p><a href='index.php?edit&class=Trombinoscope&idPersonne={$user->getPrimaryKey()}'>{$updatePhotoLabel}</a></p>";

if ($sameUserAsActor) {
	$page->appendActionButton ( "Modifier mon profil", "edit&class=Personne", false, false );
	$page->appendActionButton ( "Changer mon mot de passe", "login&phase=changePassword", false, false );
	$page->asset ( 'changePhotoLink', $changePhotoLink );
} else {
	if (Roles::canAdministratePersonne ()) {
		
		$catAffilies = $user->getCategoriesAffiliesList ();
		
		$page->appendActionButton ( "Modifier le profil de " . $user->prenom, "edit&class=Personne&idPersonne=" . $user->getPrimaryKey () );
		$page->appendActionButton ( "Changer le mot de passe de " . $user->prenom, "login&phase=changePassword&idPersonne=" . $user->getPrimaryKey () );
		$page->appendActionButton ( "Affectations aux catégories", "list&class=AffiliationCategorie&personne=" . $user->getPrimaryKey () );
		$page->appendActionButton ( "Supprimer", "delete&class=Personne&id=" . $user->getPrimaryKey () );
		$page->asset ( 'changePhotoLink', $changePhotoLink );
	}
}

$familleList = "";

foreach($user->getAllPersonnesInFamily($user->idFamille) as $mFamille){
    if($mFamille->idPersonne == $user->idPersonne){
        continue;
    }
    
    $familleList .= "<a href='index.php?show&class=Personne&id={$mFamille->idPersonne}'>{$mFamille->prenom} {$mFamille->nom}</a>, ";
}

if(strlen($familleList) == 0){
    $familleList = "<i>Sans lien avec d'autres personnes de VISA30</i>";
}

$page->asset("family", $familleList);


if (Roles::isGestionnaireCategorie () || $sameUserAsActor) {
    $itb = "";
    
    $inscription = new Inscription();
    $ipp = new InscriptionPersonneProduit();
    $produit = new Produit();
    $reglement = new Reglement();
    
    $now = new MyDateTime();
    
    $iscpList = $inscription->getInscriptionsForFamille($user->idFamille);
    
    if(count($iscpList) == 0){
        $page->append("inscriptionsList", "<i>Pas d'inscription à ce jour</i>");
    }
    
    foreach($iscpList as $iscp){
        if($iscp->etat < 20 || $iscp->etat >= 70){
            continue;
        }
        
        $liensAdmin = "";
        
        if (Roles::isGestionnaireCategorie ()){
            $liensAdmin = "<p>";
            if($iscp->etat != InscriptionEtat::$SUPPRIME){
                $liensAdmin .= " <a onclick='changeInscriptionStatut({$iscp->idInscription}, -15, {$user->idPersonne}); return false;' href='#'>Supprimer</a> ";
            }
            
            if($iscp->etat != InscriptionEtat::$ACCEPTE){
                $liensAdmin .= " <a onclick='changeInscriptionStatut({$iscp->idInscription}, 50, {$user->idPersonne}); return false;' href='#'>Accepter</a> ";
            }
            
            if($iscp->etat != InscriptionEtat::$ARCHIVE && $iscp->etat != InscriptionEtat::$SOUMIS){
                $liensAdmin .= " <a onclick='changeInscriptionStatut({$iscp->idInscription}, 70, {$user->idPersonne});  return false;' href='#'>Archiver</a> ";
            }
            
            $liensAdmin .= "</p>";
            
        }
        
        $lienDownloadConfirm = $iscp->etat == 50 ? " <a target='_blank' href='index.php?exportInscriptionAttestation&idInscription=".$iscp->idInscription."'>Télécharger l'attestation</a>" : "";
        
        $itb .= "<div class='inscriptionItem'> <span class='inscriptionTitre'> Inscription du {$iscp->debut->format("d/m/Y")} </span> ";
        $itb .= "<span class='etatInscription'>".InscriptionEtat::getEtatLibelle($iscp->etat).$lienDownloadConfirm."</span>";
        
        $itb .= $liensAdmin;
        
        /*
        foreach ($ipp->getAllForInscription($iscp->idInscription) as $anIpp){
            $pers = $anIpp->getPersonne();
            $itb .= "<p class='produitLigne'>{$anIpp->getProduit()->libelle} pour {$pers->prenom} {$pers->nom}</p>";
            //$itb .= "<tr><td>".print_r($prod, true)."</td><td>{$iscp->etat}</td><td>{$pers->prenom} {$pers->nom}</td></tr>";
        }
        */
        
        $lignes = array();
        
        $itb .= "<span class='titreBeforeOptions'>Options souscrites : </span>";
        
        /*
        
        foreach ($reglement->getAllForInscription($iscp->idInscription) as $aReglement){
            
            
            if(is_null( $aReglement->datePerception)){
                $when = "à percevoir ";
                $when .= $aReglement->dateEcheance->date > $now->date ? "le ".$aReglement->dateEcheance->format("d/m/Y") : "au prochain atelier / activité";
                
            } else {
                $when = "perçu le ".$aReglement->datePerception->format("d/m/Y");
            }
            
            $lignes[] = "<li class='produitLigne' date='{$aReglement->dateEcheance->format("Y-m-d")}'>{$aReglement->libelle}, {$aReglement->montant}€ {$when}</li>";
            //$itb .= "<tr><td>".print_r($prod, true)."</td><td>{$iscp->etat}</td><td>{$pers->prenom} {$pers->nom}</td></tr>";
        }
        
        */
        
        foreach($ipp->getAllForInscription($iscp->idInscription) as $produitLigne){
            $lignes[] = "<li class='produitLigne' >{$produitLigne->getProduit()->libelle} pour {$produitLigne->getPersonne()->prenom}</li>";
        }
        
        sort($lignes);
        
        $itb .= "<ul>";
        
        foreach ($lignes as $al){
            $itb .= $al;
        }
        
        $itb .= "</ul>";
        $itb .= "</div>\n";
    }
    
    $page->append("inscriptionsList", $itb);
    
    
    $cotisationList = "";
    
    $reglementsList = $reglement->getAllForFamille($user->idFamille);
    
    if(count($reglementsList) == 0){
        $cotisationList = "<i>Pas de cotisations à ce jour</i>";
    } else {
        
        $rglParDate = array();
        
        $now = new MyDateTime();
        
        foreach($reglementsList as $aReglement){
            $kDate = $aReglement->dateEcheance->format("Y-m-d");
            if(!array_key_exists($kDate, $rglParDate)){
                $rglParDate[$kDate] = array();
            }
            $rglParDate[$kDate][] = $aReglement;
            
        }
        
        ksort($rglParDate);
        
        $reglements = "<table>";
        
        $montantTotal = 0;
        $montantADate = 0;
        
        $saisieReglementFormConfig = array(
            "idFamille" => $user->idFamille, 
            "targetTag" => "formReglement",
            "thenAction" => "show",
            "thenClass" => "Personne",
            "thenIdName" => "idPersonne",
            "thenIdValue" => $user->idPersonne
        );
        
        foreach ($rglParDate as $kDate => $rgls){
            $dt = MyDateTime::createFromFormat("Y-m-d H:i", $kDate." 00:00");
            $aPercevoirDesQuePossible = false;
            
            if($dt->date <= $now->date){
                $aPercevoirDesQuePossible = true;
            }
            
            $momentPerception = $aPercevoirDesQuePossible ? "Côtisations dûes à ce jour" : "Côtisations dûes au ".$dt->format("d/m/Y");
            
            
            $montantADate = $montantADate < 0 ? $montantADate : 0;
            
            sort($rgls);
            
            foreach($rgls as $aReglement){
                $cssClass = "Inconnu";
                $libelle = $aReglement->libelle;
                switch ($aReglement->modePerception){
                    case "debit" :
                        $montantADate += $aReglement->montant;
                        $montantTotal += $aReglement->montant;
                        $cssClass = "debit";
                        break;
                    case "Espèces" :
                        $montantADate -= $aReglement->montant;
                        $montantTotal -= $aReglement->montant;
                        $cssClass = "credit";
                        $libelle = "Règlement en espèces ";
                        break;
                    case "Chèque" :
                        $montantADate -= $aReglement->montant;
                        $montantTotal -= $aReglement->montant;
                        $cssClass = "credit";
                        $libelle = "Règlement par chèque ('{$aReglement->libelle}') ";
                        if(! array_key_exists("montant", $saisieReglementFormConfig)){
                            $saisieReglementFormConfig["libelle"] = $aReglement->libelle;
                        }
                        break;
                    default : throw new Exception("Mode de perception '{$aReglement->modePerception}' non pris en charge ici. ");
                }
                
                $reglements .= "<tr><td class='{$cssClass}'>{$libelle}</td><td>{$aReglement->montant}€</td></tr>";
            }
            
            if($montantADate > 0){
                $bizut = $aPercevoirDesQuePossible ? "dès que possible" : "pour le ".$dt->format("d/m/Y");
                $reglements .= "<tr><td class='sumUp'>Reste à régler {$bizut}</td><td class='sumUp'>{$montantADate}€</td></tr>";
            } else if ($montantADate == 0){
                $reglements .= "<tr><td class='sumUp'>Cotisations soldées au {$dt->format("d/m/Y")}</td><td class='sumUp'>{$montantADate}€</td></tr>";
            } else {
                $reglements .= "<tr><td class='sumUp'>Avance sur cotisations au {$dt->format("d/m/Y")}</td><td class='sumUp'>{$montantADate}€</td></tr>";
            } 
            
            {
                $reglements .= "<tr><td class='sumUp'>Solde Total</td><td class='sumUp'>{$montantADate}€</td></tr>";
            } 
            
            if($montantADate > 0 && ! array_key_exists("montant", $saisieReglementFormConfig)){
                $saisieReglementFormConfig["montant"] = $montantADate;
                $saisieReglementFormConfig["datePerception"] = $kDate;
            }
        }
        
        $reglements .= "</table>";
        
        $cotisationList .= "<div>{$reglements}</div>";
        
        if (Roles::canAdministratePersonne ()) {
            include 'includes/actions/saisieReglementForm.php';
        }
        
        
    }
    
    
    $page->append("cotisationsList", $cotisationList);
    
    
    
    
    
}


if (Roles::canAdministratePersonne ()) {
    
    $page->asset("fastSwitchScript", "<script src='ressources/searchPersonne.js' ></script>");
    $page->asset("fastSwitchDisplay", "block");
    
    $page->asset("inscriptionsList", "<p><a href='index.php?list&class=InscriptionsOuvertes&forceFamily={$user->idFamille}'>Débuter une inscription</a></p>");
    
    $page->appendActionButton ( "Modifier la famille", "edit&class=Famille&idFamille=" . $user->idFamille );
    
}

