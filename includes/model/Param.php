<?php
class ParamKey {
	public $key;
	public function __construct($key) {
		$this->key = $key;
	}
}
final class PKeys {
	public static $HOME_TITLE;
	public static $HOME_TEXT;
	public static $HOME_ACTIVITY_NBJOURS;
	public static $PUBLICATION_HEADER;
	public static $MAIL_SPOOL_SIZE;
	public static $MAIL_SPOOL_RUNNING;
	public static $MAIL_MAX_TENTATIVES;
	public static $MAIL_RAPPEL_SET_PASSWORD;
	public static $PROPOSITION_ALERT_LAST_EXEC;
	public static $CONTACT_EMAIL;
	public static $CONTACT_TEXT;
	public static $SNB_API_KEY;
}
PKeys::$HOME_TITLE = new ParamKey ( "HOME_TITLE" );
PKeys::$HOME_TEXT = new ParamKey ( "HOME_TEXT" );
PKeys::$HOME_ACTIVITY_NBJOURS = new ParamKey ( "HOME_ACTIVITY_NBJOURS" );
PKeys::$PUBLICATION_HEADER = new ParamKey ( "PUBLICATION_HEADER" );
PKeys::$MAIL_SPOOL_SIZE = new ParamKey ( "MAIL_SPOOL_SIZE" );
PKeys::$MAIL_SPOOL_RUNNING = new ParamKey ( "MAIL_SPOOL_RUNNING" );
PKeys::$MAIL_MAX_TENTATIVES = new ParamKey ( "MAIL_MAX_TENTATIVES" );
PKeys::$MAIL_RAPPEL_SET_PASSWORD = new ParamKey ( "MAIL_RAPPEL_SET_PASSWORD" );
PKeys::$PROPOSITION_ALERT_LAST_EXEC = new ParamKey ( "PROPOSITION_ALERT_LAST_EXEC" );
PKeys::$CONTACT_EMAIL = new ParamKey ( "CONTACT_EMAIL" );
PKeys::$CONTACT_TEXT = new ParamKey ( "CONTACT_TEXT" );
PKeys::$SNB_API_KEY = new ParamKey ( "SNB_API_KEY" );
class Param extends Persistant {
	public $pKey;
	public $pValue;
	public function getPrimaryKey() {
		return $this->pKey;
	}
	public function setPrimaryKey($newId) {
		$this->pKey = $newId;
	}
	private static $memberDeclaration = null;
	static function getMembersDeclaration() {
		if (is_null ( Param::$memberDeclaration )) {
			$pk = new SqlColumnMappgin ( "pKey", null, SqlColumnTypes::$VARCHAR, 30 );
			$pk->setPrimaryKey ( true );
			
			Param::$memberDeclaration = array (
					$pk,
					new SqlColumnMappgin ( "pValue", "Valeur", SqlColumnTypes::$LONGTEXT ) 
			);
		}
		
		return Param::$memberDeclaration;
	}
	public function getShortToString() {
		return $this->pKey . " = " . $this->pValue;
	}
	protected function getNaturalOrderColumn() {
		return "pKey";
	}
	private static $CACHE = null;
	private static function initCache() {
		$p = new Param ();
		Param::$CACHE = array ();
		foreach ( $p->getAll () as $aParam ) {
			Param::$CACHE [$aParam->pKey] = $aParam->pValue;
		}
	}
	public static function getValue(ParamKey $key, $defaultValue = null) {
		if (Param::$CACHE == null)
			Param::initCache ();
		if (array_key_exists ( $key->key, Param::$CACHE ))
			return Param::$CACHE [$key->key];
		else
			return $defaultValue;
	}
}
