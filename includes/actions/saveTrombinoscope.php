<?php
if (! array_key_exists ( "trombiFile", $_FILES )) {
	throw new Exception ( "Aucun fichier n'a été envoyé." );
}

$personne = new Personne ( $ARGS ["idPersonne"] + 0 );

$max_upload = ( int ) (ini_get ( 'upload_max_filesize' ));
$max_post = ( int ) (ini_get ( 'post_max_size' ));
$memory_limit = ( int ) (ini_get ( 'memory_limit' ));
$upload_mb = min ( $max_upload, $max_post, $memory_limit );

$fileType = $_FILES ["trombiFile"] ['type'];
$autorizedFilesTypes = array (
		'image/jpeg',
		'image/pjpeg' 
);

if (! in_array ( $fileType, $autorizedFilesTypes )) {
	throw new Exception ( "Le type de fichier '{$fileType}' n'est pas supporté. Essayer en jpg." );
}

if ($_FILES ["trombiFile"] ['size'] > $upload_mb * 1000000) {
	throw new Exception ( "La taille du fichier ne doit pas dépasser les {$upload_mb} Mo." );
}

if ($_FILES ["trombiFile"] ['error'] > 0) {
	throw new Exception ( "Une erreur a empêché l'upload du fichier." );
}

$newFileName = time ();

switch ($fileType) {
	case "image/jpeg" :
	case "image/pjpeg" :
		$newFileName .= ".jpg";
		break;
	default :
		throw new Exception ( "Le type de fichier '{$fileType}' n'est pas supporté. Essayez en jpg" );
}

$fieldName = "trombiFile";

if (is_null ( $personne->$fieldName ) || strlen ( $personne->$fieldName ) == 0) {
	$oldFileName = null;
} else {
	$oldFileName = $personne->$fieldName;
}

$completeFileName = getcwd () . '/documents/trombi/' . $newFileName;

if (! move_uploaded_file ( $_FILES ["trombiFile"] ['tmp_name'], $completeFileName )) {
	// throw new Exception("Erreur lors de l'écriture du fichier vers l'emplacement définitif.");
}

// redimentionnement de l'image

if (array_key_exists ( "width", $ARGS ) && array_key_exists ( "height", $ARGS ) && array_key_exists ( "x1", $ARGS ) && array_key_exists ( "y1", $ARGS )) {
	
	list ( $width, $height ) = getimagesize ( $completeFileName );
	
	$imageInfo = getimagesize ( $completeFileName );
	$memoryNeeded = round ( ($imageInfo [0] * $imageInfo [1] * $imageInfo ['bits'] * $imageInfo ['channels'] / 8 + Pow ( 2, 16 )) * 1.65 );
	$memoryNeeded += memory_get_usage ( true );
	$memoryAllowed = return_bytes ( ini_get ( 'memory_limit' ) );
	
	$memoryAllowed -= 100000;
	
	if ($memoryAllowed < $memoryNeeded) {
		$page->appendNotification ( "Bien que votre image ne dépasse pas les 2Mo, sa résolution est trop grande pour être traitée par le serveur. Réduisez la résolution, ou choisissez une autre image." );
		$ACTIONS [] = array (
				"edit",
				"class" => "Trombinoscope",
				"idPersonne" => $ARGS ["idPersonne"] + 0 
		);
	} else {
		$width = min ( $ARGS ["width"], 200 );
		$height = min ( $ARGS ["height"], 250 );
		
		$trombiFile = imagecreatetruecolor ( $width, $height );
		
		$source = imagecreatefromjpeg ( $completeFileName );
		
		// Crop
		imagecopyresampled ( $trombiFile, $source, 0, 0, $ARGS ["x1"], $ARGS ["y1"], $width, $height, $ARGS ["width"], $ARGS ["height"] );
		
		imagejpeg ( $trombiFile, $completeFileName );
		
		imagedestroy ( $source );
		
		$personne->trombiFile = $newFileName;
		$personne->dontWantUseTrombi = null;
		$personne->cantUploadTrombiFile = null;
		$personne->save ();
		
		$page->appendNotification ( "Photo enregistrée !" );
		
		$ACTIONS [] = array (
				"show",
				"class" => "Personne",
				"id" => $personne->getPrimaryKey () 
		);
		
		// Suppression de l'ancien fichier
		
		if (! is_null ( $oldFileName ) && file_exists ( getcwd () . '/documents/trombi/' . $oldFileName )) {
			unlink ( getcwd () . '/documents/trombi/' . $oldFileName );
		}
	}
} else {
	echo "Pas de dimentions ?!";
}

?>


