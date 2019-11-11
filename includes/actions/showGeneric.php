<?php

/* @var $page SmartPage */
$class = $ARGS ["class"];
$idToEdit = - 1;
if (array_key_exists ( "id", $ARGS )) {
	$idToEdit = $ARGS ["id"];
}

/* @var $obj Persistant */
$obj = new $class ( $idToEdit );

$memberDecl = $obj->getMembersDeclaration ();

/* @var $aColumn SqlColumnMappgin */

$page->appendBody ( file_get_contents ( "includes/html/showGeneric.html" ) );

foreach ( $memberDecl as $aColumn ) {
	if (! ($aColumn->isPrimaryKey && $aColumn->sqlType == SqlColumnTypes::$INTEGER)) {
		
		$fieldName = $aColumn->objectName;
		$fieldValue = null;
		if (! is_null ( $obj )) {
			$fieldValue = $obj->$fieldName;
		}
		
		$page->append ( "members", "<p>" . buildMemberHtml ( $aColumn, $fieldValue ) . "</p>" );
	}
}

$page->addArticleMenu ( $class, $idToEdit );
function buildMemberHtml($aColumn, $valueObject) {
	$inputNameId = $aColumn->objectName;
	$titre = $aColumn->description;
	if (is_null ( $titre ))
		$titre = $aColumn->objectName;
	$r = "<label for='{$inputNameId}'>{$titre}</label>";
	if (! is_null ( $aColumn->foreignTableName )) {
		if (! is_null ( $valueObject )) {
			/* @var $aClassInstence Persistant */
			/* @var $aFoObj Persistant */
			
			if (is_integer ( $valueObject )) {
				$objectClassName = $aColumn->foreignObjectClassName;
				@$aFoObj = new $objectClassName ( $valueObject );
			} else {
				$aFoObj = $valueObject;
			}
			
			$r .= "<p>{$aFoObj->getShortToString()}</p>";
		} else {
			$r .= "<p>Sans valeur</p>";
		}
	} else {
		switch (strtolower ( $aColumn->sqlType )) {
			case 'varchar' :
			case 'longtext' :
			case 'int' :
			case 'integer' :
				$r .= "<p>{$valueObject}</p>";
				break;
			case 'datetime' :
				$r .= "<p>{$valueObject->format("d/m/y H:i")}</p>";
				break;
			case 'boolean' :
				if (is_null ( $valueObject )) {
					$r .= "<p>Sans valeur</p>";
				} else if ($valueObject) {
					$r .= "<p>Oui</p>";
				} else {
					$r .= "<p>Non</p>";
				}
				break;
			default :
				throw new Exception ( "Le type sql {$aColumn->sqlType} n'est pas pris en charge par l'afficheur générique." );
		}
	}
	return $r;
}