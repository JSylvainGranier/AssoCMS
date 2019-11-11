<?php
class SqlColumnTypes {
	public static $VARCHAR = "varchar";
	public static $INTEGER = "integer";
	public static $BOOLEAN = "boolean";
	public static $DATETIME = "datetime";
	public static $LONGTEXT = "longtext";
	private static $liste;
	public static function getListe() {
		if (is_null ( SqlColumnTypes::$liste )) {
			SqlColumnTypes::$liste = array (
					SqlColumnTypes::$VARCHAR,
					SqlColumnTypes::$INTEGER,
					SqlColumnTypes::$BOOLEAN,
					SqlColumnTypes::$DATETIME,
					SqlColumnTypes::$LONGTEXT 
			);
		}
		
		return SqlColumnTypes::$liste;
	}
}
class SqlColumnMappgin {
	public $objectName;
	public $columnName;
	public $sqlType;
	public $sqlLenght;
	public $isNullable = true;
	public $isPrimaryKey = false;
	public $isAutoIncrement = false;
	public $defaultValue;
	public $persistReadFunction;
	public $persistWriteFunction;
	public $foreignObjectClassName;
	public $foreignTableName;
	public $foreignTablePrimaryColumn;
	public $foreignOnDeleteCascade;
	public $foreignLasyLoading;
	/*
	 * deux types de liste : la lecture inversée d'un foreign sur une autre entitée
	 * la relation many to many.
	 *
	 * //Le nom de la classe qui contient un champ référançant la classe courante.
	 * $reverseForeignClassName;
	 * $reverseForeignFieldName;
	 *
	 * $manyToMany
	 */
	public $description;
	function __construct($colName, $description, $colType, $colLenght = 11) {
		$this->objectName = $colName;
		$this->columnName = $colName;
		$this->sqlType = $colType;
		$this->sqlLenght = $colLenght;
		$this->description = $description;
		
		if ($colType == SqlColumnTypes::$BOOLEAN) {
			$this->isNullable = false;
			$this->defaultValue = false;
		}
	}
	function setPrimaryKey($isAutoIncrement = false) {
		$this->isNullable = false;
		$this->isPrimaryKey = true;
		$this->isAutoIncrement = $isAutoIncrement;
	}
	function setForeing($objectInstance, $objectName, $onDeleteCascade, $lasyLoading) {
		/* @var $objectInstance Persistant */
		$this->objectName = $objectName;
		
		$this->foreignLasyLoading = $lasyLoading;
		$this->foreignOnDeleteCascade = $onDeleteCascade;
		$this->foreignTableName = $objectInstance->getTableName ();
		$this->foreignObjectClassName = get_class ( $objectInstance );
		
		foreach ( $objectInstance->getMembersDeclaration () as $aColumn ) {
			/* @var $aColumn SqlColumnMappgin */
			if ($aColumn->isPrimaryKey) {
				$this->foreignTablePrimaryColumn = $aColumn->columnName;
				break;
			}
		}
	}
}