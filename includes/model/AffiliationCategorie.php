<?php
class AffiliationCategorie extends HasMetaData {
	public $idPersonneCategorie;
	public $personne;
	public $categorie;
	public function getPrimaryKey() {
		return $this->idPersonneCategorie;
	}
	public function setPrimaryKey($newId) {
		$this->idPersonneCategorie = $newId;
	}
	private static $memberDeclaration = null;
	static public function getMembersDeclaration() {
		if (is_null ( AffiliationCategorie::$memberDeclaration )) {
			
			$pk = new SqlColumnMappgin ( "idPersonneCategorie", null, SqlColumnTypes::$INTEGER );
			$pk->setPrimaryKey ( true );
			
			$persCol = new SqlColumnMappgin ( "fkPersonne", "Personne affiliée à cette catégorie", SqlColumnTypes::$INTEGER );
			$persCol->setForeing ( new Personne (), "personne", false, true );
			
			$catCol = new SqlColumnMappgin ( "fkCategorie", "Catégorie d'affiliation de la personne", SqlColumnTypes::$INTEGER );
			$catCol->setForeing ( new Categorie (), "categorie", false, true );
			
			AffiliationCategorie::$memberDeclaration = array (
					$pk,
					$catCol,
					$persCol 
			);
			
			AffiliationCategorie::$memberDeclaration = array_merge ( AffiliationCategorie::$memberDeclaration, HasMetaData::getMembersDeclaration () );
		}
		
		return AffiliationCategorie::$memberDeclaration;
	}
	public function getShortToString() {
		return $this->personne->prenom . " est affilié(e) à la catégorie " . $this->categorie->nom;
	}
	public function getNaturalOrderColumn() {
		return "fkCategorie";
	}
	public function getTableName() {
		return "personne_categorie";
	}
	public function getCategorie() {
		$this->fetchLasyObject ( "categorie", "Categorie" );
	}
	public function getPersonne() {
		$this->fetchLasyObject ( "personne", "Personne" );
	}
	
	public static function deleteForPersonne(Personne $pers){
		$sql = "delete from personne_categorie where fkPersonne = ".$pers->idPersonne;
		AffiliationCategorie::ask($sql);
	}
	
	public function findForPersonneEtCategorie($idPersonne, $idCategorie){
		$sql = "select * from personne_categorie where fkPersonne = {$idPersonne} and fkCategorie = {$idCategorie}";
		return $this->getObjectListFromQuery ( $sql );
	}
}
