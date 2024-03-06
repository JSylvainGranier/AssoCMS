<?php
abstract class HasMetaData extends Persistant {
	public $lastUpdateOn;
	public $lastUpdateBy;
	private static $members = null;
	static function getMembersDeclaration() {
		if (is_null ( HasMetaData::$members )) {
			
			$persCol = new SqlColumnMappgin ( "fkLastUpdateBy", "Dernier utilisateur ayant modifié l'objet", SqlColumnTypes::$INTEGER );
			$persCol->setForeing ( new Personne (), "lastUpdateBy", false, true );
			
			HasMetaData::$members = array (
					new SqlColumnMappgin ( "lastUpdateOn", "Date de dernière mise à jour", SqlColumnTypes::$DATETIME, "60" ),
					$persCol 
			);
		}
		
		return HasMetaData::$members;
	}
	public function getLastUpdatePersonne() {
		return $this->fetchLasyObject ( "lastUpdateBy", "Personne" );
	}
	public function getLastUpdateOn() {
		return $this->lastUpdateOn;
	}
}

