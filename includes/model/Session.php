<?php
class Session extends HasMetaData {
	public $idSession;
	public $fkIdPersonne;
	public $longSessionToken;
	public $nbReUse = 0;

	public function getPrimaryKey() {
		return $this->idSession;
	}
	public function setPrimaryKey($newId) {
		$this->idSession = $newId;
	}
	private static $memberDeclaration;
	static function getMembersDeclaration() {
		if (is_null ( Session::$memberDeclaration )) {
			$pk = new SqlColumnMappgin ( "idSession", null, SqlColumnTypes::$INTEGER );
			$pk->setPrimaryKey ( true );
			
			Session::$memberDeclaration = array (
					$pk,
					new SqlColumnMappgin ( "fkIdPersonne", "Personne de cette session", SqlColumnTypes::$INTEGER ),
					new SqlColumnMappgin ( "longSessionToken", "Token", SqlColumnTypes::$VARCHAR, 255 ),
					new SqlColumnMappgin ( "nbReUse", "Personne de cette session", SqlColumnTypes::$INTEGER )
			);
			
			Session::$memberDeclaration = array_merge ( Session::$memberDeclaration, HasMetaData::getMembersDeclaration () );
		}
		
		return Session::$memberDeclaration;
	}
	
	protected function getNaturalOrderColumn() {
		return "lastUpdateOn";
	}
	
	public function findByLongSessionToken($lstk){
		$sql = "select * from sessions where longSessionToken = '$lstk' limit 1";
		return $this->getOneObjectOrNullFromQuery($sql );
	}
	
	
	
	public function getShortToString(){
		$pers = new Personne($this->fkIdPersonne);
		return "Session de ".$pers->getNomPrenom()." du ".$this->lastUpdateOn." raffraîchie ".$this->nbReUse." foix.";
	}
	
	public function getTableName() {
		return "sessions";
	}
}

?>