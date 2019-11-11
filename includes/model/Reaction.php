<?php
class Reaction extends HasMetaData {
	public $idReaction;
	public $message;
	public $auteur;
	public $page;
	public $enReactionA;
	public $dateRedaction;
	function __construct() {
		$this->dateRedaction = new MyDateTime ();
	}
	public function getPrimaryKey() {
		return $this->idReaction;
	}
	public function setPrimaryKey($newId) {
		$this->idReaction = $newId;
	}
	private static $memberDeclaration;
	static function getMembersDeclaration() {
		if (is_null ( Reaction::$memberDeclaration )) {
			$pk = new SqlColumnMappgin ( "idReaction", null, SqlColumnTypes::$INTEGER );
			$pk->setPrimaryKey ( true );
			
			$page = new SqlColumnMappgin ( "fkPage", "Page à laquelle est rattachée cette réaction", SqlColumnTypes::$INTEGER );
			$page->setForeing ( new Page (), "page", true, true );
			
			$auteur = new SqlColumnMappgin ( "fkAuteur", "Personne ayant écrit cette réaction", SqlColumnTypes::$INTEGER );
			$auteur->setForeing ( new Personne (), "auteur", false, true );
			
			$reactionA = new SqlColumnMappgin ( "fkReactionA", "En réaction à une autre réaction", SqlColumnTypes::$INTEGER );
			$reactionA->setForeing ( new Personne (), "enReactionA", false, true );
			
			$dCrea = new SqlColumnMappgin ( "dateRedaction", "Date à laquelle le message a été créé.", SqlColumnTypes::$DATETIME );
			$dCrea->isNullable = false;
			
			Reaction::$memberDeclaration = array (
					$pk,
					$page,
					$auteur,
					$reactionA,
					$dCrea,
					new SqlColumnMappgin ( "message", "Message de la réaction", SqlColumnTypes::$LONGTEXT ) 
			);
			
			Reaction::$memberDeclaration = array_merge ( Reaction::$memberDeclaration, HasMetaData::getMembersDeclaration () );
		}
		
		return Reaction::$memberDeclaration;
	}
	public function getShortToString() {
		return "Réaction de " . $this->getAuteur ()->nom . " à la page " . $this->getPage ()->titre;
	}
	protected function getNaturalOrderColumn() {
		return "lastUpdateOn";
	}
	
	/**
	 * Retourne l'auteur de cette réaction.
	 *
	 * @return Personne
	 */
	public function getAuteur() {
		return $this->fetchLasyObject ( "auteur", "Personne" );
	}
	
	/**
	 * Retourne la Page à laquelle est liée cette réaction.
	 *
	 * @return Page
	 */
	public function getPage() {
		return $this->fetchLasyObject ( "page", "Page" );
	}
	
	/**
	 * Retourne la Réaction à laquelle est liée celle-ci, la réaction parante.
	 *
	 * @return Reaction
	 */
	public function getReactionA() {
		return $this->fetchLasyObject ( "enReactionA", "Reaction" );
	}
	
	/**
	 * Retourne une liste des réactions en relation à la page
	 * dont l'id est passé en paramètre.
	 *
	 * @param int $idPage        	
	 * @return array
	 */
	public function findListForPage($idPage) {
		$sql = "select * from " . $this->getTableName () . " where fkPage = " . $idPage . " order by dateRedaction";
		return $this->getObjectListFromQuery ( $sql );
	}
	
	public static function deleteForPersonne(Personne $p){
		$sql = "delete from reaction where fkAuteur = ".$p->idPersonne;
		Reaction::ask($sql);
	}
	
	public static function deleteForPage(Page $page){
		$sql = "delete from reaction where fkPage = ".$page->idPage;
		Reaction::ask($sql);
	}
}

?>