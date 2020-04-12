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
                $produit
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
    
}