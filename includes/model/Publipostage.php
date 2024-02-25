<?php
class Publipostage extends HasMetaData {
	public $idPublipostage;
	public $objet;
	public $message;
	public $sentByEmail;
	public $sentByMail;
	public function getPrimaryKey() {
		return $this->idPublipostage;
	}
	public function setPrimaryKey($newId) {
		$this->idPublipostage = $newId;
	}
	public function getShortToString() {
		return $this->objet;
	}
	public function getNaturalOrderColumn() {
		return "lastUpdateOn desc";
	}
	private static $memberDeclaration = null;
	static function getMembersDeclaration() {
		if (is_null ( Publipostage::$memberDeclaration )) {
			$pk = new SqlColumnMappgin ( "idPublipostage", null, SqlColumnTypes::$INTEGER, "5" );
			$pk->setPrimaryKey ( true );
			
			Publipostage::$memberDeclaration = array (
					$pk,
					new SqlColumnMappgin ( "objet", "Objet du message", SqlColumnTypes::$VARCHAR, "256" ),
					new SqlColumnMappgin ( "message", "Message", SqlColumnTypes::$LONGTEXT ),
					new SqlColumnMappgin ( "sentByEmail", "Message a été envoyé par email", SqlColumnTypes::$BOOLEAN ),
					new SqlColumnMappgin ( "sentByMail", "Message a été envoyé par courrier", SqlColumnTypes::$BOOLEAN ) 
			);
			
			Publipostage::$memberDeclaration = array_merge ( Publipostage::$memberDeclaration, HasMetaData::getMembersDeclaration () );
		}
		
		return Publipostage::$memberDeclaration;
	}
	public function delete() {
		$desti = new PublipostageDestinataire ();
		$destiList = $desti->getAllForPublipostage ( $this->getPrimaryKey () );
		foreach ( $destiList as $aDestinataire ) {
			$aDestinataire->delete ();
		}
		
		parent::delete ();
	}
}

