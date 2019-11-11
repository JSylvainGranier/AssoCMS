<?php
$attachmentList = new Attachment ();
$attachmentCount = $attachmentList->countAttachmentsForPage ( $pageToShow->getPrimaryKey () );
$attachmentList = $attachmentList->listAttachmentsForPage ( $pageToShow->getPrimaryKey () );

if (sizeof ( $attachmentList ) > 0) {
	
	$includes = '<link rel="stylesheet" href="ressources/photoswipe/photoswipe.css">';
	$includes .= '<link rel="stylesheet" href="ressources/photoswipe/default-skin/default-skin.css">';
	$includes .= '<script src="ressources/photoswipe/photoswipe.min.js"></script>';
	$includes .= '<script src="ressources/photoswipe/photoswipe-ui-default.min.js"></script>';
	$page->append ( "linkZone", $includes );
	
	$page->appendBody ( "<div class='attachmentListContainer'>" );
	
	$page->appendBody ( "<h4>Fichiers joints</h4>" );
	
	if (file_exists ( $fsDir . "cache.html" )) {
		$page->appendBody ( file_get_contents ( $fsDir . "cache.html" ) );
	} else {
		
		$finalBody = "";
		
		$photoSwiper = file_get_contents ( "includes/html/photoswipe.html" );
		
		$imagesList = array ();
		$musicList = array ();
		$videoList = array ();
		$otherFilesList = array ();
		
		foreach ( $attachmentList as $anAttachment ) {
			
			if ($anAttachment->isImage ()) {
				$imagesList [] = $anAttachment;
			} else if ($anAttachment->isMP3 ()) {
				$musicList [] = $anAttachment;
			} else if ($anAttachment->isVideo ()) {
				$videoList [] = $anAttachment;
			} else {
				$otherFilesList [] = $anAttachment;
			}
		}
		
		$thumbs = '<div class="my-simple-gallery" itemscope itemtype="http://schema.org/ImageGallery">';
		
		foreach ( $imagesList as $anAttachment ) {
			
			$src = $anAttachment->getServerFileUrl ();
			$msrc = $anAttachment->getThumbnailUrl ();
			
			$imgSize = getimagesize ( $anAttachment->getServerFilePath () );
			
			$thumbs .= '<figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
								        <a href="' . $src . '" itemprop="contentUrl" data-size="' . $imgSize [0] . 'x' . $imgSize [1] . '">
								            <img src="' . $msrc . '" itemprop="thumbnail" alt="Image description"  />
								        </a>
										<figcaption itemprop="caption description">' . $anAttachment->description . '</figcaption>
								    </figure>';
		}
		
		$thumbs .= "</div>";
		
		if (sizeof ( $imagesList ) > 0) {
			$thumbs .= "<a href='index.php?show&class=Page&id=57#DroitImage'>Informations sur le droit à l’image</a>";
		}
		
		foreach ( $videoList as $anAttachment ) {
			
			$src = $anAttachment->getServerFileUrl ();
			$msrc = $anAttachment->getThumbnailUrl ();
			
			$thumbs .= "<h3>{$anAttachment->description}</h3>";
			if ($anAttachment->typeMime == "video/youtube") {
				$thumbs .= "<iframe width='560' height='315' src='https://www.youtube.com/embed/{$anAttachment->originalFileName}' frameborder='0' allowfullscreen></iframe>";
			} else {
				throw new Exception ( "Le type de video {$anAttachment->typeMime} n'est pas pris en charge" );
			}
		}
		
		foreach ( $musicList as $anAttachment ) {
			
			$src = $anAttachment->getServerFileUrl ();
			$dlLink = "index.php?show&class=Attachment&id={$anAttachment->getPrimaryKey()}&forceDownload";
			$msrc = $anAttachment->getThumbnailUrl ();
			
			$thumbs .= "<table><tr><td><a href='{$dlLink}'><img src='{$msrc}'></a></td><td style='vertical-align : middle;'><b>{$anAttachment->originalFileName}</b><br />{$anAttachment->description}<br /><audio controls style='width: 300px;' preload='none'><source src='{$src}' type='audio/mpeg'>Votre navigateur ne supporte pas les fichiers MP3.</audio><br /><a class='forceDownloadLink' href='{$dlLink}'>Enregistrer sur mon ordinateur</a></td></tr></table>";
		}
		
		foreach ( $otherFilesList as $anAttachment ) {
			
			$src = $anAttachment->getServerFileUrl ();
			$forceDlLink = "index.php?show&class=Attachment&id={$anAttachment->getPrimaryKey()}&forceDownload";
			$dlLink = "index.php?show&class=Attachment&id={$anAttachment->getPrimaryKey()}";
			$msrc = $anAttachment->getThumbnailUrl ();
			
			$thumbs .= "<table><tr><td><a href='{$dlLink}' target='_blank'><img src='{$msrc}'></a></td><td style='vertical-align : middle;'><a href='{$dlLink}' target='_blank'>{$anAttachment->originalFileName}</a><br />{$anAttachment->description} <br /> <a class='forceDownloadLink' href='{$forceDlLink}'>Enregistrer sur mon ordinateur</a></td></tr></table>";
		}
		
		$finalBody .= $thumbs;
		$finalBody .= $photoSwiper;
		
		$page->appendBody ( $finalBody );
	}
	
	$page->appendBody ( "</div>" );
}

if ($pageToShow->allowMemberAttachments && Roles::isMembre () && ! Roles::isGestionnaireCategorie ()) {
	$link = "<a href='index.php?edit&class=Attachment&id={$pageToShow->getPrimaryKey()}&included=false'>Ajouter mes photos</a>";
	$page->appendBody ( $link );
}

/*
 *
 * if (file_exists($fsDir."/images") && file_exists($fsDir."/thumbnails")) {
 * //Mode Gallerie
 *
 *
 * $photoSwiper = file_get_contents("includes/html/photoswipe.html");
 *
 *
 * $list = scandir($fsDir."/images/");
 *
 * $thumbs = '<div class="my-simple-gallery" itemscope itemtype="http://schema.org/ImageGallery">';
 *
 * foreach ($list as $aFile){
 * if(is_dir($fsDir.$aFile) || strpos($aFile,".") === 0)
 * continue;
 *
 * $src = $urlDir."/images/".$aFile;
 * $msrc = $urlDir."/thumbnails/".$aFile;
 *
 * $imgSize = getimagesize($fsDir."/images/".$aFile);
 *
 * $thumbs .= '<figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
 * <a href="'.$src.'" itemprop="contentUrl" data-size="'.$imgSize[0].'x'.$imgSize[1].'">
 * <img src="'.$msrc.'" itemprop="thumbnail" alt="Image description" />
 * </a>
 *
 * </figure>';
 *
 * }
 *
 *
 * $thumbs .= "</div>";
 *
 * $finalBody .= $thumbs;
 * $finalBody .= $photoSwiper;
 *
 *
 * } else {
 * $array = scandir($fsDir);
 *
 * foreach ($array as $anElement){
 * if(is_dir($fsDir.$anElement) || strpos($anElement,".") === 0)
 * continue;
 *
 * $ext = pathinfo($dir.'/'.$anElement, PATHINFO_EXTENSION);
 * $ext = strtolower($ext);
 *
 * switch ($ext){
 * case "jpg":
 * case "png":
 * case "gif":
 * $finalBody .= "<img src='{$urlDir}{$anElement}' />";
 * break;
 * case "youtube":
 * $youtubeId = str_ireplace(".youtube", "", $anElement) ;
 * $finalBody .= "<iframe width='560' height='315' src='https://www.youtube.com/embed/{$youtubeId}' frameborder='0' allowfullscreen></iframe>";
 * break;
 * case "mp3":
 * $finalBody .= "<a href='{$urlDir}{$anElement}'>{$anElement}</a><audio controls><source src='{$urlDir}{$anElement}' type='audio/mpeg'>Votre navigateur ne supporte pas les fichiers MP3.</audio>";
 * break;
 * default :
 * $finalBody .= "<a href='{$urlDir}{$anElement}' target='_blank'>$anElement</a>";
 * }
 *
 * }
 *
 * }
 *
 *
 * $page->appendBody($finalBody);
 *
 * //file_put_contents($fsDir."/cache.html", $finalBody);
 *
 */

?>