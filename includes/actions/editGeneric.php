<?php

/* @var $page SmartPage */
$idToEdit = - 1;
if (array_key_exists ( "id", $ARGS )) {
	$idToEdit = $ARGS ["id"];
}

@$classToEditInstance = new $class ();

/* @var $classToEditInstance Persistant */
/* @var $obj Persistant */
if (array_key_exists ( "clone", $ARGS )) {
	$obj = $classToEditInstance->findById ( $ARGS ["clone"] );
	$obj->setPrimaryKey ( null );
} else {
	$obj = $classToEditInstance->findById ( $idToEdit );
}

$memberDecl = $classToEditInstance->getMembersDeclaration ();

/* @var $aColumn SqlColumnMappgin */

$page->asset ( "body", file_get_contents ( "includes/html/editGeneric.html" ) );

$page->asset ( "classToEdit", $class );
$page->asset ( "redirectAction", "show" );
$page->asset ( "idToEdit", $idToEdit );

foreach ( $memberDecl as $aColumn ) {
	if (! ($aColumn->isPrimaryKey && $aColumn->sqlType == SqlColumnTypes::$INTEGER)) {
		
		$fieldName = $aColumn->objectName;
		$fieldValue = "";
		if (! is_null ( $obj )) {
			$fieldValue = $obj->$fieldName;
		}
		
		$page->append ( "inputs", "<p>" . buildEditionField ( $aColumn, $fieldValue ) . "</p>" );
	}
}
function buildEditionField($aColumn, $valueObject) {
	$inputNameId = $aColumn->objectName;
	$titre = $aColumn->description;
	if (is_null ( $titre ))
		$titre = $aColumn->objectName;
	$r = "<label for='{$inputNameId}'>{$titre}</label>";
	
	if (! is_null ( $aColumn->foreignTableName )) {
		
		$objectClassName = $aColumn->foreignObjectClassName;
		@$aClassInstence = new $objectClassName ();
		
		/* @var $aClassInstence Persistant */
		/* @var $aFoObj Persistant */
		
		$foreignObjects = $aClassInstence->getAll ();
		
		$options = array ();
		
		if (count ( $foreignObjects ) > 0) {
			foreach ( $foreignObjects as $aFoObj ) {
				$options [$aFoObj->getPrimaryKey () + ""] = $aFoObj->getShortToString ();
			}
		} else {
		}
		
		if (is_int ( $valueObject + 0 )) {
			$selectedValue = $valueObject;
		} else {
			$selectedValue = $valueObject->getPrimaryKey ();
		}
		
		$r .= getSelectHtml ( $inputNameId, $options, $selectedValue, $aColumn->isNullable );
	} else {
		$htmlValue = $valueObject;
		switch (strtolower ( $aColumn->sqlType )) {
			case 'varchar' :
				$htmlValue = str_replace ( '"', "&quot;", $htmlValue );
				$r .= "<input type=\"text\" name=\"{$inputNameId}\" id=\"{$inputNameId}\" value=\"{$htmlValue}\">";
				break;
			case 'longtext' :
				$r .= "<textarea type='text' name='{$inputNameId}' id='{$inputNameId}'>{$htmlValue}</textarea>";
				break;
			case 'datetime' :
				$dateTime = ! is_null ( $htmlValue ) && get_class ( $htmlValue ) == "MyDateTime" ? $htmlValue->format ( "d/m/y H:i" ) : $htmlValue;
				$r .= "<input type='datetime' name='{$inputNameId}' id='{$inputNameId}' value='{$dateTime}'>";
				break;
			case 'boolean' :
				$trueSelected = ! is_null ( $valueObject ) && $valueObject ? "checked='checked'" : "";
				$falseSelected = ! is_null ( $valueObject ) && ! $valueObject ? "checked='checked'" : "";
				$nullSelected = is_null ( $valueObject ) ? "checked='checked'" : "";
				$r .= "<label for='{$inputNameId}TRUE'>OUI</label><input type='radio' {$trueSelected} name='{$inputNameId}' id='{$inputNameId}TRUE' value='1'>&nbsp;&nbsp;";
				$r .= "<label for='{$inputNameId}FALSE'>NON</label><input type='radio' {$falseSelected} name='{$inputNameId}' id='{$inputNameId}FALSE' value='0'>&nbsp;&nbsp;";
				if ($aColumn->isNullable) {
					$r .= "<label for='{$inputNameId}NULL'>NULL</label><input type='radio' {$nullSelected} name='{$inputNameId}' id='{$inputNameId}NULL' value=''>";
				}
				break;
			case 'int' :
			case 'integer' :
				$r .= "<input type='number' name='{$inputNameId}' id='{$inputNameId}' value='{$valueObject}'>";
				break;
			default :
				throw new Exception ( "Le type sql {$aColumn->sqlType} n'est pas pris en charge par l'éditeur générique." );
		}
	}
	
	return $r;
}