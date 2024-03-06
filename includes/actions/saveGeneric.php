<?php
$class = $ARGS ["class"];

$id = $ARGS ["id"];

/* @var $object Persistant */
/* @var $foreignObject Persistant */

@$object = new $class ();

if (! is_null ( $id ) && strlen ( trim ( $id ) ) > 0 && $id > - 1) {
	$object = $object->findById ( $id );
	if (is_null ( $object )) {
		throw new Exception ( "Cet objet de classe '{$class}' n'existe pas !" );
	}
}

$membersDeclaration = $object->getMembersDeclaration ();
$haveChanged = false;

foreach ( $membersDeclaration as $aColumn ) {
	/* @var $aColumn SqlColumnMappgin */
	
	if (array_key_exists ( $aColumn->objectName, $ARGS )) {
		$fieldName = $aColumn->objectName;
		$fieldValue = secureFormInput ( $ARGS [$aColumn->objectName] );
		if (! is_null ( $aColumn->foreignTableName )) {
			
			if ($fieldValue < 1) {
				$fieldValue = null;
			} else {
				if (! $aColumn->foreignLasyLoading) {
					$objectClassName = $aColumn->foreignObjectClassName;
					$fieldValue = new $objectClassName ( $fieldValue );
				}
			}
		}
		
		if ($object->$fieldName !== $fieldValue) {
			
			if (get_magic_quotes_gpc () && ($aColumn->sqlType == SqlColumnTypes::$VARCHAR || $aColumn->sqlType == SqlColumnTypes::$LONGTEXT)) {
				$newVal = stripslashes ( $fieldValue );
				$object->$fieldName = strlen ( $newVal ) > 0 ? $newVal : null;
			} else if ($aColumn->sqlType == SqlColumnTypes::$INTEGER && ! is_null ( $fieldValue )) {
				$object->$fieldName = $fieldValue + 0;
			} else if ($aColumn->sqlType == SqlColumnTypes::$NUMERIC && ! is_null ( $fieldValue )) {
				$object->$fieldName = $fieldValue + 0;
			} else if ($aColumn->sqlType == SqlColumnTypes::$DATETIME && ! is_null ( $fieldValue )) {
			    $fieldValue = strlen ( $fieldValue ) > 0 ? $fieldValue : null;
			    if(!is_null($fieldValue)){
			        try {
			            $object->$fieldName = MyDateTime::createFromFormat ( "d/m/y H:i", $fieldValue );
			        } catch (Exception $p){
			            try {
			                $object->$fieldName = MyDateTime::createFromFormat ( "d/m/Y H:i", $fieldValue );
			            } catch (Exception $p){
			                try {
			                    $object->$fieldName = MyDateTime::createFromFormat ( "d/m/Y H:i", $fieldValue." 00:00" );
			                } catch (Exception $p){
			                    try {
			                        $object->$fieldName = MyDateTime::createFromFormat ( "d/m/y H:i", $fieldValue." 00:00" );
			                    } catch (Exception $p){
			                        try {
			                            $object->$fieldName = MyDateTime::createFromFormat ( "Y-m-d H:i", $fieldValue." 00:00" );
			                        } catch (Exception $p){
			                            try {
											$object->$fieldName = MyDateTime::createFromFormat ( "Ymd H:i", $fieldValue." 00:00" );
										} catch (Exception $p){
											throw $p;
										}
			                        }
			                    }
			                }
			            }
			        }
			    } else {
			        $object->$fieldName = null;
			    }
			    
			} else if ($aColumn->sqlType == SqlColumnTypes::$BOOLEAN) {
				if ($fieldValue == "1") {
					$object->$fieldName = true;
				} else if ($fieldValue == "0") {
					$object->$fieldName = false;
				} else {
					$object->$fieldName = null;
				}
			} else {
				$object->$fieldName = $fieldValue;
			}
			
			$haveChanged = true;
		}
	}
}

if ($haveChanged) {
	$object->save ();
	$ARGS ["id"] = $object->getPrimaryKey ();
	$page->appendNotification ( "Enregistré !", 10 );
}


