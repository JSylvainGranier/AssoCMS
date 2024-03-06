<?php
class PublipostageDestinataire extends HasMetaData {
	public $idPubDest;
	/**
	 *
	 * @var Publipostage
	 */
	public $publipostage;
	
	/**
	 *
	 * @var Personne
	 */
	public $destinataire;
	public function getPrimaryKey() {
		return $this->idPubDest;
	}
	public function setPrimaryKey($newId) {
		$this->idPubDest = $newId;
	}
	public function getShortToString() {
		return $this->publipostage->objet . " a destination de " . $this->destinataire->getPrenomNom ();
	}
	public function getNaturalOrderColumn() {
		return null;
	}
	private static $memberDeclaration = null;
	static function getMembersDeclaration() {
		if (is_null ( PublipostageDestinataire::$memberDeclaration )) {
			$pk = new SqlColumnMappgin ( "idPubDest", null, SqlColumnTypes::$INTEGER, "5" );
			$pk->setPrimaryKey ( true );
			
			$publipostage = new SqlColumnMappgin ( "publipostage", "Publipostage", SqlColumnTypes::$INTEGER );
			$publipostage->setForeing ( new Publipostage (), "publipostage", true, true );
			
			$destinataire = new SqlColumnMappgin ( "destinataire", "Destinataire du publipostage", SqlColumnTypes::$INTEGER );
			$destinataire->setForeing ( new Personne (), "destinataire", true, true );
			
			PublipostageDestinataire::$memberDeclaration = array (
					$pk,
					$publipostage,
					$destinataire 
			);
		}
		
		return PublipostageDestinataire::$memberDeclaration;
	}
	
	/**
	 *
	 * @return Personne
	 */
	public function getDestinataire() {
		return $this->fetchLasyObject ( "destinataire", "Personne" );
	}
	
	/**
	 *
	 * @return Publipostage
	 */
	public function getPublipostage() {
		return $this->fetchLasyObject ( "publipostage", "Publipostage" );
	}
	
	/**
	 *
	 * @return array Publipostage
	 */
	public function getAllForPublipostage($idPublipostage) {
		$sql = "select dp.* from {$this->getTableName()} dp left outer join personne pers on pers.idPersonne = destinataire  where publipostage = {$idPublipostage}
		 order by pers.nom, pers.prenom";
		return $this->getObjectListFromQuery ( $sql );
	}
	public function cleanDoublons() {
		$sql = "select * from publipostage_destinataire group by destinataire,publipostage having count(*) > 1";
		$toDelete = $this->getObjectListFromQuery ( $sql );
		foreach ( $toDelete as $aDoublon ) {
			$aDoublon->delete ();
		}
	}
	public function getTableName() {
		return "publipostage_destinataire";
	}
	
	public static function deleteForPersonne(Personne $p){
		$sql = "delete from publipostage_destinataire where destinataire = ".$p->idPersonne;
		PublipostageDestinataire::ask($sql);
	}
}

