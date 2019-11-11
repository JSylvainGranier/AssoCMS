<?php
$id = $ARGS ["id"];

/* @var $object Persistant */
/* @var $evenement Evenement */
/* @var $page Page */
/* @var $foreignObject Persistant */

$evenement = new Evenement ();
$evtPage = new Page ();

if (! is_null ( $id ) && strlen ( trim ( $id ) ) > 0 && $id > - 1) {
	$evenement = $evenement->findById ( $id );
	$evtPage = $evenement->getPage ();
}

$evtPage->isSubClass = true;
$object = $evtPage;

do {
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
						$evenementClassName = $aColumn->foreignObjectClassName;
						@$foreignObject = new $evenementClassName ();
						$fieldValue = $foreignObject->findById ( $fieldValue );
					}
				}
			}
			
			if ($object->$fieldName !== $fieldValue) {
				
				if (get_magic_quotes_gpc () && ($aColumn->sqlType == SqlColumnTypes::$VARCHAR || $aColumn->sqlType == SqlColumnTypes::$LONGTEXT)) {
					$object->$fieldName = stripslashes ( $fieldValue );
				} else if ($aColumn->sqlType == SqlColumnTypes::$INTEGER && ! is_null ( $fieldValue )) {
					$object->$fieldName = $fieldValue + 0;
				} else if ($aColumn->sqlType == SqlColumnTypes::$DATETIME && ! is_null ( $fieldValue )) {
					$object->$fieldName = MyDateTime::createFromFormat ( "j/m/Y H:i", $fieldValue );
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
	}
	
	if ($object != $evenement) {
		$object = $evenement;
	} else {
		$object = null;
	}
} while ( $object != null );

$evenement->page = $evtPage;
$evenement->save ();

$page->appendNotification ( "Enregistré !", 10 );
$ACTIONS [] = array (
		"show",
		"class" => "Evenement",
		"id" => $evenement->getPrimaryKey () 
);



