<?php
class RemiseEnBanque extends HasMetaData {
    public $idRemiseEnBanque;
    public $dateRemise;
    public $libelle;
    public $montantTotal;
    
    public function getPrimaryKey() {
        return $this->idRemiseEnBanque;
    }
    public function setPrimaryKey($newId) {
        return $this->idRemiseEnBanque = $newId;
    }
    private static $memberDeclaration;
    static function getMembersDeclaration() {
        if (is_null ( RemiseEnBanque::$memberDeclaration )) {
            $pk = new SqlColumnMappgin ( "idRemiseEnBanque", null, SqlColumnTypes::$INTEGER );
            $pk->setPrimaryKey ( true );
            
            
            RemiseEnBanque::$memberDeclaration = array (
                $pk,
                new SqlColumnMappgin ( "dateRemise", "Date du dépot à la banque", SqlColumnTypes::$DATETIME ),
                new SqlColumnMappgin ( "libelle", "Libelle", SqlColumnTypes::$VARCHAR, 255),
                new SqlColumnMappgin ( "montantTotal", "Montant total de la remise en banque", SqlColumnTypes::$NUMERIC )
            );
            
            RemiseEnBanque::$memberDeclaration = array_merge ( RemiseEnBanque::$memberDeclaration, HasMetaData::getMembersDeclaration () );
        }
        
        return RemiseEnBanque::$memberDeclaration;
    }
    public function getShortToString() {
        return "Remise en banque n°".$this->idRemiseEnBanque;
    }
    protected function getNaturalOrderColumn() {
        return "dateRemise";
    }
    
    
    public function getTableName(){
        return "remise_en_banque";
    }
    
    public function getAllForFamille($idFamille) {
        $q = "select rgl.* from reglement rgl join inscription iscp on iscp.idInscription = rgl.fkInscription  where  iscp.etat >= 20 and iscp.etat < 70 and rgl.idFamille = ".$idFamille."
			union 
			select rgl.* from reglement rgl where  rgl.fkInscription is null and  rgl.idFamille = ".$idFamille;
        return $this->getObjectListFromQuery ( $q );
    }
    
   
}