<?php
class Reglement extends HasMetaData {
    public $idReglement;
    public $dateEcheance;
    public $datePerception; 
    public $modePerception;
    public $refPerception;
    public $libelle;
    public $montant;
    public $inscription;
    public $idFamille;
    public $produit;
    public $remisebq;
    
    public function getPrimaryKey() {
        return $this->idReglement;
    }
    public function setPrimaryKey($newId) {
        return $this->idReglement = $newId;
    }
    private static $memberDeclaration;
    static function getMembersDeclaration() {
        if (is_null ( Reglement::$memberDeclaration )) {
            $pk = new SqlColumnMappgin ( "idReglement", null, SqlColumnTypes::$INTEGER );
            $pk->setPrimaryKey ( true );
            
            $inscription = new SqlColumnMappgin ( "fkInscription", "Inscription que cela règle", SqlColumnTypes::$INTEGER );
            $inscription->setForeing ( new Inscription (), "inscription", true, true );
            $inscription->isNullable = true;

            $rbq = new SqlColumnMappgin ( "fkRemiseEnBanque", "Remise en banque", SqlColumnTypes::$INTEGER );
            $rbq->setForeing ( new RemiseEnBanque (), "remisebq", true, true );
            $rbq->isNullable = true;
            
            $produit = new SqlColumnMappgin ( "fkProduit", "Produit que cela règle", SqlColumnTypes::$INTEGER );
            $produit->setForeing ( new Produit (), "produit", true, true );
            $produit->isNullable = true;
            
            
            Reglement::$memberDeclaration = array (
                $pk,
                new SqlColumnMappgin ( "dateEcheance", "Date d'échéance du règlement", SqlColumnTypes::$DATETIME ),
                new SqlColumnMappgin ( "datePerception", "Date de perception du règlement", SqlColumnTypes::$DATETIME ),
                new SqlColumnMappgin ( "modePerception", "Mode de perception", SqlColumnTypes::$VARCHAR, 30 ),
                new SqlColumnMappgin ( "refPerception", "Référence de la perception du règlement", SqlColumnTypes::$VARCHAR, 255 ),
                new SqlColumnMappgin ( "libelle", "Libelle", SqlColumnTypes::$VARCHAR, 255),
                new SqlColumnMappgin ( "montant", "Montant du règlement", SqlColumnTypes::$NUMERIC ),
                new SqlColumnMappgin ( "idFamille", "Famille en rapport avec ce reglement", SqlColumnTypes::$INTEGER ),
                $inscription,
                $produit,
                $rbq
            );
            
            Reglement::$memberDeclaration = array_merge ( Reglement::$memberDeclaration, HasMetaData::getMembersDeclaration () );
        }
        
        return Reglement::$memberDeclaration;
    }
    public function getShortToString() {
        return "Règlement n°".$this->idReglement;
    }
    protected function getNaturalOrderColumn() {
        return "dateEcheance";
    }
    
    
    public function clearReglementNonPercusForInscription($idInscription){
        $q = "delete from reglement where datePerception is null and fkInscription=".$idInscription;
        
        $this->ask($q);
        
    }
    
    public function getAllForInscription($idInscription) {
        $q = "select * from reglement where fkInscription = ".$idInscription;
        return $this->getObjectListFromQuery ( $q );
    }

    public function findReglementsSurRemise($idRemiseEnBanque){
        $q = "select * from reglement where fkRemiseEnBanque = ".$idRemiseEnBanque;
        return $this->getObjectListFromQuery ( $q );
    }
    
    public function getAllForFamille($idFamille) {
        $q = "select rgl.* from reglement rgl join inscription iscp on iscp.idInscription = rgl.fkInscription  where  iscp.etat >= 20 and iscp.etat < 70 and rgl.idFamille = ".$idFamille."
			union 
			select rgl.* from reglement rgl where  rgl.fkInscription is null and  rgl.idFamille = ".$idFamille;
        return $this->getObjectListFromQuery ( $q );
    }
    
    public function getAllFamillesEnAttenteReglement($dateReglementAttendu){

        
        $q = "select sum(uni.mnt) as dete, uni.idFamille 
            from (
                select sum(montant) as mnt, reglement.idFamille  from reglement
                join inscription on inscription.idInscription = reglement.fkInscription
                where reglement.modePerception = 'debit'
                and inscription.etat >= 20 and inscription.etat < 70
                and reglement.dateEcheance <= '{$dateReglementAttendu->formatMySql()}'
                group by reglement.idFamille
            union 
                select 0-sum(montant) as mnt, reglement.idFamille  from reglement
                where reglement.modePerception <> 'debit'
                and reglement.dateEcheance <= '{$dateReglementAttendu->formatMySql()}'
                group by reglement.idFamille
            ) as uni
            group by uni.idFamille
            having sum(uni.mnt) > 0";
        
        return $this->getDataFromQuery($q);
    }
   
}