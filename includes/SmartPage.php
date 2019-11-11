<?php
class SmartPage {
	private $body;
	private $templateConfigurated = false;
	private $disableStandardOutput = false;
	public $displayPhpErrors = true;
	/**
	 *
	 * @var Categorie
	 */
	private $currentCategorie = null;
	public function __construct($specificSkeltonFileName = null) {
		$this->reset ( $specificSkeltonFileName );
	}
	public function reset($specificSkeltonFileName = null) {
		$this->setStandardOuputDisabled ( false );
		if (! is_null ( $specificSkeltonFileName )) {
			$skelton = file_get_contents ( "includes/html/" . $specificSkeltonFileName );
			$this->body = $skelton;
		} else {
			$this->body = file_get_contents ( "includes/html/html.html" );
		}
	}
	public function setStandardOuputDisabled($disabled) {
		$this->disableStandardOutput = $disabled;
	}
	public function isStandardOututDisabled() {
		return $this->disableStandardOutput;
	}
	public function asset($tagName, $replacement) {
		$this->body = str_ireplace ( '$' . $tagName . '$', $replacement, $this->body );
	}
	public function append($tagName, $html) {
		$inxOfTag = strpos ( $this->body, '$' . $tagName . '$' );
		$start = substr ( $this->body, 0, $inxOfTag );
		$end = substr ( $this->body, $inxOfTag );
		$this->body = $start . "\r\n" . $html . "\r\n" . $end;
	}
	public function appendNotification($strNotification, $autoHideDelaySeconds = 0) {
		$this->append ( "globalNotifZone", "<p class='notification'>{$strNotification}</p>" );
	}
	public function appendBody($html) {
		$this->append ( "body", $html );
	}
	public function buildPage($browserPage = true) {
		if ($browserPage) {
			$this->append ( "menu", $this->getMenu () );
			
			global $ARGS;
			$getKeys = array_keys ( $_GET );
			$argsKeys = array_keys ( $ARGS );
			$currentAction = $argsKeys [0];
			$currentUrl = $getKeys [0];
			if (! Roles::isMembre () && ($currentUrl !== "login" && $currentAction !== "login")) {
				$this->doAppendInvitationLogin ();
			}
			
			$this->configureTemplate ();
		}
		
		$credits = file_get_contents ( "ressources/template/credits.html" );
		$this->asset ( "footer", $credits );
		
		$this->asset ( "site_root", SITE_ROOT );
		$this->asset ( "site_title", SITE_TITLE );
		$this->asset ( "site_domain", SITE_DOMAIN );
		$this->asset ( "site_path", SITE_PATH );
		$this->asset ( "email_on_error", EMAIL_ON_ERROR );
		
		
		if ($this->displayPhpErrors === true) {
			$this->asset ( "php_errors", $this->formatErrors ( true ) );
		}
		
		$this->body = preg_replace ( '/\$.*\$/', "", $this->body );
		return $this->body;
	}
	private function doAppendInvitationLogin() {
		$currentUrl = basename ( $_SERVER ['REQUEST_URI'] );
		
		$currentUrl = str_replace ( "index.php?", "", $currentUrl );
		
		$currentUrl = urlencode ( $currentUrl );
		
		$smallLoginForm = file_get_contents ( "includes/html/ath-notLogged.small.html" );
		$this->appendBody ( $smallLoginForm );
		$this->asset ( "currentUrl", $currentUrl );
	}
	public function setTitle($title) {
		$this->asset ( "pageTitle", $title . " — " . SITE_TITLE );
	}
	public function appendActionButton($title, $action, $actionIsAJsFunction = false, $isForGestionnaire = true) {
		$adminLinkClass = $isForGestionnaire || Roles::isGestionnaireCategorie () ? "class='adminLink'" : "";
		$html = "<a {$adminLinkClass} href='" . SITE_ROOT . "index.php?" . $action . "'>{$title}</a>";
		
		if ($actionIsAJsFunction)
			throw new Exception ( "SmartPage::appendActionButton n'est pas encore fait avec une action JavaScript." );
		
		$this->appendBody ( $html );
	}
	public function appendEditionBar($class, $id) {
		$r = "<a class='adminLink' href='index.php?edit&class={$class}&id={$id}&afterSaveAction=page'>Modifier</a>";
		$r .= " <a class='adminLink' href='index.php?delete&class={$class}&id={$id}'>Supprimer</a>";
		
		$this->appendBody ( $r );
	}
	public function getMenu() {
		$r = '<ul  class="nav">';
		
		$r .= '<li class="nav-item"><a  href="$site_root$index.php?home"><div class="logo nav-link"><img src="$site_root$/ressources/template/logo.png" style="width: 50%;" /></div></a></li>' . "\r\n";
		
		$catRoot = new Categorie ();
		$catList = $catRoot->getAll ();
		
		$pageRoot = new Page ();
		
		if (! is_null ( $catList ))
			foreach ( $catList as $aCat ) {
				/* @var $aCat Categorie */
				$r .= '<li class="nav-item"><a class="nav-link" href="$site_root$index.php?list&class=Page&categorie=' . $aCat->getPrimaryKey () . '">' . $aCat->nom . '</a>' . "\r\n";
				
				$pageList = $pageRoot->getFilteredInCategorie ( $aCat->getPrimaryKey (), false, true );
				
				$eventCount = $aCat->getNombreEvenementFutur ();
				
				if (count ( $pageList ) > 0 || $eventCount > 0) {
					$r .= "<ul class='accordion'>\r\n";
					
					if (count ( $pageList ) > 0) {
						foreach ( $pageList as $aPage ) {
							/* @var $aPage Page */
							$r .= '<li class="nav-item"><a class="nav-link" href="$site_root$index.php?show&class=Page&id=' . $aPage->getPrimaryKey () . '">' . $aPage->getTitre () . '</a>' . "\r\n";
							$r .= "</li>\r\n";
						}
					}
					
					if ($eventCount > 0) {
						$r .= '<li class="nav-item"><a class="nav-link" href="$site_root$index.php?show&class=Calendrier&categorie=' . $aCat->getPrimaryKey () . '">RDV ' . $aCat->nom . '</a>' . "\r\n";
					}
					
					$r .= "</ul>\r\n";
				}
				
				$r .= "</li>\r\n";
			}
		
		$r .= '<li class="nav-item"><a class="nav-link" href="$site_root$index.php?photobook">Album photo</a></li>' . "\r\n";
		
		$r .= '<li class="nav-item"><a class="nav-link" href="$site_root$index.php?trombinoscope">Trombinoscope</a></li>' . "\r\n";
		
		$r .= '<li class="nav-item"><a class="nav-link" href="$site_root$index.php?show&class=Calendrier">Calendrier</a></li>' . "\r\n";
		
		if (Roles::canManageCategories ()) {
			$r .= '<li class="nav-item"><a class="nav-link" href="$site_root$index.php?edit&class=Categorie">[Ajouter]</a></li>' . "\r\n";
		}
		
		$r .= '<li class="nav-item"><hr /></li>' . "\r\n";
		
		if (Roles::isMembre ()) {
			
			$r .= '<li class="nav-item"><a class="nav-link" href="index.php?show&class=Personne">Mon Compte</a>' . "\r\n";
			
			$r .= "<ul class='accordion'>\r\n";
			
			$r .= '<li class="nav-item"><a class="nav-link" href="index.php?login&phase=loggingOff">Se déconnecter</a></li>' . "\r\n";
			
			if (Roles::isSuperAdmin ()) {
				$r .= '<li class="nav-item"><a class="nav-link" href="index.php?superAdminMenu">Administration</a></li>' . "\r\n";
			}
			
			if (Roles::isGestionnaireGlobal ()) {
				$r .= '<li class="nav-item"><a class="nav-link" href="index.php?list&class=Publipostage">Publipostage</a></li>' . "\r\n";
			}
			
			$r .= "</li></ul>\r\n";
		} else {
			$r .= '<li class="nav-item"><a class="nav-link" href="index.php?login&phase=notLogged">Se connecter</a></li>' . "\r\n";
		}
		
		$r .= "</ul>\r\n";
		
		return $r;
	}
	public function addArticleMenu($class, $id) {
		$this->asset ( "articleMenu", file_get_contents ( "includes/html/articleMenuAdmin.html" ) );
		
		$this->asset ( "class", $class );
		$this->asset ( "id", $id );
	}
	public function setCategorie(Categorie $curCat) {
		$this->currentCategorie = $curCat;
	}
	private function configureTemplate() {
		if ($this->templateConfigurated)
			return;
		
		$this->templateConfigurated = true;
		
		$bgCss = null;
		
		if ($this->currentCategorie != null) {
			if (! is_null ( $this->currentCategorie->couleurRuban )) {
				$this->append ( "menuColor", $this->currentCategorie->couleurRuban );
				$this->asset ( "menuHoverColor", $this->currentCategorie->couleurRubanSurvol );
				$this->asset ( "menuSubElementsColor", "green" );
			}
			
			if (! is_null ( $this->currentCategorie->backgroundImage )) {
				$bgCss = $this->currentCategorie->backgroundImage;
			} else {
				$bgCss = "default.jpg";
			}
		} else {
			$bgCss = "default.jpg";
		}
		
		if ($bgCss != null && strlen ( $bgCss ) > 3) {
			$bgCssIe8 = "background-image: url('ressources/template/{$bgCss}')  !important;\nbackground-size: cover;";
			$bgCss = "background-image: url('ressources/template/alpha-40.png'), url('ressources/template/{$bgCss}')  !important; \nbackground-size: cover;";
		} else {
			$bgCssIe8 = "background-image: url('ressources/template/default.jpg')  !important;\nbackground-size: cover;";
			$bgCss = "background-image: url('ressources/template/alpha-40.png'), url('ressources/template/default.jpg')  !important; \nbackground-size: cover;";
		}
		
		$this->asset ( "backgroundImage", $bgCss );
		$this->asset ( "backgroundImageIe8", $bgCssIe8 );
	}
	
	/**
	 * Formate toute les erreurs présentes dans l'array PHP_ERRORS
	 *
	 * @param boolean $inHtml        	
	 */
	public function formatErrors($inHtml) {
		global $PHP_ERRORS;
		
		$ret = "";
		if (sizeof ( $PHP_ERRORS ) > 0 && DISPLAY_ERRORS) {
			$ret = "<article>";
			
			$errSeparator = $inHtml ? "<br />" : "\r\n";
			
			foreach ( $PHP_ERRORS as $anError ) {
				$errNo = $anError [0];
				$errStr = $anError [1];
				$errFile = $anError [2];
				$errLine = $anError [3];
				
				$file = str_ireplace ( getcwd (), "", $errFile );
				
				$ret .= "{$errNo}{$errStr} ({$file}@{$errLine}){$errSeparator}";
			}
			
			$ret .= "</article>";
		}
		
		return $ret;
	}
}

?>