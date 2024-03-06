<?php
/**
 * État de publication d'une page
 *
 */
class PageEtat {
	public static $SUPPRIME = - 15;
	public static $PROPOSITION_REFFUSEE = - 10;
	public static $BROUILLON = 0;
	public static $PROPOSE = 10;
	public static $ACCESS_CATEGORIE = 15;
	public static $ACCESS_MEMBRE = 25;
	public static $ACCESS_PUBLIC = 50;
}
class Page extends HasMetaData {
	/**
	 *
	 * @var integer
	 */
	public $idPage;
	/**
	 *
	 * @var string
	 */
	public $titre;
	/**
	 *
	 * @var string
	 */
	public $introduction;
	/**
	 *
	 * @var string
	 */
	public $suite;
	/**
	 *
	 * @var integer
	 */
	public $etat;
	/**
	 *
	 * @var Personne
	 */
	public $auteur;
	/**
	 *
	 * @var Page
	 */
	public $pageParent;
	/**
	 *
	 * @var String
	 */
	public $fileName;
	public $ordre;
	public $allowReactions;
	public $allowMemberAttachments = false;
	public $displayInMenu;
	
	/**
	 *
	 * @var Categorie
	 */
	public $categorieClassement;
	
	/**
	 * La page est surclassée par autre chose.
	 * Exemple : un evenement.
	 *
	 * @var boolean
	 */
	public $isSubClass = false;
	public function getPrimaryKey() {
		return $this->idPage;
	}
	public function setPrimaryKey($newId) {
		$this->idPage = $newId;
	}
	private static $memberDeclaration = null;
	static function getMembersDeclaration() {
		if (is_null ( Page::$memberDeclaration )) {
			$pk = new SqlColumnMappgin ( "idPage", null, SqlColumnTypes::$INTEGER );
			$pk->setPrimaryKey ( true );
			
			$categorie = new SqlColumnMappgin ( "fkCategorie", "Page dans la catégorie", SqlColumnTypes::$INTEGER );
			$categorie->setForeing ( new Categorie (), "categorieClassement", true, true );
			
			$auteur = new SqlColumnMappgin ( "fkAuteur", "Auteur ayant créé la page", SqlColumnTypes::$INTEGER );
			$auteur->setForeing ( new Personne (), "auteur", false, true );
			
			$allowReactions = new SqlColumnMappgin ( "allowReactions", "Les réactions sont autorisées sur cette page", SqlColumnTypes::$BOOLEAN );
			$allowReactions->defaultValue = true;
			
			$allowMemberAttachments = new SqlColumnMappgin ( "allowMemberAttachments", "Les membres peuvent ajouter leurs pièces jointes", SqlColumnTypes::$BOOLEAN );
			
			$displayInMenu = new SqlColumnMappgin ( "displayInMenu", "Affiche cette page dans le menu", SqlColumnTypes::$BOOLEAN );
			
			Page::$memberDeclaration = array (
					$pk,
					new SqlColumnMappgin ( "titre", "Titre", SqlColumnTypes::$VARCHAR, 255 ),
					new SqlColumnMappgin ( "introduction", "Introduction", SqlColumnTypes::$LONGTEXT ),
					new SqlColumnMappgin ( "suite", "Suite après l'introduction", SqlColumnTypes::$LONGTEXT ),
					new SqlColumnMappgin ( "isSubClass", null, SqlColumnTypes::$BOOLEAN ),
					new SqlColumnMappgin ( "ordre", "Ordre d'affichage", SqlColumnTypes::$INTEGER ),
					new SqlColumnMappgin ( "etat", "Etat de publication", SqlColumnTypes::$INTEGER ),
					$allowReactions,
					$allowMemberAttachments,
					$displayInMenu,
					$categorie,
					$auteur,
					new SqlColumnMappgin ( "fileName", "Nom de la page respectant la norme URL", SqlColumnTypes::$VARCHAR, 128 ) 
			);
			
			Page::$memberDeclaration = array_merge ( Page::$memberDeclaration, HasMetaData::getMembersDeclaration () );
		}
		
		return Page::$memberDeclaration;
	}
	public function getTitre() {
		return $this->titre;
	}
	public function getIntroduction() {
		return $this->introduction;
	}
	public function getSuite() {
		return $this->suite;
	}
	public function getShortToString() {
		return $this->titre;
	}
	protected function getNaturalOrderColumn() {
		return "ordre";
	}
	public function save() {
		if (is_null ( $this->auteur ) || $this->auteur < 1) {
			$this->auteur = thisUserId();
		}
		return parent::save ();
	}
	public function getAllInCategorie($idCategorie, $isSubClass = false) {
		$boolVal = Persistant::toSql ( $isSubClass );
		return $this->getObjectListFromQuery ( "select * from page where fkCategorie = {$idCategorie} and isSubClass = {$boolVal}" );
	}
	
	/**
	 * Retourne toutes les pages de la catégorie indiquée, sous réserve
	 * que les pages respectent les filtres de session de l'utilisateur
	 * ou en appliquant le filtre visiteur.
	 */
	public function getFilteredInCategorie($idCategorie, $isSubClass = false, $onlyInMenuPages = false) {
		$boolVal = Persistant::toSql ( $isSubClass );
		
		$sql = "select * from page where fkCategorie = {$idCategorie} and isSubClass = {$boolVal} and ";
		$sql .= $this->getSqlFilterForPagePublicationState ();
		
		if ($onlyInMenuPages) {
			$sql .= " and displayInMenu = true ";
		}
		
		$sql .= " order by " . $this->getNaturalOrderColumn ();
		
		// echo $sql;
		
		return $this->getObjectListFromQuery ( $sql );
	}
	
	/**
	 * Retourne le nombre de pages (et sous classes) qui sont masquées
	 * pour faute de filtre visiteur.
	 *
	 * @param unknown_type $idCategorie        	
	 */
	public function getHiddenCountInCategorie($idCategorie) {
		$sql = "select count(*) as 'count' from page where fkCategorie = {$idCategorie} and not ({$this->getSqlFilterForPagePublicationState()})";
		
		$assoc = $this->getDataFromQuery ( $sql );
		
		return $assoc [0] ["count"];
	}
	
	/**
	 *
	 * @return Categorie
	 */
	public function getCategorieClassement() {
		return $this->fetchLasyObject ( "categorieClassement", "Categorie" );
	}
	/**
	 *
	 * @return Personne
	 */
	public function getAuteur() {
		return $this->fetchLasyObject ( "auteur", "Personne" );
	}
	
	/**
	 * Selon les préférences de l'utilisateur et le type de visiteur au sujet
	 * des pages visibles (ref.
	 * Page->etat), retourne les conditions SQL
	 * qui permettent de filtrer les pages retournées.
	 */
	public static function getSqlFilterForPagePublicationState($idCategorie = null) {
		$filter = " (";
		
		if (Roles::isSuperAdmin ()) {
			$filter .= " 1 = 1 ";
		} else if (Roles::isGestionnaireGlobal ()) {
			
			$filter .= " etat >= " . PageEtat::$PROPOSE . " or ( etat = " . PageEtat::$BROUILLON . " and fkAuteur = {$_SESSION["userId"]} )";
		} else if (Roles::isGestionnaireOfCategorie ( $idCategorie )) {
			
			$filter .= " etat >= " . PageEtat::$PROPOSE . " or ( etat = " . PageEtat::$BROUILLON . " and fkAuteur = ".thisUserId()." )";
		} else if (Roles::isMembre ()) {
			
			$autorisedCat = "";
			$catSize = count ( $_SESSION ["userCategories"] );
			for($idx = 0; $idx < $catSize; $idx ++) {
				$autorisedCat .= $_SESSION ["userCategories"] [$idx];
				if ($idx + 1 < $catSize) {
					$autorisedCat .= ",";
				}
			}
			
			$filter .= " etat >= " . PageEtat::$ACCESS_MEMBRE . " ";
			$filter .= " OR fkAuteur = ".thisUserId();
			if (strlen ( $autorisedCat ) > 0)
				$filter .= " OR ( etat = " . PageEtat::$ACCESS_CATEGORIE . " and fkCategorie in ({$autorisedCat}) )";
		} else {
			$filter .= " etat >= " . PageEtat::$ACCESS_PUBLIC;
		}
		
		$filter .= " ) ";
		
		return $filter;
	}
	/**
	 * Retourne vrai si la page courante peut-être montrée à l'utilisateur courant
	 * si non, false.
	 */
	public function canBeShown(){
	    if (Roles::isSuperAdmin ()) {
	        return true;
	    } else if (Roles::isGestionnaireGlobal ()) {
	        
	        //$filter .= " etat >= " . PageEtat::$PROPOSE . " or ( etat = " . PageEtat::$BROUILLON . " and fkAuteur = {$_SESSION["userId"]} )";
	        
	        return ($this->etat >= PageEtat::$PROPOSE) || ($this->etat = PageEtat::$BROUILLON && $this->auteur->idPersonne == $_SESSION["userId"] ) ;
	    } else if (Roles::isGestionnaireOfCategorie ( $this->categorieClassement )) {
	        
	        //$filter .= " etat >= " . PageEtat::$PROPOSE . " or ( etat = " . PageEtat::$BROUILLON . " and fkAuteur = ".thisUserId()." )";
	        
	        return ($this->etat >= PageEtat::$PROPOSE) || ($this->etat = PageEtat::$BROUILLON && $this->auteur->idPersonne == thisUserId() ) ;
	        
	    } else if (Roles::isMembre ()) {
	        
	        $accesCategorie = $this->etat == PageEtat::$ACCESS_CATEGORIE;
	        
	        
	        if($accesCategorie){
	            
	            $accesCategorie = false;
	            
	            $autorisedCat = "";
	            $catSize = count ( $_SESSION ["userCategories"] );
	            for($idx = 0; $idx < $catSize; $idx ++) {
	                if($_SESSION ["userCategories"] [$idx] == $this->getCategorieClassement()->idCategorie){
	                    $accesCategorie = true;
	                }
	            }
	            
	        }
	        
	        
	        
	        //$filter .= " etat >= " . PageEtat::$ACCESS_MEMBRE . " ";
	        //$filter .= " OR fkAuteur = ".thisUserId();
	        //if (strlen ( $autorisedCat ) > 0)
	        //    $filter .= " OR ( etat = " . PageEtat::$ACCESS_CATEGORIE . " and fkCategorie in ({$autorisedCat}) )";
	        
	            return ($this->etat >= PageEtat::$ACCESS_MEMBRE) || ($this->getAuteur()->idPersonne == thisUserId() ) || $accesCategorie ;
	    } else {
	        //$filter .= " etat >= " . PageEtat::$ACCESS_PUBLIC;
	        return ($this->etat >= PageEtat::$ACCESS_PUBLIC);
	    }
	}
	
	
	public function delete() {
		$idPage = $this->getPrimaryKey ();
		
				
		Reaction::deleteForPage($this);
		Attachment::deleteForPage($this);
		
		parent::delete ();
	}
	
	/**
	 * Si la page à une description, une introduction plus longue que celle renvoyée sans HTML, ou des pièces jointes,
	 * retourne un lien "Lire la suite...", éventuellement accompagné d'une petite icone indiquant la présance de pièces jointes.
	 */
	public function getReadMoreLink() {
		$link = $this->getShowURL ();
		
		$filesIcons = $this->getAttachmentPreciInLink ();
		
		$suiteLenght = strlen($this->suite);
		$iconsLenght = strlen($filesIcons);
		
		if ($suiteLenght > 15 || $iconsLenght > 3){
			$readMoreLink = "<a href='{$link}' class='readMoreLink'>Cliquez ici</a> pour lire la suite. {$filesIcons}";
		} else {
			$readMoreLink = "";
		}
		
		return $readMoreLink;
	}
	public function getAttachmentPreciInLink() {
		$attachment = new Attachment ();
		$query = "select * from attachment where fkPage = {$this->getPrimaryKey()}";
		
		$attachmentList = $attachment->getObjectListFromQuery ( $query );
		
		$images = 0;
		$audios = 0;
		$others = 0;
		$videos = 0;
		
		foreach ( $attachmentList as $anAttachement ) {
			if ($anAttachement->isImage ()) {
				$images ++;
			} else if ($anAttachement->isMP3 ()) {
				$audios ++;
			} else if ($anAttachement->isVideo ()) {
				$videos ++;
			} else {
				$others ++;
			}
		}
		
		$attachmentCount = $images + $audios + $others + $videos;
		
		$filesIcons = "";
		
		$assoc = $this->getDataFromQuery("select count(*) as cnt from reaction where fkPage = {$this->getPrimaryKey()}");
		
		if ($attachmentCount > 0 || strlen ( $this->suite ) > 10 || $assoc[0]["cnt"] > 0) {
				$filesIcons = " (";
				$coma = "";
				if ($images > 0) {
					$filesIcons .= "{$images} <i class='fa fa-camera' title='{$images} photo(s)'></i>";
					$coma = ", ";
				}
				
				if ($audios > 0) {
					$filesIcons .= "{$coma}{$audios}<i class='fa fa-music' title='{$audios} fichier(s) audio'></i>";
					$coma = ", ";
				}
				
				if ($videos > 0) {
					$filesIcons .= "{$coma}{$videos}<i class='fa fa-video-camera' title='{$videos} vidéo(s)'></i>";
					$coma = ", ";
				}
				
				if ($others > 0) {
					$filesIcons .= "{$coma}{$others}<i class='fa fa-paperclip' title='{$others} pièces jointes'></i>";
					$coma = ", ";
				}
				
				if ($assoc[0]["cnt"] == 1){
					$filesIcons .= "{$coma}<i class='fa fa-comment-o' aria-hidden='true' title='Un commentaire'></i>";
				} else if ($assoc[0]["cnt"] > 1){
					$filesIcons .= "{$coma}<i class='fa fa-comments-o' aria-hidden='true' title='{$assoc[0]["cnt"]} commentaires'></i>";
				}
				
				$filesIcons .= ")";
				
				if(strlen($filesIcons) == 3) $filesIcons = "";
		}
		
		
		return $filesIcons;
	}
	
	
	public static function deleteForPersonne(Personne $p){
		$sql = "update page set fkAuteur = null where fkAuteur = ".$p->idPersonne;
		Page::ask($sql);
	}
	
}
function rmdir_recursive($dir) {
	foreach ( scandir ( $dir ) as $file ) {
		if ('.' === $file || '..' === $file)
			continue;
		if (is_dir ( "$dir/$file" ))
			rmdir_recursive ( "$dir/$file" );
		else
			unlink ( "$dir/$file" );
	}
	rmdir ( $dir );
}