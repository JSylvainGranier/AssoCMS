<?php
class Produit extends HasMetaData {
    public $idProduit;
    public $libelle;
    public $description;
    public $politiqueTarifaire; //{tarif unique, 22€, fonction de l'age : 0-12 : 13€, 13-21€ : 19€, 22-110 : 27€}
    public $conditionsLegales;
    public $debutDisponibilite;
    public $finDisponibilite;
    public $quantiteDisponible;
    public $produitRequis;
    public $produitOrdre = 0;
    public $accesDirect = 0;
    public $quantiteLibre = 0;
    public $idCategorieAffecter = 0;
    public $archive = false;
    
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
                new SqlColumnMappgin ( "description", "Description du produit", SqlColumnTypes::$LONGTEXT ),
                new SqlColumnMappgin ( "politiqueTarifaire", "Politique tarifaire", SqlColumnTypes::$LONGTEXT ),
                new SqlColumnMappgin ( "conditionsLegales", "Conditions légales à accepter lors de la souscription", SqlColumnTypes::$LONGTEXT ),
                new SqlColumnMappgin ( "debutDisponibilite", "Disponible entre le ", SqlColumnTypes::$DATETIME ),
                new SqlColumnMappgin ( "finDisponibilite", "Et le", SqlColumnTypes::$DATETIME ),
                new SqlColumnMappgin ( "quantiteDisponible", "Quantité disponible", SqlColumnTypes::$INTEGER ),
                new SqlColumnMappgin ( "produitRequis", "Produit requis pour avoir droit à ce produit", SqlColumnTypes::$INTEGER ),
                new SqlColumnMappgin ( "produitOrdre", "Ordre du produit dans le groupe", SqlColumnTypes::$INTEGER, 0 ),
                new SqlColumnMappgin ( "accesDirect", "Produit que l'utilisateur peut cocher lui-même", SqlColumnTypes::$BOOLEAN, false ),
                new SqlColumnMappgin ( "quantiteLibre", "L'utilisateur peut lui-même choisir la quantité qu'il commande", SqlColumnTypes::$BOOLEAN, false ),
                new SqlColumnMappgin ( "archive", "Est un élément archivé", SqlColumnTypes::$BOOLEAN )
            );
            
            Produit::$memberDeclaration = array_merge ( Produit::$memberDeclaration, HasMetaData::getMembersDeclaration () );
        }
        
        return Produit::$memberDeclaration;
    }
    public function getShortToString() {
        return "Produit ".$this->libelle;
    }
    protected function getNaturalOrderColumn() {
        return "debutDisponibilite";
    }
    
    public function getAllActive(){
        $q = "select * from produit where archive = false";
        return $this->getObjectListFromQuery ( $q );
    }

    public function hasInscriptionsOuvertesEnCeMoment(){
        $q = "select count(*) from produit where debutDisponibilite < now() and finDisponibilite > now() and accesDirect = 1 and archive = false";
        $resultSet = $this->ask($q);
        while ( $data = mysql_fetch_assoc ( $resultSet ) ) {
            return $data["count(*)"] > 0;
        }
    }
    
    public function getInscriptionsOuvertesEnCeMoment($adminMode){
        $q = "select * from produit where debutDisponibilite < now() and finDisponibilite > now() and accesDirect = 1 and archive = false order by produitOrdre desc;";

        if($adminMode == true){
            $q = "select * from produit where archive = false and debutDisponibilite < now() and finDisponibilite  + interval 280 day  > now() and accesDirect = 1 order by produitOrdre desc;";    
        }

        return $this->getObjectListFromQuery ( $q );
    }
    
    public function getInscritsOuPasSurCeProduit(){
        $q = "select pers.idPersonne, pers.idFamille, pers.nom, pers.prenom, ipp.fkProduit, i.etat, ipp.quantite
        from personne pers
        left outer join inscription_personne_produit ipp on ipp.fkPersonne = pers.idPersonne
        left outer join inscription i on i.idInscription = ipp.fkInscription
        where (ipp.fkProduit = {$this->idProduit} or ipp.fkProduit is null)
        and (i.etat in (20, 50, 70) or i.etat is null)
        order by pers.nom, pers.prenom";
        
        $ret = array();
        
        $resultSet = $this->ask($q);
        while ( $data = mysql_fetch_assoc ( $resultSet ) ) {
            $ret[] = $data;
        }
        
        return $ret;
    }
    
    public function getAll() {
        $query = "select * from produit order by finDisponibilite, produitOrdre";
        $objList = $this->getObjectListFromQuery ( $query );
        return $objList;
    }
    
}
