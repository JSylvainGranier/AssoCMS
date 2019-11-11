<?php
include_once "includes/config.php";
include_once "includes/SqlColumnMapping.php";
include_once "includes/Tools.php";
include_once "includes/IpTools.php";
include_once "includes/LoginTools.php";
include_once "includes/Persistant.php";
include_once "includes/HasMetaData.php";
include_once "includes/SmartPage.php";
include_once "includes/libs/MyDateTime.php";

$classFiles = scandir ( "includes/model/", 1 );
foreach ( $classFiles as $aFile ) {
	if (strrpos ( $aFile, ".php" ) !== false) {
		$indexOfExtention = strrpos ( $aFile, ".php" );
		
		$className = substr ( $aFile, 0, $indexOfExtention );
		include_once "includes/model/" . $aFile;
	}
}

if (! defined ( 'PHP_VERSION_ID' )) {
	$version = explode ( '.', PHP_VERSION );
	
	define ( 'PHP_VERSION_ID', ($version [0] * 10000 + $version [1] * 100 + $version [2]) );
}
function isPhpUp() {
	return (PHP_VERSION_ID >= 50217);
}

if (! isPhpUp ()) {
	include_once "includes/libs/JSON/jsonwrapper.php";
	include_once "includes/libs/JSON/jsonwrapper_inner.php";
}
