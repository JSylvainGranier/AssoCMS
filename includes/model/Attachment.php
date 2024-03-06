<?php
class Attachment extends HasMetaData {
	public static $DOCS_ROOT_FS = "./documents/pages/";
	public $idAttachment;
	public $originalFileName;
	public $serverFileName;
	public $typeMime;
	public $description;
	public $page;
	public $ordre;
	public $isPublic = false;
	private static $memberDeclaration;
	static function getMembersDeclaration() {
		if (is_null ( Attachment::$memberDeclaration )) {
			$pk = new SqlColumnMappgin ( "idAttachment", null, SqlColumnTypes::$INTEGER );
			$pk->setPrimaryKey ( true );
			
			$page = new SqlColumnMappgin ( "fkPage", "Page à laquelle le fichier est lié", SqlColumnTypes::$INTEGER );
			$page->setForeing ( new Page (), "page", true, true );
			
			Attachment::$memberDeclaration = array (
					$pk,
					$page,
					new SqlColumnMappgin ( "originalFileName", "Nom original du fichier", SqlColumnTypes::$VARCHAR, 255 ),
					new SqlColumnMappgin ( "serverFileName", "Nom du fichier sur le serveur", SqlColumnTypes::$VARCHAR, 255 ),
					new SqlColumnMappgin ( "typeMime", "Type Mime du fichier", SqlColumnTypes::$VARCHAR, 70 ),
					new SqlColumnMappgin ( "description", "Description accompagnant le fichier", SqlColumnTypes::$LONGTEXT ),
					new SqlColumnMappgin ( "ordre", "Ordre du fichier parmis ceux liés au même fichier.", SqlColumnTypes::$INTEGER ),
					new SqlColumnMappgin ( "isPublic", "Le fichier est accessible même si on n'est pas connecté.", SqlColumnTypes::$BOOLEAN ) 
			);
			
			Attachment::$memberDeclaration = array_merge ( Attachment::$memberDeclaration, HasMetaData::getMembersDeclaration () );
		}
		
		return Attachment::$memberDeclaration;
	}
	public function getPrimaryKey() {
		return $this->idAttachment;
	}
	public function setPrimaryKey($newId) {
		$this->idAttachment = $newId;
	}
	function delete() {
		// Il faut supprimer le fichier en même temps
		if (file_exists ( $this->getThumbnailServerPath () )) {
			unlink ( $this->getThumbnailServerPath () );
		}
		
		if (file_exists ( $this->getServerFilePath () )) {
			unlink ( $this->getServerFilePath () );
		}
		
		parent::delete ();
	}
	public function getShortToString() {
		return "<a href='{$this->getDownloadLink()}' target='_blank'>$this->originalFileName</a>";
	}
	protected function getNaturalOrderColumn() {
		return "ordre";
	}
	
	/**
	 * Retourne un formulaire pour éditer les méta du fichier (Titre et description)
	 */
	public function getEditionFormHtml() {
		if (file_exists ( $this->getServerFilePath () )) {
			
			$allowUpdate = true;
			if (! Roles::isGestionnaireCategorie ()) {
				if (getUserId () != $this->getLastUpdatePersonne ()->getPrimaryKey ()) {
					$allowUpdate = false;
				}
			}
			
			$sm = new SmartPage ( "editAttachmentItem.html" );
			
			$sm->asset ( "id", $this->getPrimaryKey () );
			$sm->asset ( "originalFileName", $this->originalFileName );
			$sm->asset ( "description", $this->description );
			$sm->asset ( "ordre", $this->ordre );
			$sm->asset ( "thumbnail", $this->getThumbnailUrl () );
			if ($this->isPublic) {
				$sm->asset ( "publicChecked", "checked='checked'" );
			} else {
			}
			if (! $allowUpdate) {
				$sm->asset ( "disabled", "disabled='disabled'" );
			}
		} else {
			
			$sm = new SmartPage ( "editAttachmentItemNotFound.html" );
		}
		
		return $sm->buildPage ( false );
	}
	
	/**
	 * Retourne une URL pouvant être utilisée comme mignature
	 * du fichier.
	 * Si la mignature n'existe pas, renvéra une icone correspondante
	 * au type de fichier
	 */
	public function getThumbnailUrl() {
		if (file_exists ( $this->getThumbnailServerPath () ) && filesize($this->getThumbnailServerPath ()) > 0) {
			return $this->getServerFileUrl () . ".thumbnail";
		} else {
			return SITE_ROOT . "ressources/mimetypes/{$this->typeMime}-icon-64x64.png";
		}
	}
	
	/**
	 * Retourne le chemin de l'image correspondant à la mignature du fichier, si elle existe.
	 */
	public function getThumbnailServerPath() {
		return $this->getServerFilePath () . ".thumbnail";
	}
	
	/**
	 * Retourne le chemin système pour accéder au fichier.
	 */
	public function getServerFilePath() {
		return $this->getServerDirPath () . $this->serverFileName;
	}
	
	/**
	 * Retourne le chemin système pour accéder au dossier qui conteint le fichier.
	 */
	public function getServerDirPath() {
		$idPage = 0;
		if (is_int ( $this->page )) {
			$idPage = $this->page;
		} else {
			$idPage = $this->page->getPrimaryKey ();
		}
		return Attachment::$DOCS_ROOT_FS . "{$idPage}/";
	}
	
	/**
	 * Retourne l'URL qui permet d'accéder au fichier dans son nom serveur.
	 */
	public function getServerFileUrl() {
		$idPage = 0;
		if (is_int ( $this->page )) {
			$idPage = $this->page;
		} else {
			$idPage = $this->page->getPrimaryKey ();
		}
		return SITE_ROOT . "documents/pages/{$idPage}/" . $this->serverFileName;
	}
	
	/**
	 * Liste tous les Attachment d'un page.
	 * Si le visiteur n'est pas authentifié, retournera uniquement les fichiers qui sont publics.
	 *
	 * @param integer $idPage
	 *        	id de la page
	 * @return Attachment[]
	 */
	public function listAttachmentsForPage($idPage) {
		$memberRestriction = Roles::isMembre () ? "" : "and isPublic = true";
		$query = "select * from attachment where fkPage = {$idPage} {$memberRestriction} order by ordre asc";
		
		$atchList = $this->getObjectListFromQuery ( $query );
		
		return $atchList;
	}
	
	/**
	 * Retourne le nombre de pièce jointes liées à cette page, peut importe que
	 * le visiteur soit connecté ou pas.
	 *
	 * @param unknown $idPage        	
	 */
	public function countAttachmentsForPage($idPage) {
		$query = "select count(*) as 'count' from attachment where fkPage = {$idPage}";
		$assoc = $this->getDataFromQuery ( $query );
		return $assoc [0] ["count"];
	}
	public function updateWithARGS() {
		global $ARGS;
		$this->ordre = $ARGS ["ordre"];
		$this->originalFileName = $ARGS ["originalFileName"];
		$this->description = $ARGS ["description"];
		
		$this->isPublic = array_key_exists ( "public", $ARGS );
	}
	public static function buildAttachment($idPage, $fileAttributes) {
		$att = new Attachment ();
		$existingAttachList = $att->listAttachmentsForPage ( $idPage );
		$att->ordre = 0;
		if ($existingAttachList != null) {
			$att->ordre = sizeof ( $existingAttachList ) * 100;
		}
		
		$att->typeMime = $fileAttributes ["type"];
		
		$serverFileName = round(microtime(true) * 1000);+"";
		$serverFileName = strtolower ( substr ( $serverFileName, strrpos ( $serverFileName, DIRECTORY_SEPARATOR ) + 1 ) );
		
		// extention du fichier original
		$originalFileName = $fileAttributes ['name'];
		$originalFileName = stripslashes ( $originalFileName );
		$ext = strtolower ( substr ( $originalFileName, strrpos ( $originalFileName, '.' ) + 1 ) );
		
		if (strtolower ( $ext ) === "youtube") {
			$att->typeMime = "video/youtube";
		} else if (strtolower ( $ext ) === "rtf") {
			$att->typeMime = "text/richtext";
		}
		
		$originalFileName = str_replace ( "." . $ext, "", $originalFileName );
		
		$serverFileName = str_replace ( "." . $ext, "", $serverFileName );
		$serverFileName .= "." . $ext;
		
		$att->serverFileName = $serverFileName;
		$att->originalFileName = $originalFileName;
		
		$att->page = $idPage + 0;
		
		return $att;
	}
	public function isImage() {
		switch ($this->typeMime) {
			case "image/jpeg" :
			case "image/gif" :
			case "image/png" :
				return true;
		}
		return false;
	}
	public function isMP3() {
		switch ($this->typeMime) {
			case "audio/mpeg" :
			case "audio/mpeg3" :
			case "audio/mp3" :
			case "audio/x-mpeg-3" :
				return true;
		}
		return false;
	}
	public function isVideo() {
		$pos = stripos ( $this->typeMime, "video" );
		if ($pos !== false) {
			return ($pos >= 0);
		} else {
			return false;
		}
	}
	public function createTumbnail() {
		if ($this->isImage ()) {
			
			// Si c'est une image, on prépare un redimentionnement
			
			list ( $width, $height ) = getimagesize ( $this->getServerFilePath () );
			
			$imgRatio = $height / $width;
			
			$thumbWidth = min ( $width, 280 );
			$thumbHeight = $thumbWidth * $imgRatio;
			
			$thumbImage = imagecreatetruecolor ( $thumbWidth, $thumbHeight );
			
			switch ($this->typeMime) {
				case "image/jpeg" :
					$source = imagecreatefromjpeg ( $this->getServerFilePath () );
					break;
				case "image/gif" :
					$source = imagecreatefromgif ( $this->getServerFilePath () );
					break;
				case "image/png" :
					$source = imagecreatefrompng ( $this->getServerFilePath () );
					break;
			}
			
			// Crop
			
			imagecopyresampled ( $thumbImage, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height );
			
			switch ($this->typeMime) {
				case "image/jpeg" :
					imagejpeg ( $thumbImage, $this->getThumbnailServerPath (), 80 );
					break;
				case "image/gif" :
					imagegif ( $thumbImage, $this->getThumbnailServerPath () );
					break;
				case "image/png" :
					imagepng ( $thumbImage, $this->getThumbnailServerPath (), 80 );
					break;
			}
		}
	}
	
	public static function deleteForPage(Page $page){
		$at = new Attachment();
		$atList = $at->listAttachmentsForPage($page->idPage);
		foreach ($atList as $anAttachment){
			$anAttachment->delete();
		}
	}
}

?>