<?php
class Erreur extends HasMetaData {
	public $id;
	public $ip;
	public $forwardedFor;
	public $referer;
	public $userAgent;
	public $args;
	public $actions;
	public $session;
	
	public $exceptionMessage;
	public $exceptionStack;
	
	public function getPrimaryKey() {
		return $this->id;
	}
	public function setPrimaryKey($newId) {
		$this->id = $newId;
	}
	private static $memberDeclaration;
	static function getMembersDeclaration() {
		if (is_null ( Erreur::$memberDeclaration )) {
			$pk = new SqlColumnMappgin ( "id", null, SqlColumnTypes::$INTEGER );
			$pk->setPrimaryKey ( true );
			
			Erreur::$memberDeclaration = array (
					$pk,
					new SqlColumnMappgin ( "ip", "Client IP", SqlColumnTypes::$VARCHAR, 35 ),
					new SqlColumnMappgin ( "forwardedFor", "Forwarded For", SqlColumnTypes::$VARCHAR, 35 ),
					new SqlColumnMappgin ( "args", "Args", SqlColumnTypes::$LONGTEXT ),
					new SqlColumnMappgin ( "actions", "Actions", SqlColumnTypes::$LONGTEXT ),
					new SqlColumnMappgin ( "session", "Session", SqlColumnTypes::$LONGTEXT ),
					new SqlColumnMappgin ( "exceptionStack", "Stack Trace", SqlColumnTypes::$LONGTEXT ),
					new SqlColumnMappgin ( "exceptionMessage", "Message de l'exception", SqlColumnTypes::$VARCHAR, 255 ),
					new SqlColumnMappgin ( "userAgent", "User Agent", SqlColumnTypes::$VARCHAR, 255 ),
					new SqlColumnMappgin ( "referer", "Referer", SqlColumnTypes::$VARCHAR, 255 ), 
					
			);
			
			Erreur::$memberDeclaration = array_merge ( Erreur::$memberDeclaration, HasMetaData::getMembersDeclaration () );
		}
		
		return Erreur::$memberDeclaration;
	}
	public function getShortToString() {
		return $this->lastUpdateOn->format("Y-m-d H:i:s"). " par ". $this->ip . " : " . $this->exceptionMessage;
	}
	protected function getNaturalOrderColumn() {
		return "lastUpdateOn";
	}
	
	public function send(){
		$msg = "<p>Une erreur vient de se produite sur le site " . SITE_ROOT . ".</p>";
		$msg .= "<p>Referer :{$this->referer}</p>";
		$msg .= "<p>Args :</p>";
		$msg .= $this->args;
		$msg .= "<p>Actions :</p>";
		$msg .= $this->actions;
		$msg .= "<p>Parametres de la session :</p>";
		$msg .= $this->session;
		$msg .= "<p>User Agent :</p>";
		$msg .= $this->userAgent;
		$msg .= "<p>Remote ADR :</p>";
		$msg .= $this->ip;
		$msg .= " <a href='". SITE_ROOT ."index.php?blacklist=".$this->ip."'>Ajouter à la blacklist</a>";
		$msg .= "<p>HTTP X Forwarded for :</p>";
		$msg .= $this->forwardedFor;
		$msg .= " <a href='". SITE_ROOT ."index.php?blacklist=".$this->forwardedFor."'>Ajouter à la blacklist</a>";
		$msg .= "<p>Exception Message :</p>";
		$msg .= $this->exceptionMessage;
		$msg .= "<p>Stack Trace :</p>";
		$msg .= $this->exceptionStack;
		
		try {
			sendSimpleMail ( "[" . SITE_TITLE . "] Erreur de navigation sur " . SITE_ROOT, $msg, EMAIL_ON_ERROR, false );
		} catch ( Exception $e ) {
		}
	}
	
	//Recherche toutes les erreurs produites par la même adresse IP, puis le bloque s'il en fait trop.
	public function scanAndLock(){
		
		$sql = "select * from erreur where ip = '{$this->ip}' and lastUpdateOn > NOW() - INTERVAL 1 HOUR";
		$list = $this->getObjectListFromQuery($sql);

		if(sizeof($list) > 10){
			blackListIp($this->ip);
			if( !is_null($this->forwardedFor) && strlen($this->forwardedFor) > 0){
				blackListIp($this->forwardedFor);
			}
		}
	}
	
	public function getAllErrorsFromIp($ip){
	
		$sql = "select * from erreur where ip = '{$ip}' order by lastUpdateOn asc ";
		return $this->getObjectListFromQuery($sql);
	
	}
	
	public function dropAll(){
		$sql = "truncate erreur";
		$this->ask($sql);
	}
	
}

?>