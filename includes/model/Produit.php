<?php
class Produit extends HasMetaData {
    public $idProduit;
    public $libelle;
    public $politiqueTarifaire; //{tarif unique, 22€, fonction de l'age : 0-12 : 13€, 13-21€ : 19€, 22-110 : 27€}
    public $debutDisponibilite;
    public $finDisponibilite;
    public $quantiteDisponible;
    
    public function getPrimaryKey() {
        return $this->idProduit;
    }
    public function setPrimaryKey($newId) {
        $this->idProduit = newId;
    }
    private static $memberDeclaration;
    static function getMembersDeclaration() {
        if (is_null ( Produit::$memberDeclaration )) {
            $pk = new SqlColumnMappgin ( "idProduit", null, SqlColumnTypes::$INTEGER );
            $pk->setPrimaryKey ( true );
            
            Produit::$memberDeclaration = array (
                $pk,
                new SqlColumnMappgin ( "libelle", "Libellé du produit", SqlColumnTypes::$VARCHAR, 255 ),
                new SqlColumnMappgin ( "politiqueTarifaire", "Politique tarifaire", SqlColumnTypes::$LONGTEXT ),
                new SqlColumnMappgin ( "debutDisponibilite", "Disponible entre le ", SqlColumnTypes::$DATETIME ),
                new SqlColumnMappgin ( "finDisponibilite", "Et le", SqlColumnTypes::$DATETIME ),
                new SqlColumnMappgin ( "quantiteDisponible", "Quantité disponible", SqlColumnTypes::$INTEGER ),
            );
            
            Produit::$memberDeclaration = array_merge ( Produit::$memberDeclaration, HasMetaData::getMembersDeclaration () );
        }
        
        return Produit::$memberDeclaration;
    }
    public function getShortToString() {
        return "Produit ".$this->libelle;
    }
    protected function getNaturalOrderColumn() {
        return "debutDisponibilité";
    }
}
