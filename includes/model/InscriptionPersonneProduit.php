<?php
/**
 * Inscription d'une personne à un produit
 * @author leyry
 *
 */
class InscriptionPersonneProduit extends HasMetaData {
    public $idIPP;
    public $inscription;
    public $personne; 
    public $produit;
    public $dateAcceptationConditionsLegales;
    public $conditionsLegales;
    public $quantite;
    
    public function getPrimaryKey() {
    }
    public function setPrimaryKey($newId) {
    }
    private static $memberDeclaration;
    static function getMembersDeclaration() {
        if (is_null ( InscriptionPersonneProduit::$memberDeclaration )) {
            $pk = new SqlColumnMappgin ( "idMail", null, SqlColumnTypes::$INTEGER );
            $pk->setPrimaryKey ( true );
            
            $inscription = new SqlColumnMappgin ( "inscription", "Inscription qui fait la liaison", SqlColumnTypes::$INTEGER );
            $inscription->setForeing ( new Inscription (), "fkInscription", true, true );
            
            $personne = new SqlColumnMappgin ( "personne", "Personne que l'on inscrit à une activité", SqlColumnTypes::$INTEGER );
            $personne->setForeing ( new Personne (), "fkPersonne", true, true );
            
            $produit = new SqlColumnMappgin ( "produit", "Produit souscrit pour la personne", SqlColumnTypes::$INTEGER );
            $produit->setForeing ( new Produit (), "fkProduit", true, true );
            
            InscriptionPersonneProduit::$memberDeclaration = array (
                $pk,
                $inscription,
                $personne,
                $produit,
                new SqlColumnMappgin ( "dateAcceptationConditionsLegales", "Date d'acception des conditions", SqlColumnTypes::$DATETIME ),
                new SqlColumnMappgin ( "conditionsLegales", "Conditions légales acceptées", SqlColumnTypes::$LONGTEXT ),
                new SqlColumnMappgin ( "quantite", "Quantité souscrite", SqlColumnTypes::$NUMERIC )
            );
            
            InscriptionPersonneProduit::$memberDeclaration = array_merge ( InscriptionPersonneProduit::$memberDeclaration, HasMetaData::getMembersDeclaration () );
        }
        
        return InscriptionPersonneProduit::$memberDeclaration;
    }
    public function getShortToString() {
        return "Inscription d'une personne à un produit ".$this->idIPP;
    }
    protected function getNaturalOrderColumn() {
        return "idIPP";
    }
    
    public function getTableName() {
        return "inscription_personne_produit";
    }
    
    public function getAllForInscription($idInscription) {
        $q = "select * from inscription_personne_produit where fkInscription = ".$idInscription;
        return $this->getObjectListFromQuery ( $q );
    }
    
}