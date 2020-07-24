<?php
class InscriptionEtat {
    public static $SUPPRIME = - 15;
    public static $BROUILLON = 10;
    public static $SOUMIS = 20;
    public static $ACCEPTE = 50;
    public static $ARCHIVE = 70;
}

class Inscription extends HasMetaData {
    public $idInscription;
    public $debut;
    public $fin;
    public $commentaire;
    public $etat;
    
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
    
   
}


