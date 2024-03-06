<?php
abstract class Persistant {
	private static $CONN;
	protected static function getCon() {
		if (is_null ( Persistant::$CONN )) {
			@Persistant::$CONN = mysql_connect ( DB_HOST, DB_LOGIN, DB_PASSWORD, DB_NAME );
			
			if (Persistant::$CONN === false) {
				throw new Exception ( "Erreur MySQL : Erreur de connexion au serveur ou à la BDD" );
			}
			
			mysql_select_db ( DB_NAME );
			mysql_query ( "SET NAMES 'utf8'" );
			mysql_query ( "SET CHARACTER SET utf8 " );
		}
		
		return Persistant::$CONN;
	}
	
	public function __construct($id = null) {
		if ($id != null && strlen ( $id ) > 0) {
			
			$fromDb = $this->findById ( $id );
			if ($fromDb != null) {
				
				$attributes = get_object_vars ( $fromDb );
				
				foreach ( $attributes as $attrbName => $attrbValue ) {
					$this->$attrbName = $attrbValue;
				}
			} else {
				throw new NoExistOnDbException ( "Impossible de trouver d'objet ayant l'id " . $id );
			}
		}
	}
	public static function testDb() {
		$rs = Persistant::ask ( "SHOW TABLES FROM " . DB_NAME );
		return ! is_null ( $rs ) && $rs != false;
	}
	public static function createTables() {
		try {
			
			$categorie = new Categorie ();
			$evenement = new Evenement ();
			$personne = new Personne ();
			$persCat = new AffiliationCategorie ();
			$page = new Page ();
			$reaction = new Reaction ();
			$param = new Param ();
			$publipostage = new Publipostage ();
			$publiDesti = new PublipostageDestinataire ();
			$mail = new Mail ();
			$attachment = new Attachment ();
			
			Persistant::ask ( $personne->getCreateTableQuery ( true ) );
			Persistant::ask ( $categorie->getCreateTableQuery ( true ) );
			Persistant::ask ( $persCat->getCreateTableQuery ( true ) );
			Persistant::ask ( $page->getCreateTableQuery ( true ) );
			Persistant::ask ( $reaction->getCreateTableQuery ( true ) );
			Persistant::ask ( $evenement->getCreateTableQuery ( true ) );
			Persistant::ask ( $param->getCreateTableQuery ( true ) );
			Persistant::ask ( $publipostage->getCreateTableQuery ( true ) );
			Persistant::ask ( $publiDesti->getCreateTableQuery ( true ) );
			Persistant::ask ( $mail->getCreateTableQuery ( true ) );
			Persistant::ask ( $attachment->getCreateTableQuery ( true ) );
		} catch ( Exception $e ) {
			return "Sur erreur : " . $e->getMessage ();
		}
		
		return "Fait";
	}
	public static function dropTables() {
		$categorie = new Categorie ();
		$evenement = new Evenement ();
		$personne = new Personne ();
		$persCat = new AffiliationCategorie ();
		$page = new Page ();
		$reaction = new Reaction ();
		$mail = new Mail ();
		
		try {
			Persistant::ask ( "drop table {$evenement->getTableName()} cascade;" );
		} catch ( Exception $e ) {
		}
		;
		
		try {
			Persistant::ask ( "drop table {$reaction->getTableName()} cascade;" );
		} catch ( Exception $e ) {
		}
		;
		try {
			Persistant::ask ( "drop table {$page->getTableName()} cascade;" );
		} catch ( Exception $e ) {
		}
		;
		try {
			Persistant::ask ( "drop table {$persCat->getTableName()} cascade;" );
		} catch ( Exception $e ) {
		}
		;
		try {
			Persistant::ask ( "drop table {$categorie->getTableName()} cascade;" );
		} catch ( Exception $e ) {
		}
		;
		try {
			Persistant::ask ( "drop table {$mail->getTableName()} cascade;" );
		} catch ( Exception $e ) {
		}
		;
		try {
			Persistant::ask ( "drop table {$personne->getTableName()} cascade;" );
		} catch ( Exception $e ) {
		}
		;
		
		return "Fait";
	}
	public static function getMembersDeclaration() {
	}
	public abstract function getPrimaryKey();
	public abstract function setPrimaryKey($newId);
	public abstract function getShortToString();
	/**
	 * Retourne le nom de la colonne utilisée pour
	 * un classement naturel.
	 * Peut renvoyer null, indiquant qu'il n'existe pas d'ordre naturel.
	 */
	protected abstract function getNaturalOrderColumn();
	
	/**
	 *
	 * @return number
	 */
	public function getLastInsertedId() {
		return mysql_insert_id ();
	}
	public function getCreateTableQuery($ifNotExists = false) {
		$class = $this->getTableName ();
		$strIfNotExists = $ifNotExists ? "IF NOT EXISTS " : "";
		$str = "CREATE TABLE {$strIfNotExists}{$class} ( ";
		
		$foreing = null;
		
		$members = $this->getMembersDeclaration ();
		
		$nbMembers = count ( $members );
		$idx = 1;
		
		foreach ( $members as $aMember ) {
			/* @var $aMember SqlColumnMappgin  */
			$colDeclaration = $aMember->columnName . " " . $aMember->sqlType;
			
			if (! is_null ( $aMember->sqlLenght ) && $aMember->sqlType == SqlColumnTypes::$VARCHAR) {
				$colDeclaration .= '(' . $aMember->sqlLenght . ')';
			}
			
			if (! is_null ( $aMember->defaultValue )) {
				$colDeclaration .= ' DEFAULT ' . Persistant::toSql ( $aMember->defaultValue );
			}
			
			if ($aMember->isNullable) {
				$colDeclaration .= " NULL";
			} else {
				$colDeclaration .= " NOT NULL";
			}
			
			if ($aMember->isPrimaryKey) {
				$colDeclaration .= " PRIMARY KEY";
			}
			
			if ($aMember->isAutoIncrement && $aMember->sqlType == SqlColumnTypes::$INTEGER) {
				$colDeclaration .= " AUTO_INCREMENT";
			}
			
			if (! is_null ( $aMember->foreignTableName )) {
				if (! is_null ( $foreing )) {
					$foreing .= ", \n";
				}
				$foreing .= "FOREIGN KEY ({$aMember->columnName}) REFERENCES {$aMember->foreignTableName}({$aMember->foreignTablePrimaryColumn}) ";
				if ($aMember->foreignOnDeleteCascade) {
					$foreing .= " ON DELETE CASCADE ";
				}
			}
			
			$str .= $colDeclaration;
			
			if ($idx < $nbMembers) {
				$str .= ', ';
			}
			
			$str .= "\n";
			$idx ++;
		}
		
		if (! is_null ( $foreing )) {
			$str .= ",\n " . $foreing;
		}
		
		$str .= " ) ; \n";
		
		return $str;
	}
	public function getDeleteQuery() {
		$pkCol = $this->getPrimaryKeyColumnName ();
		$sqlId = $this->toSql ( $this->getPrimaryKey () );
		$sql = "DELETE FROM {$this->getTableName()} where {$pkCol}={$sqlId}";
		return $sql;
	}
	public function getInsertQuery() {
		$this->checkNotNullFields ();
		
		$sql = "INSERT INTO " . $this->getTableName () . " (";
		
		$fieldsToInsert = array ();
		
		foreach ( $this->getMembersDeclaration () as $aColumn ) {
			$member = $this->getMemberByName ( $aColumn->objectName );
			
			if (! is_null ( $member )) {
				$sqlField = Persistant::toSql ( $member, $aColumn );
				
				$fieldsToInsert [$aColumn->columnName] = $sqlField;
			}
		}
		
		$fieldsCount = count ( $fieldsToInsert );
		
		if ($fieldsCount == 0)
			throw new Exception ( "Il n'y avait rien à insérer" );
		
		$idx = 1;
		foreach ( $fieldsToInsert as $colName => $colValue ) {
			$sql .= $colName;
			if ($idx == $fieldsCount) {
				$sql .= ") VALUES (";
			} else {
				$sql .= ", ";
			}
			$idx ++;
		}
		
		$idx = 1;
		foreach ( $fieldsToInsert as $colName => $colValue ) {
			$sql .= $colValue;
			if ($idx == $fieldsCount) {
				$sql .= ");";
			} else {
				$sql .= ", ";
			}
			$idx ++;
		}
		
		return $sql;
	}
	private function getPrimaryKeyColumnName() {
		foreach ( $this->getMembersDeclaration () as $aColumn ) {
			/* @var $aColumn SqlColumnMappgin */
			if ($aColumn->isPrimaryKey)
				return $aColumn->columnName;
		}
	}
	public function getUpdateQuery() {
		$this->checkNotNullFields ();
		
		$sql = "UPDATE " . $this->getTableName () . " SET ";
		
		$fieldsToUpdate = array ();
		
		$primaryKeyColumName = $this->getPrimaryKeyColumnName ();
		
		foreach ( $this->getMembersDeclaration () as $aColumn ) {
			/* @var $aColumn SqlColumnMappgin */
			$member = $this->getMemberByName ( $aColumn->objectName );
			
			$sqlField = Persistant::toSql ( $member, $aColumn );
			
			$fieldsToUpdate [$aColumn->columnName] = $sqlField;
		}
		
		if (is_null ( $primaryKeyColumName ) || is_null ( $this->getPrimaryKey () ))
			throw new Exception ( "Impossible de faire un update sur une entité qui n'a pas de clef primaire." );
		
		$fieldsCount = count ( $fieldsToUpdate );
		
		if ($fieldsCount == 0)
			throw new Exception ( "Il n'y avait rien à modifier" );
		
		$idx = 1;
		foreach ( $fieldsToUpdate as $colName => $colValue ) {
			$sql .= $colName . " = " . $colValue;
			if ($idx == $fieldsCount) {
				$sql .= " ";
			} else {
				$sql .= ", ";
			}
			$idx ++;
		}
		
		$sql .= "WHERE {$primaryKeyColumName} = " . Persistant::toSql ( $this->getPrimaryKey () );
		
		return $sql;
	}
	public function save() {
		$sqlInsertOrUpdate = "oups!";
		$currentPrimaryKey = $this->getPrimaryKey ();
		
		if ($this instanceof HasMetaData) {
			$this->lastUpdateOn = new MyDateTime ();
			if (! is_null ( $_SESSION ) && array_key_exists ( "userId", $_SESSION )) {
				$this->lastUpdateBy = thisUserId() + 0;
			} else {
				$this->lastUpdateBy = null;
			}
		}
		
		if ($currentPrimaryKey = ! null && sizeof ( $currentPrimaryKey ) > 0) {
			$pkSql = $this->toSql ( $this->getPrimaryKey () );
			$sqlExist = "SELECT EXISTS(SELECT 1 FROM {$this->getTableName()} WHERE {$this->getPrimaryKeyColumnName()}={$pkSql}) as exi";
			
			$resultArray = Persistant::getDataFromQuery ( $sqlExist );
			
			if ($resultArray [0] ["exi"]) {
				$sqlInsertOrUpdate = $this->getUpdateQuery ();
			} else {
				$sqlInsertOrUpdate = $this->getInsertQuery ();
			}
		} else {
			$sqlInsertOrUpdate = $this->getInsertQuery ();
		}
		$this->ask ( $sqlInsertOrUpdate );
		
		$newId = mysql_insert_id ( Persistant::getCon () );
		
		if ($newId > 0) {
			$this->setPrimaryKey ( $newId );
		}
	}
	public function delete() {
		$currentPrimaryKey = $this->getPrimaryKey ();
		
		if ($currentPrimaryKey = ! null && sizeof ( $currentPrimaryKey ) > 0) {
			$this->ask ( $this->getDeleteQuery () );
		} else {
			return;
		}
	}
	public function getClass() {
		return get_class ( $this );
	}
	
	/**
	 * Retourne l'URL partielle qui permet d'afficher cet objet dans le navigateur.
	 */
	public function getShowURL() {
		return "index.php?show&class={$this->getClass()}&id={$this->getPrimaryKey()}";
	}
	public function getTableName() {
		$class = get_class ( $this );
		$class = strtolower ( get_class ( $this ) );
		return $class;
	}
	private function getMemberByName($memberName) {
		$members = get_object_vars ( $this );
		$objectExist = false;
		$correspondingObject = null;
		
		foreach ( $members as $objectName => $objectValue ) {
			if ($objectName == $memberName) {
				$correspondingObject = $objectValue;
				$objectExist = true;
				break;
			}
		}
		
		if (! $objectExist) {
			$calssName = get_class ( $this );
			throw new Exception ( "L'objet '{$memberName}' n'existe pas sur la classe {$calssName}." );
		}
		
		return $correspondingObject;
	}
	private function checkNotNullFields() {
		$errorFileds = null;
		
		foreach ( $this->getMembersDeclaration () as $aColumn ) {
			$fieldName = $aColumn->objectName;
			if (! $aColumn->isNullable && ! $aColumn->isAutoIncrement) {
				$correspondingObject = $this->getMemberByName ( $fieldName );
				
				if (is_null ( $correspondingObject )) {
					if (is_null ( $aColumn->defaultValue )) {
						$errorFileds .= $aColumn->objectName . ", ";
					} else {
						$this->$fieldName = $aColumn->defaultValue;
					}
				}
			}
		}
		
		if (! is_null ( $errorFileds ))
			throw new Exception ( "Les champs suivants sont vides alors qu'ils ne peuvent pas l'être : " . $errorFileds );
	}
	protected static function ask($query) {
		$req = mysql_query ( $query, Persistant::getCon () );
		if (! $req) {
			throw new Exception ( "Erreur MySQL" . ((DEBUG_SQL_ON_ERROR == "true") ? " : " . mysql_error ( Persistant::getCon () ) . " : " . $query : ".") );
		}
		return $req;
	}
	
	/**
	 * Retourne dans un tableau associatif le résulat
	 * d'une requête SQL.
	 *
	 * @param unknown_type $query        	
	 * @return multitype:multitype:
	 */
	public static function getDataFromQuery($query) {
		$reponse = Persistant::ask ( $query );
		
		return Persistant::getDataFromResultSet ( $reponse );
	}
	
	/**
	 * Retourne un resultSet sous forme de tableau associatif.
	 *
	 * @param ResultSet $resultSet        	
	 * @return array
	 */
	private static function getDataFromResultSet($resultSet) {
		$returnable = array ();
		while ( $data = mysql_fetch_assoc ( $resultSet ) ) {
			$returnable [] = $data;
		}
		
		return $returnable;
	}
	
	private static $idCheckRegex = "/[0-9]{1,5}|[A-Z_a-z]{1,30}/";
	/**
	 * Retourne un objet en fonction de son ID, ou null.
	 *
	 * @param integer $id        	
	 * @return Persistant
	 */
	public function findById($id) {
		if (is_null ( $id ))
			throw new Exception ( "Impossible de réccupérer un objet sans fournir son ID !" );
		
		$safeId = array();
		
		preg_match(Persistant::$idCheckRegex, $id, $safeId);
		if(array_key_exists(0, $safeId)){
			
			if ($safeId[0] == abs($id)){
				$sqlId = $this->toSql ( $id );
				$query = "select * from {$this->getTableName()} where {$this->getPrimaryKeyColumnName()} = {$sqlId}";
					
				return $this->getOneObjectOrNullFromQuery ( $query );
			}
			
			
		} 
		
		throw new Exception ( "Id mal fromé." );
		
	}
	
	/**
	 * Retourne tous les objets de la classe
	 *
	 * @param integer $id        	
	 * @return array
	 */
	public function getAll() {
		$query = "select * from {$this->getTableName()}";
		$order = $this->getNaturalOrderColumn ();
		if (! is_null ( $order )) {
			$query .= " order by {$order} ";
			if(strpos(strtolower($order), ' desc')){

			} else {
				$query .= ' ASC ';
			}
		}
		$objList = $this->getObjectListFromQuery ( $query );
		return $objList;
	}
	
	/**
	 * Lit une ligne d'un result set (transformé en tableau associatif), et en retourne l'objet correspondant.
	 *
	 * @param
	 *        	sql result set
	 * @throws Exception
	 * @return Persistant
	 */
	public function readFromResultSet($aa) {
		$objectClassName = get_class ( $this );
		@$aClassInstence = new $objectClassName ();
		
		foreach ( $this->getMembersDeclaration () as $aColumn ) {
			/* @var $aColumn SqlColumnMapping */
			
			if (! array_key_exists ( $aColumn->columnName, $aa )) {
				throw new Exception ( "Le résultat de la requête SQL sur la table " . $this->getTableName () . " ne contient pas la colonne " . $aColumn->columnName );
			}
			
			$fieldValue = $aa [$aColumn->columnName];
			$phpObjectName = $aColumn->objectName;
			
			if (is_null ( $fieldValue )) {
				$aClassInstence->$phpObjectName = null;
			} else {
				if (! is_null ( $aColumn->foreignTableName )) {
					if (! $aColumn->foreignLasyLoading && ! is_null ( $fieldValue )) {
						
						$foreignObjectClassName = $aColumn->foreignObjectClassName;
						
						$aClassInstence->$phpObjectName = new $foreignObjectClassName ( $fieldValue );
					} else {
						// C'est du lasy loading, on met juste l'ID à la place de l'objet.
						$aClassInstence->$phpObjectName = $fieldValue + 0;
					}
				} else {
					switch (strtolower ( $aColumn->sqlType )) {
						case SqlColumnTypes::$VARCHAR :
						case SqlColumnTypes::$LONGTEXT :
							$aClassInstence->$phpObjectName = $fieldValue;
							break;
						case SqlColumnTypes::$INTEGER :
							$aClassInstence->$phpObjectName = intval($fieldValue);
							break;
						case SqlColumnTypes::$NUMERIC :
							$aClassInstence->$phpObjectName = floatval($fieldValue);
							break;
						case SqlColumnTypes::$DATETIME :
							$aClassInstence->$phpObjectName = MyDateTime::createFromFormat ( "Y-m-d H:i:s", $fieldValue );
							break;
						case SqlColumnTypes::$BOOLEAN :
							if ($fieldValue == null) {
								$aClassInstence->$phpObjectName = null;
							} else {
								$aClassInstence->$phpObjectName = $fieldValue == 1;
							}
							break;
						default :
							$aClassInstence->$phpObjectName = $fieldValue;
					}
				}
			}
		}
		
		return $aClassInstence;
	}
	
	/**
	 * Exécute une requête SQL, et lit retourne
	 * une liste d'objets PHP du modèle.
	 *
	 * @param unknown_type $query        	
	 * @return array
	 */
	public function getObjectListFromQuery($query) {
		$repList = Persistant::getDataFromQuery ( $query );
		if ($repList != false) {
			$return = array ();
			foreach ( $repList as $repLine ) {
				$return [] = $this->readFromResultSet ( $repLine );
			}
			return $return;
		} else {
			return array ();
		}
	}
	
	/**
	 * Exécute la requête SQL.
	 * S'il existe un et un seul
	 * résultat, retournera ce résultat.
	 * Dans tous les autres cas, retournera null.
	 *
	 * @param string $query        	
	 * @return Persistant
	 */
	public function getOneObjectOrNullFromQuery($query) {
		$rs = $this->ask ( $query );
		
		if (mysql_num_rows ( $rs ) !== 1) {
			return null;
		}
		
		$resultArray = Persistant::getDataFromResultSet ( $rs );
		return $this->readFromResultSet ( $resultArray [0] );
	}
	
	/**
	 * Réccupère et affecte un object membre de cette instance qui aurait été configuré en lasy loading.
	 * La méthode peut être appelée plusieurs fois dessuite, l'objet ne sera réccupéré qu'une fois.
	 *
	 * @param string $fieldNameToFeed
	 *        	le nom du membre en lasy loading.
	 * @param string $className
	 *        	la classe du membre en lasy loadgin
	 */
	public function fetchLasyObject($fieldNameToFeed, $className) {
		if (is_null ( $this->$fieldNameToFeed )) {
			return null;
		} else if (is_object ( $this->$fieldNameToFeed )) {
			return $this->$fieldNameToFeed;
		} else if (is_int ( $this->$fieldNameToFeed )) {
			$this->$fieldNameToFeed = new $className ( $this->$fieldNameToFeed );
			
			return $this->$fieldNameToFeed;
		} else {
			throw new Exception ( "Le champ {$fieldNameToFeed} n'était ni un objet déjà instancié, ni l'id d'un objet à réccupérer en lasy loading. Situation très étrange..." );
		}
	}
	
	/**
	 * Réccupère et affecte une liste d'objets qui sont en relation n->1 vers l'objet courant.
	 * La méthode peut être appelée plusieurs fois dessuite, la collection ne sera constituée qu'une seule fois.
	 *
	 * @param string $localFieldNameToFeed
	 *        	le nom du membre local en lasy loading qui va recevoir la collection d'objets.
	 * @param string $distantClassName
	 *        	la classe distante ayant une référence vers l'objet courant.
	 * @param string $distantFieldName
	 *        	dans la class distante, le nom du membre portant la référence vers l'objet courant.
	 */
	public function fetchLasyCollection($localFieldNameToFeed, $distantClassName, $distantFieldName) {
		// Champ déjà initialisé, on le retourne sans plus d'histoire.
		if (! is_null ( $this->$localFieldNameToFeed ) && is_array ( $this->$localFieldNameToFeed )) {
			return $this->$localFieldNameToFeed;
		}
		
		/* @var $distantClassInstance Persistant  */
		/* @var $aColMapping SqlColumnMappgin  */
		@$distantClassInstance = new $distantClassName ();
		
		// Recherche dans la classe distante de la déclaration de la colonne correspondant au $distantFieldName.
		$aColMapping = null;
		foreach ( $distantClassInstance->getMembersDeclaration () as $aColMapping ) {
			if ($aColMapping->objectName == $distantFieldName) {
				$aColMapping = $aColMapping;
				break;
			}
		}
		
		$sql = "select * from {$distantClassInstance->getTableName()} where {$aColMapping->columnName} = {$this->getPrimaryKey()}";
		
		$this->$localFieldNameToFeed = $distantClassInstance->getObjectListFromQuery ( $sql );
		
		return $this->$localFieldNameToFeed;
	}
	
	/**
	 * Transorme un objet php simple (string, int, boolean, .
	 *
	 * ..) en
	 * instruction utilisable pour un insert, un update, ou une coparaison dans un where.
	 *
	 * @param unknown_type $phpSimpleType        	
	 */
	public static function toSql($phpSimpleType, SqlColumnMappgin $mapping = null) {
		
		// ---------------------------------------------------∨ Je ne sais pas pourquoi, mais en PHP, un boolen est null.
		if (is_null ( $phpSimpleType ) && gettype ( $phpSimpleType ) != SqlColumnTypes::$BOOLEAN) {
			return "null";
		}
		
		$sql = null;
		
		if (! is_null ( $mapping ) && ! is_null ( $mapping->foreignTableName )) {
			// Si c'est du lasy loaging qui n'a pas été instancié, on n'a qu'un id à la place de l'objet.
			if (is_int ( $phpSimpleType )) {
				return $phpSimpleType;
			} else {
				// Dans tous les autres cas, c'est sensé être un objet.
				if (is_null ( $phpSimpleType->getPrimaryKey () )) {
					throw new Exception ( "Sauvegardez les objets membres avant de sauvegarder cet objet. Sans cela, la contrainte d'intégrité référentielle n'est pas respectée." );
				} else {
					return $phpSimpleType->getPrimaryKey ();
				}
			}
		}
		
		switch (gettype ( $phpSimpleType )) {
			case 'string' :
				if (sizeof ( trim ( $phpSimpleType ) ) == 0) {
					$sql = "null";
				} else {
					// $string = mysql_real_escape_string(trim($phpSimpleType), Persistant::getCon());
					// $string = trim($phpSimpleType);
					$string = str_replace ( "'", "''", trim ( $phpSimpleType ) );
					$string = $string;
					if (! is_null ( $mapping ) && strlen ( $string ) > $mapping->sqlLenght && $mapping->sqlType == "varchar") {
						throw new Exception ( "La valeur '{$string}' est trop grande pour contenir dans la colonne '{$mapping->columnName}'
						, prévue pour {$mapping->sqlLenght} caractères maximum." );
					}
					$sql = "'{$string}'";
				}
				break;
			case 'integer' :
				$sql = $phpSimpleType;
				break;
			case 'boolean' :
				
				$sql = $phpSimpleType ? "true" : "false";
				break;
			case 'double' :
				$sql = $phpSimpleType;
				break;
			case 'object' :
				$className = get_class ( $phpSimpleType );
				if ($className == "MyDateTime") {
					$sql = "'{$phpSimpleType->format('Y-m-d H:i:s')}'";
				} else {
					throw new Exception ( "Le type objet '" . gettype ( $phpSimpleType ) . "' n'est pas convertible en un type SQL simple." );
				}
				break;
			default :
				throw new Exception ( "Le type php '" . gettype ( $phpSimpleType ) . "' n'est pas convertible en un type SQL simple." );
		}
		
		return $sql;
	}
}
class NoExistOnDbException extends Exception {
}
;