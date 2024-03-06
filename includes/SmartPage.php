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
		$this->asset ( "EMAIL_WORKER_URL", EMAIL_WORKER_URL );
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
	    /*
	     *  <ul class="sidebar__nav">
                <li>
                    <a href="#" class="sidebar__nav__link">
                        <i class="mdi mdi-fingerprint"></i>
                        <span class="sidebar__nav__text">Fingerprint</span>
                    </a>
                </li>
	     * 
	     */
		$r = '<ul class="sidebar__nav">';
		
		
		$r .= '<li><a class="sidebar__nav__link" href="$site_root$index.php?home"><i><img src="$site_root$/ressources/template/logo-white.png" style="width: 2.1rem;" /></i><span class="sidebar__nav__text">VISA30</span></a></li>';
		
		$catRoot = new Categorie ();
		$catList = $catRoot->getAll ();
		
		$pageRoot = new Page ();
		
		$menuItems = array();
		/*
		if (! is_null ( $catList ))
			foreach ( $catList as $aCat ) {
				$menuItems[] = array('url' => '$site_root$index.php?list&class=Page&categorie=' . $aCat->getPrimaryKey (), 
				                    'text' => $aCat->nom,
				                    'classicon' => $aCat->iconClass
				                );
			}
		*/
		
		$produit = new Produit();
		if ($produit->hasInscriptionsOuvertesEnCeMoment()){
		    $menuItems[] = array('url' => '$site_root$index.php?list&class=InscriptionsOuvertes',
		        'text' => 'Inscriptions',
		        'classicon' => 'fa fa-pencil-square-o'
		    );
		}
		
		
		
		$menuItems[] = array('url' => '$site_root$index.php?list&class=Categorie',
		    'text' => 'Sections',
		    'classicon' => 'fa fa-star-half-o'
		);
		
		$menuItems[] = array('url' => '$site_root$index.php?show&class=Calendrier',
		    'text' => 'Calendrier',
		    'classicon' => 'fa fa-calendar-check-o'
		);

		$menuItems[] = array('url' => '$site_root$index.php?photobook',
		    'text' => 'Album photo',
		    'classicon' => 'fa fa-camera-retro'
		);
		
		$menuItems[] = array('url' => '$site_root$index.php?trombinoscope',
		    'text' => 'Trombinoscope',
		    'classicon' => 'fa fa-users'
		);
		
		$menuItems[] = array('url' => '$site_root$index.php?contact',
		    'text' => 'Contact',
		    'classicon' => 'fa fa-envelope-o'
		);
		
		
		
		
		if (Roles::isMembre ()) {
		    
		    $menuItems[] = array('url' => '',
		        'text' => '',
		        'classicon' => ''
		    );
		    
		    $menuItems[] = array('url' => '$site_root$index.php?show&class=Personne',
		        'text' => 'Mon Compte',
		        'classicon' => 'fa fa-user'
		    );
		    
		    $menuItems[] = array('url' => '$site_root$index.php?login&phase=loggingOff',
		        'text' => 'Se déconnecter',
		        'classicon' => 'fa fa-sign-out'
		    );
		    
			
			if (Roles::isSuperAdmin ()) {
				
				$menuItems[] = array('url' => '$site_root$index.php?superAdminMenu',
				    'text' => 'Administration',
				    'classicon' => 'fa fa-cogs'
				);
				
			}
			
			if (Roles::isGestionnaireGlobal ()) {
			    $menuItems[] = array('url' => '$site_root$index.php?list&class=Publipostage',
			        'text' => 'Publipostage',
			        'classicon' => 'fa fa-envelope-o'
			    );
			    
			    
			}
			
		} else {
		    $menuItems[] = array('url' => '$site_root$index.php?login&phase=notLogged',
		        'text' => 'Se connecter',
		        'classicon' => 'fa fa-user'
		    );
		}
		
		foreach ($menuItems as $mit){
		    $r .= '<li><a class="sidebar__nav__link" href="'.$mit['url'].'"><i class="'.$mit['classicon'].'"></i>
                        <span class="sidebar__nav__text">' .$mit['text']. '</span></a>' . "</li>\r\n";
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