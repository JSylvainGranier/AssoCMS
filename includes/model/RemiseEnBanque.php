<?php
class RemiseEnBanque extends HasMetaData {
    public $idRemiseEnBanque;
    public $dateRemise;
    public $libelle;
    public $montantTotal;
    public $depositaire;
    
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
            
            $depositaireCol = new SqlColumnMappgin ( "depositaire", "Personne qui gère cette remise en banque", SqlColumnTypes::$INTEGER );
			$depositaireCol->setForeing ( new Personne (), "depositaire", false, true );
            
            RemiseEnBanque::$memberDeclaration = array (
                $pk,
                new SqlColumnMappgin ( "dateRemise", "Date du dépot à la banque", SqlColumnTypes::$DATETIME ),
                new SqlColumnMappgin ( "libelle", "Libelle", SqlColumnTypes::$VARCHAR, 255),
                new SqlColumnMappgin ( "montantTotal", "Montant total de la remise en banque", SqlColumnTypes::$NUMERIC ),
                $depositaireCol
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
    

    public function getAllActivesForActiveUser() {
        $q = "select * from remise_en_banque where depositaire = ".thisUserId()." and dateRemise is null order by lastUpdateOn DESC";
        return $this->getObjectListFromQuery ( $q );
    }

    public function getAllActives() {
        $q = "select * from remise_en_banque where dateRemise is null order by lastUpdateOn DESC";
        return $this->getObjectListFromQuery ( $q );
    }

    public function getLastInactivated() {
        $q = "select * from remise_en_banque where dateRemise > DATE_SUB(CURDATE(),INTERVAL 365 DAY) order by lastUpdateOn DESC";
        return $this->getObjectListFromQuery ( $q );
    }

    public function getDepositaire() {
		return $this->fetchLasyObject ( "depositaire", "Personne" );
	}
    
   
}