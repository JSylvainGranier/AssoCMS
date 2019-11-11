<?php


/**
 *
 * Logging operation - to a file (upload_log.txt) and to the stdout
 * @param string $str - the logging string
 */
function _log($str) {

	console.log($str);
}

/**
 *
 * Delete a directory RECURSIVELY
 * @param string $dir - directory path
 * @link http://php.net/manual/en/function.rmdir.php
 */
function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir . "/" . $object) == "dir") {
					rrmdir($dir . "/" . $object);
				} else {
					unlink($dir . "/" . $object);
				}
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

/**
 *
 * Check if all the parts exist, and
 * gather all the parts of the file together
 * @param string $temp_dir - the temporary directory holding all the parts of the file
 * @param string $fileName - the original file name
 * @param string $chunkSize - each chunk size (in bytes)
 * @param string $totalSize - original file size (in bytes)
 */
function createFileFromChunks($temp_dir, $fileName, $chunkSize, $totalSize,$total_files, $attachement) {

	// count all the parts of this file
	$total_files_on_server_size = 0;
	$temp_total = 0;
	foreach(scandir($temp_dir) as $file) {
		$temp_total = $total_files_on_server_size;
		$tempfilesize = filesize($temp_dir.'/'.$file);
		$total_files_on_server_size = $temp_total + $tempfilesize;
	}
	// check that all the parts are present
	// If the Size of all the chunks on the server is equal to the size of the file uploaded.
	if ($total_files_on_server_size >= $totalSize) {
		// create the final destination file
		if($attachement != null && $attachement->idAttachment > 0){
			$completeFileName = $attachement->getServerFilePath ();
		} else {
			$completeFileName = "documents/lostUploadedFiles/".$fileName;
		}
				
		if (($fp = fopen($completeFileName, 'w')) !== false) {
			for ($i=1; $i<=$total_files; $i++) {
				fwrite($fp, file_get_contents($temp_dir.'/'.$fileName.'.part'.$i));
				_log('writing chunk '.$i);
			}
			fclose($fp);
			
			if($attachement != null && $attachement->idAttachment > 0){
				$attachement->createTumbnail();
				echo $attachement->getEditionFormHtml();
			}
		} else {
			_log('cannot create the destination file : '.$completeFileName);
			return false;
		}

		// rename the temporary directory (to avoid access from other
		// concurrent chunks uploads) and than delete it
		if (rename($temp_dir, $temp_dir.'_UNUSED')) {
			rrmdir($temp_dir.'_UNUSED');
		} else {
			rrmdir($temp_dir);
		}
	}

}


// $page->displayPhpErrors = false;
$page->setStandardOuputDisabled ( true );
$attachement;
switch ($ARGS ['action']) {
	case "delete" :
		$attachement = new Attachment ( $ARGS ["id"] );
		$attachement->delete ();
		
		echo $attachement->getEditionFormHtml ();
		break;
	case "update" :
		$attachement = new Attachment ( $ARGS ["id"] );
		$attachement->updateWithARGS ();
		$attachement->save ();
		echo $attachement->getEditionFormHtml ();
		break;
		
	case "uploadNewFileMetaData" :
		// Créé l'Attachment, et retourne les informations de l'attachment pour référence.
		if (array_key_exists ( "name", $ARGS )) {
			
			$attachement = new Attachment ();
			
			$idPage = $ARGS ["idPage"];
			$fileType = $ARGS ["type"];
			$fileName = $ARGS ['name'];
			$fileSize = $ARGS ['size'];

			
			try {
				
				$fileName = stripslashes ( $fileName );
				$ext = strtolower ( substr ( $fileName, strrpos ( $fileName, '.' ) + 1 ) );
				if (strtolower ( $ext ) === "youtube") {
					$fileType = "video/youtube";
				} else if (strtolower ( $ext ) === "rtf") {
					$fileType = "text/richtext";
				}
				
				$upload_mb = 15;
				
				$autorizedFilesTypes = array (
						'image/jpeg',
						'image/gif',
						'image/png',
						'application/pdf',
						'audio/mpeg',
						'audio/mp3',
						'video/youtube',
						'application/rtf',
						'application/x-rtf',
						'text/richtext',
						'text/rtf' 
				);
				
						
				if (! in_array ( $fileType, $autorizedFilesTypes )) {
					if (startsWith ( $fileType, "application" )) {
						throw new Exception ( "Ce format de fichier ({$fileType}) n'est autorisé. Envisagez de l'exporter en PDF." );
					} else {
						throw new Exception ( "Le type de fichier '{$fileType}' n'est pas autorisé." );
					}
				}
				
				// Limiter la taille pour les images
				
				if ($_FILES ["attachment"] ['size'] > $upload_mb * 1000000) {
					throw new Exception ( "La taille du fichier ne doit pas dépasser les {$upload_mb} Mo." );
				}
				
				
				$attachement = Attachment::buildAttachment ( $ARGS ["idPage"], $ARGS );
				
				if ($attachement->isImage()  && $ARGS['size'] > 1000000) {
					throw new Exception ( "Sur les pages web, les images ne devraient pas dépasser 1 Mo." );
				}
				
				$completeFileName = $attachement->getServerFilePath ();
				
				mkdir ( $attachement->getServerDirPath (), 0777, true );
				
				/*
				if (! move_uploaded_file ( $_FILES ["attachment"] ['tmp_name'], $completeFileName )) {
					throw new Exception ( "Impossible de copier le fichier de {$_FILES["attachment"]['tmp_name']} à {$completeFileName}." );
				}
				
				$attachement->createTumbnail ();
				*/
				
				$attachement->save ();
				
				echo json_encode(array('idAttachment' => $attachement->idAttachment));
			} catch ( Exception $e ) {
				echo json_encode(array('error' => "Problème avec le fichier {$ARGS["name"]} : {$e->getMessage()}"));
			}
		}
		
		break;
	case "uploadFileChunk" :
				$file = $_FILES["file"];
				
				$idAttachment = -1;
				if(array_key_exists("idAttachment", $ARGS)){
					$idAttachment = $ARGS["idAttachment"];
				}
				
				if($idAttachment+0 > 0){
					$attachement = new Attachment($idAttachment+0);
				}
				
				// check the error status
			    if ($file['error'] != 0) {
			        _log('error '.$file['error'].' in file '.$_POST['resumableFilename']);
			        break;
			    }
			
			    // init the destination file (format <filename.ext>.part<#chunk>
			    // the file is stored in a temporary directory
			    if(isset($_POST['resumableIdentifier']) && trim($_POST['resumableIdentifier'])!=''){
			        $temp_dir = 'documents/tmp/'.$_POST['resumableIdentifier'];
			    }
			    $dest_file = $temp_dir.'/'.$_POST['resumableFilename'].'.part'.$_POST['resumableChunkNumber'];
			
			    // create the temporary directory
			    if (!is_dir($temp_dir)) {
			        mkdir($temp_dir, 0777);
			    }
			
			    $src = $file['tmp_name'];
			    
			    // move the temporary file
			    if (!move_uploaded_file($src, $dest_file)) {
			        _log('Error saving (move_uploaded_file) chunk '.$_POST['resumableChunkNumber'].' for file '.$_POST['resumableFilename'].' from '.$src.' to '.$dest_file);
			    } else {
			        // check if all the parts present, and create the final destination file
			        createFileFromChunks($temp_dir, $_POST['resumableFilename'],$_POST['resumableChunkSize'], $_POST['resumableTotalSize'],$_POST['resumableTotalChunks'], $attachement);
			    }
		break;
	
	
	default :
		echo "Cette action (".$ARGS ['action'].") n'existe pas.";
}



?>