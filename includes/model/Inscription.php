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
    
    public function findRecentInscriptionForFamille($idFamille){
        $query = "select * from inscription where idFamille = {$idFamille} and etat = 10 and lastUpdateOn >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) order by lastUpdateOn DESC limit 1";
        
        return $this->getOneObjectOrNullFromQuery ( $query );
    }
   
}


