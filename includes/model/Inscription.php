<?php
class InscriptionEtat {
    public static $SUPPRIME = - 15;
    public static $BROUILLON = 10;
    public static $SOUMIS = 20;
    public static $ACCEPTE = 50;
    public static $ARCHIVE = 70;
    
    public static function getEtatLibelle($etat){
        switch ($etat) {
            case InscriptionEtat::$SUPPRIME : return "Supprimée" ;
            case InscriptionEtat::$BROUILLON : return "Brouillon enregistré" ;
            case InscriptionEtat::$SOUMIS : return "Soumise à la validation du bureau" ;
            case InscriptionEtat::$ACCEPTE : return "Validée" ;
            case InscriptionEtat::$ARCHIVE : return "Archivée" ;
        }
        
    }
}

class Inscription extends HasMetaData {
    public $idInscription;
    public $debut;
    public $fin;
    public $commentaire;
    public $etat;
    public $idFamille;
    
    public function getPrimaryKey() {
        return $this->idInscription;
    }
    public function setPrimaryKey($newId) {
        $this->idInscription = $newId;
    }
    private static $memberDeclaration;
    static function getMembersDeclaration() {
        if (is_null ( Inscription::$memberDeclaration )) {
            $pk = new SqlColumnMappgin ( "idInscription", null, SqlColumnTypes::$INTEGER );
            $pk->setPrimaryKey ( true );
            
            Inscription::$memberDeclaration = array (
                $pk,
                new SqlColumnMappgin ( "debut", "Début validité inscription", SqlColumnTypes::$DATETIME ),
                new SqlColumnMappgin ( "fin", "Fin validité inscription", SqlColumnTypes::$DATETIME ),
                new SqlColumnMappgin ( "commentaire", "Commentaire", SqlColumnTypes::$LONGTEXT ),
                new SqlColumnMappgin ( "etat", "Etat de l'inscription", SqlColumnTypes::$INTEGER ),
                new SqlColumnMappgin ( "idFamille", "idFamille", SqlColumnTypes::$INTEGER ),
            );
            
            Inscription::$memberDeclaration = array_merge ( Inscription::$memberDeclaration, HasMetaData::getMembersDeclaration () );
        }
        
        return Inscription::$memberDeclaration;
    }
    public function getShortToString() {
        return "Inscription n°".$this->idInscription; 
    }
    protected function getNaturalOrderColumn() {
        return "debut";
    }
    
    public function getInscriptionsForFamille($idFamille){
        $query = "select * from inscription where idFamille = ".$idFamille;
        $order = $this->getNaturalOrderColumn ();
        if (! is_null ( $order )) {
            $query .= " order by {$order} DESC ";
        }
        $objList = $this->getObjectListFromQuery ( $query );
        return $objList;
    }
    
    public function clearOldBrouillons(){
        $wc = "etat = 10 and now() > DATE_ADD(lastUpdateOn, INTERVAL 5 DAY)";
        $query = "delete from inscription_personne_produit where fkInscription in (select idInscription from inscription where {$wc});";
        $this->ask($query);
        $query = "delete from inscription where {$wc};";
        $this->ask($query);
    }
    
    public function findRecentInscriptionForFamille($idFamille){
        $query = "select * from inscription where idFamille = {$idFamille} and etat = 10 and lastUpdateOn >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) order by lastUpdateOn DESC limit 1";
        
        return $this->getOneObjectOrNullFromQuery ( $query );
    }
    
    public function changerStatut($newStatus){
        
        
        $rep = array();
        
        
        $ipp = new InscriptionPersonneProduit();
        $ippList = $ipp->getAllForInscription($this->idInscription);
        $produit = new Produit();
        $apc = new AffiliationCategorie();
        
        $oldStatus = $this->etat;
        
        $this->etat = $newStatus;
        $this->save();
        
        $rep[] = "⇨Inscription passée de '".InscriptionEtat::getEtatLibelle($oldStatus)."' à '".InscriptionEtat::getEtatLibelle($newStatus)."'.";
        
        $sendMailConfirm = false;
        $addCategorieAffectation = false;
        $removeCategorieAffectation = false;
        $activateAccount = false;
        
        
        if($oldStatus < InscriptionEtat::$ACCEPTE && $newStatus == InscriptionEtat::$ACCEPTE){
            $sendMailConfirm = true;
            $addCategorieAffectation = true;
            $activateAccount = true;
        } else if($oldStatus == InscriptionEtat::$ACCEPTE && $newStatus == InscriptionEtat::$ARCHIVE){
            $removeCategorieAffectation = true;
        }
        
        
        
        if($addCategorieAffectation){
            
            $nbChanges = 0;
            
            foreach ($ippList as $anIpp){
                
                $actionDoneForPersonne = false;
                
                $idCateg = $anIpp->getProduit()->idCategorieAffecter;
                
                if($idCateg == -55){
                    $pers = $anIpp->getPersonne();
                    
                    if( ! $pers->wantPaperRecap){
                        $pers->wantPaperRecap = true;
                        $pers->save();
                        $rep[] = "⇨J'ai activé la calendrier papier pour {$pers->prenom} {$pers->nom}.";
                    }
                } else if($idCateg > 0){
                    foreach ($apc->findForPersonneEtCategorie($anIpp->getPersonne()->idPersonne, $idCateg)    as    $assoc){
                        $actionDoneForPersonne = true;
                        $assoc->save();
                        $nbChanges++;
                    }
                    
                    if(!$actionDoneForPersonne){
                        $affCat = new AffiliationCategorie ();
                        $affCat->categorie = $idCateg + 0;
                        $affCat->personne = $anIpp->getPersonne();
                        $affCat->save ();
                        
                        $actionDoneForPersonne = true;
                        $nbChanges++;
                    }
                }
                
            }
            
            if($nbChanges > 0)
                $rep[] = "⇨J'ai associé les personnes concernées aux bonnes sections de l'association";
        }
        
        if($removeCategorieAffectation){
            $nbChanges = 0;
            
            foreach ($ippList as $anIpp){
                $idCateg = $anIpp->getProduit()->idCategorieAffecter;
                
                $actionDoneForPersonne = false;
                
                if($idCateg > 0){
                    foreach ($apc->findForPersonneEtCategorie($anIpp->getPersonne()->idPersonne, $idCateg)    as    $assoc){
                        $actionDoneForPersonne = true;
                        $assoc->delete();
                        $nbChanges++;
                    }
                    
                }
                
            }
            
            if($nbChanges > 0)
                $rep[] = "⇨J'ai supprimé l'association entre les personnes et sections de l'association";
        }
        
        if($sendMailConfirm){
            
            $mailTo = array();
            
            foreach ($ippList as $anIpp){
                $pers = $anIpp->getPersonne();
                
                if(!is_null($pers->email) && strlen($pers->email) > 5){
                    $mailTo[] = $pers->email;
                }
                
            }
            
            $mailTo = array_unique($mailTo);
            
            if(count($mailTo) > 0){
                $mailContent = $this->getConfirmationInscriptionHtml();
                
                $mailContent = "<p>Bonjour,</p><p>Veuillez trouver ci-dessous la confirmation de votre inscription à l'association ".SITE_TITLE."</p>" . $mailContent;
                
                foreach($mailTo as $email){
                    sendSimpleMail ( "Confirmation de votre inscription " . SITE_TITLE, $mailContent, $email, true );
                    $rep[] = "⇨J'ai envoyé un email de confirmation à {$email}";
                }
                
            }
            
            
        }
        
        if($activateAccount){
            
            foreach ($ippList as $anIpp){
                $pers = $anIpp->getPersonne();
                
                if(!$pers->allowedToConnect){
                    $pers->allowedToConnect = true;
                    $pers->save();
                    $rep[] = "⇨J'ai activé le compte utilisateur de {$pers->prenom} {$pers->nom}.";
                }
                
            }
            
            
        } else {
            foreach ($ippList as $anIpp){
                $pers = $anIpp->getPersonne();
                $pers->wantPaperRecap = false;
                $pers->save();
            }
        }
        
            
        
            
        
        return $rep;
    }
    
    
    public function getConfirmationInscriptionHtml(){
    	$ipp = new InscriptionPersonneProduit();
    	$reglement = new Reglement();
		$ippList = $ipp->getAllForInscription($this->idInscription);
		
		$produitList = array();
		
		foreach($ippList as $anIpp){
            
		    $prdt = $anIpp->getProduit();

		    $pil = array();
            $pil["quantite"] = $anIpp->quantite;
		    
		    if(array_key_exists($prdt->idProduit, $produitList)){
		        $pil = $produitList[$prdt->idProduit];
		    } else {
		        $pil = array();
		        $pil["produit"] = $prdt;
		        $pil["personnes"] = array();
		        if(isset($anIpp->conditionsLegales) && !is_null($anIpp->conditionsLegales) && strlen($anIpp->conditionsLegales) > 5){
    		        $pil["conditions"] = $anIpp->conditionsLegales;
		        }
		        
		    }
		    
		    $pil["personnes"][] = $anIpp->getPersonne();
		    $produitList[$prdt->idProduit] = $pil;
		}
		
		$h = "";
		
		$h .= "<p>Inscription en date du ".$this->debut->format("d/m/Y")." </p>"; 
		
		function cmp($a, $b)
		{
		    return strcmp($a["produit"]->produitOrdre, $b["produit"]->produitOrdre);
		}
		usort($produitList, "cmp");
		
		foreach($produitList as $pid => $pil){
            echo print_r($pil);

		    $h .= "<h2>{$pil["produit"]->libelle} </h2>";
		    
		    $h .= "<p class='description'>{$pil["produit"]->description}</p>";

            $h .= "<p class='persInscrites'><u>Quantité souscrite :</u> ".$pil["quantite"]."</p>";
		    
		    $h .= "<p class='persInscrites'><u>Personnes inscrites :</u> ";
		    
		    
		    if(count($pil["personnes"]) == 2){
		        $h .= $pil["personnes"][0]->prenom . " " . $pil["personnes"][0]->nom . " et " . $pil["personnes"][1]->prenom . " " . $pil["personnes"][1]->nom;
		    } else {
		        $comma = "";
		        foreach ($pil["personnes"] as $aPers){
		            $h .= $aPers->prenom . " " . $aPers->nom . $comma;
		            $comma = ", ";
		        }
		        
		    }
		    
		    $h .= "</p>";
		    
		    if(isset($pil["conditions"]) && !is_null($pil["conditions"]) && strlen($pil["conditions"]) > 5){
		        $h .= "<i>Des conditions spécifiques s'appliquent à cette option. Veuillez vous reporter aux pages suivantes.</i>";
		        
		    }
		    
		}
		
		$h .= "<h2>Détail des cotisations</h2>";
		$reglementList = $reglement->getAllForInscription($this->idInscription);
		
		function cmpReglement($a, $b)
		{
		    return strcmp($a->dateEcheance->format("Y-m-d").$a->libelle, $b->dateEcheance->format("Y-m-d").$a->libelle);
		}
		usort($reglementList, "cmpReglement");
		
		
		if(count($reglementList) > 0){
		    
		    $h .= "<table style='width : 100%;' class='reglement'>";
		    $h .= "<tr><th style='width : 15%;'>Date échéance</th><th style='width : 15%;'>Montant</th><th style='width : 70%;'>Motif</th></tr>";
		    
		    foreach($reglementList as $aReglement){
		        
		        $h .= "<tr><td>{$aReglement->dateEcheance->format("d/m/Y")}</td><td>{$aReglement->montant} €</td><td>{$aReglement->libelle}</td></tr>";
		    }
		    
		    $h .= "</table>";
		    $h .= "<p class='info'>Le paiement des cotisations peut se faire en espèce ou en chèque (à l'ordre de <b>Association ".SITE_TITLE."</b>)</p>";
		} else {
		    $h .= "<p class='info'>Cette inscription n'entraine pas de cotisation</p>";
		}
		
		
		
		foreach($produitList as $pid => $pil){
		    
		    if(isset($pil["conditions"]) && !is_null($pil["conditions"]) && strlen($pil["conditions"]) > 5){
		        $h .= "<div class='conditionsContainer'>".$pil["conditions"]."</div>";
		    }
		    
		    
		}
		
		
		
		return $h;
    }
   
}


