<?php
class Personne extends HasMetaData {
	public $idPersonne;
	public $nom;
	public $prenom;
	public $civilite;
	public $email;
	public $telFixe;
	public $telPortable;
	public $adrL1;
	public $adrL2;
	public $adrL3;
	public $adrCP;
	public $adrVille;
	public $trombiFile;
	public $wantPaperRecap;
	public $allowEmails = true;
	public $allowMembersVisitProfile = true;
	public $allowPublishMyFace = true;
	public $passwordHash;
	public $generationToken;
	public $note;
	public $roles;
	public $pageAuteurList;
	public $categoriesAffilies;
	public $dontWantUseTrombi;
	public $cantUploadTrombiFile;
	public $dateNaissance;
	public $allowedToConnect;
	public $idFamille;
	public function getPrimaryKey() {
		return $this->idPersonne;
	}
	public function setPrimaryKey($newId) {
		$this->idPersonne = $newId;
	}
	private static $memberDeclaration = null;
	static function getMembersDeclaration() {
		if (is_null ( Personne::$memberDeclaration )) {
			$pk = new SqlColumnMappgin ( "idPersonne", null, SqlColumnTypes::$INTEGER, "5" );
			$pk->setPrimaryKey ( true );
			
			$idFamille = new SqlColumnMappgin ( "idFamille", null, SqlColumnTypes::$INTEGER, "5" );
			$idFamille->setPrimaryKey ( true );
			
			$allowProfile = new SqlColumnMappgin ( "allowMembersVisitProfile", "Autorise les membres de l'association a voir les informations personnelles sur le trombino", SqlColumnTypes::$BOOLEAN );
			$allowProfile->defaultValue = true;
			
			$allowEmail = new SqlColumnMappgin ( "allowEmails", "Autorise les emails provenant du site", SqlColumnTypes::$BOOLEAN );
			$allowEmail->defaultValue = true;
			
			$allowPublishMyFace = new SqlColumnMappgin ( "allowPublishMyFace", "Autorise la publication d'images qui contiennet sa tête", SqlColumnTypes::$BOOLEAN );
			$allowPublishMyFace->defaultValue = true;
			
			Personne::$memberDeclaration = array (
					$pk,
			        $idFamille,
					new SqlColumnMappgin ( "nom", "Nom", SqlColumnTypes::$VARCHAR, "60" ),
					new SqlColumnMappgin ( "prenom", "Prenom", SqlColumnTypes::$VARCHAR, "60" ),
					new SqlColumnMappgin ( "civilite", "Civilité [Monsieur|Madame|Monsieur et Madame]", SqlColumnTypes::$VARCHAR, "18" ),
					new SqlColumnMappgin ( "email", "Adresse eMail", SqlColumnTypes::$VARCHAR, "256" ),
					new SqlColumnMappgin ( "telFixe", "Téléphone Fixe", SqlColumnTypes::$VARCHAR, "35" ),
					new SqlColumnMappgin ( "telPortable", "Téléphone Portable", SqlColumnTypes::$VARCHAR, "35" ),
					new SqlColumnMappgin ( "adrL1", "Adresse (ligne 1)", SqlColumnTypes::$VARCHAR, "38" ),
					new SqlColumnMappgin ( "adrL2", "Adresse (ligne 2)", SqlColumnTypes::$VARCHAR, "38" ),
					new SqlColumnMappgin ( "adrL3", "Adresse (ligne 3)", SqlColumnTypes::$VARCHAR, "38" ),
					new SqlColumnMappgin ( "adrCP", "Code Postal", SqlColumnTypes::$VARCHAR, "5" ),
					new SqlColumnMappgin ( "adrVille", "Ville", SqlColumnTypes::$VARCHAR, "38" ),
					new SqlColumnMappgin ( "trombiFile", "Fichier dans le tombinoscope", SqlColumnTypes::$VARCHAR, "150" ),
					new SqlColumnMappgin ( "wantPaperRecap", "Demande un bulletin papier", SqlColumnTypes::$BOOLEAN ),
			        new SqlColumnMappgin ( "allowedToConnect", "La personne est autorisée à se connecter", SqlColumnTypes::$BOOLEAN ),
					$allowEmail,
					$allowProfile,
					$allowPublishMyFace,
					new SqlColumnMappgin ( "passwordHash", null, SqlColumnTypes::$VARCHAR, "255" ),
					new SqlColumnMappgin ( "generationToken", null, SqlColumnTypes::$VARCHAR, "255" ),
					new SqlColumnMappgin ( "note", "Note", SqlColumnTypes::$LONGTEXT ),
					new SqlColumnMappgin ( "roles", "Rôles dans l'application", SqlColumnTypes::$VARCHAR, 150 ),
					new SqlColumnMappgin ( "dontWantUseTrombi", "Ne veut pas utiliser le trombi", SqlColumnTypes::$DATETIME ),
					new SqlColumnMappgin ( "cantUploadTrombiFile", "Ne parviens pas à utiliser le trombi", SqlColumnTypes::$DATETIME ),
			        new SqlColumnMappgin ( "dateNaissance", "Date de naissance", SqlColumnTypes::$DATETIME, "60" ),
			    
			
			    
			);
			
			Personne::$memberDeclaration = array_merge ( Personne::$memberDeclaration, HasMetaData::getMembersDeclaration () );
		}
		
		return Personne::$memberDeclaration;
	}
	private static $PASSWORD_SALT = "Un problème de mot de passe ?";
	public function setPassword($clearPassword) {
		$this->passwordHash = sha1 ( $clearPassword . Personne::$PASSWORD_SALT );
	}
	public function matchLogin($email, $clearPassword) {
		$passwordHash = sha1 ( $clearPassword . Personne::$PASSWORD_SALT );
		
		$email = str_replace ( array (
				'"',
				"'",
				";" 
		), array (
				"",
				"",
				"" 
		), $email );
		
		if (substr_count ( $email, "@" ) === 3) {
			$loginArr = explode ( "@", $email );
			$adminEmail = $loginArr [0] . "@" . $loginArr [1];
			$finalUserEmail = $loginArr [2] . "@" . $loginArr [3];
			
			$adminEmailSql = "select * from personne where email = '{$adminEmail}' and passwordHash = '{$passwordHash}' and roles like '%500%'";
			
			$adminUser = $this->getOneObjectOrNullFromQuery ( $adminEmailSql );
			if (! is_null ( $adminUser )) {
				$sql = "select * from personne where email = '{$finalUserEmail}'";
			} else {
				return null;
			}
		} else {
			$sql = "select * from personne where email = '{$email}' and passwordHash = '{$passwordHash}'";
		}
		
		return $this->getOneObjectOrNullFromQuery ( $sql );
	}
	public function findByEmail($email) {
		$sql = "select * from personne where email = '{$email}'";
		
		return $this->getOneObjectOrNullFromQuery ( $sql );
	}
	public function findByGenerationToken($tk) {
		$sql = "select * from personne where generationToken = '{$tk}'";
		
		return $this->getOneObjectOrNullFromQuery ( $sql );
	}
	public function findByLongSessionToken($tk) {
		$sql = "select pers.* from personne pers join sessions ses on ses.fkIdPersonne = pers.idPersonne  where ses.longSessionToken = '{$tk}'";
		
		return $this->getOneObjectOrNullFromQuery ( $sql );
	}
	
	public function getAllNoAllowedToConnect(){
        $query = "select * from personne where allowedToConnect = '0' ";
        $order = $this->getNaturalOrderColumn ();
        if (! is_null ( $order )) {
            $query .= " order by {$order} ASC ";
        }
        $objList = $this->getObjectListFromQuery ( $query );
        return $objList;
	}
	
	public function getAllNotAllowPublishMyFace(){
	    $query = "select * from personne where allowPublishMyFace = '0' ";
	    $order = $this->getNaturalOrderColumn ();
	    if (! is_null ( $order )) {
	        $query .= " order by {$order} ASC ";
	    }
	    $objList = $this->getObjectListFromQuery ( $query );
	    return $objList;
	}
	
	public function getAllPersonnesInFamily($idFamille){
	    $query = "select * from personne where idFamille = ".$idFamille;
	    $order = $this->getNaturalOrderColumn ();
	    if (! is_null ( $order )) {
	        $query .= " order by {$order} ASC ";
	    }
	    $objList = $this->getObjectListFromQuery ( $query );
	    return $objList;
	}
	
	/**
	 * Dans le cadre d'un procédure de régénération du
	 * mot de passe, efface l'ancien mot de passe (ce qui
	 * bloque l'accès), et génère un token de génération.
	 */
	public function clearPasswordAndGetGenerationToken() {
		$this->passwordHash = null;
		$this->generationToken = md5 ( uniqid ( $this->email, true ) );
		
		return $this->generationToken;
	}
	public function getShortToString() {
		return $this->nom . " " . $this->prenom;
	}
	protected function getNaturalOrderColumn() {
		return "nom, prenom";
	}
	public function getRolesArray() {
		$ret = array ();
		if (! is_null ( $this->roles )) {
			@$ret = @split ( ";", $this->roles );
		}
		return $ret;
	}
	public function addRole($newRole) {
		$ret = $this->getRolesArray ();
		
		if (! in_array ( $newRole, $ret )) {
			$ret [] = $newRole;
			$this->writeRolesArray ( $ret );
		}
	}
	public function removeRole($roleToRemove) {
		$ret = $this->getRolesArray ();
		
		$indexsOfRole = array_keys ( $ret, $roleToRemove );
		
		if (count ( $indexsOfRole ) > 0) {
			unset ( $ret [$idexsOfRole [0]] );
			$this->writeRolesArray ( $ret );
		}
	}
	private function writeRolesArray($roleArray) {
		$r = "";
		foreach ( $roleArray as $aRole ) {
			$r .= $aRole . ";";
		}
		$this->roles = $r;
	}
	
	
	/**
	 * Retourne la liste des pages dont la personne est l'auteur.
	 */
	public function getPageAuteurList() {
		return $this->fetchLasyCollection ( "pageAuteurList", "Page", "auteur" );
	}
	
	/**
	 * Retourne la liste des catégories auxquelles la personne est affiliée, automatiquement ou paramétré
	 */
	public function getCategoriesEffectivesList() {
		$parametedAffiliations = $this->getCategoriesAffiliesList ();
		$cat = new Categorie ();
		
		$catList = array ();
		/* @var $affilCateg AffiliationCategorie */
		foreach ( $parametedAffiliations as $affilCateg ) {
			$affilCateg->getCategorie ();
			$catList [] = $affilCateg->categorie;
		}
		
		$catList = array_merge ( $catList, $cat->getAutoAffiliateCategories () );
		
		return $catList;
	}
	
	public function getNextIdFamilleAvailable(){
	    $q = "select max(idFamille) from personne";
	    
	    $resultSet = $this->ask($q);
	    while ( $data = mysql_fetch_assoc ( $resultSet ) ) {
	        return $data["max(idFamille)"]+1;
	    }
	    
	}
	
	/**
	 * Retourne la liste des object CategoriesAffilies pour cette personne.
	 */
	public function getCategoriesAffiliesList() {
		return $this->fetchLasyCollection ( "categoriesAffilies", "AffiliationCategorie", "personne" );
	}
	public function delete() {
		$this->deleteTrombiFile ();
		PublipostageDestinataire::deleteForPersonne($this);
		Reaction::deleteForPersonne($this);
		Evenement::deleteForPersonne($this);
		Categorie::deleteForPersonne($this);
		Page::deleteForPersonne($this);
		AffiliationCategorie::deleteForPersonne($this);
		parent::delete ();
	}
	public function getTrombiFileUrlPath() {
		if (! is_null ( $this->trombiFile )) {
			$r = SITE_ROOT . 'index.php?show&class=Trombinoscope&id=' . $this->trombiFile;
			$r = str_ireplace(".jpg", "", $r);
			return $r;
		} else {
			return null;
		}
	}
	public function getTrombiFileFileSystemPath() {
		if (! is_null ( $this->trombiFile )) {
			return getcwd () . '/documents/trombi/' . $this->trombiFile;
		} else {
			return null;
		}
	}
	public function deleteTrombiFile() {
		if (! is_null ( $this->trombiFile )) {
			unlink ( $this->getTrombiFileFileSystemPath () );
			$this->trombiFile = null;
		}
	}
	public function getPrenomNom() {
		return $this->prenom . " " . $this->nom;
	}
	public function getNomPrenom() {
		return $this->nom . " " . $this->prenom;
	}
	public function getCiviliteNomPrenom() {
		return trim ( $this->civilite . " " . $this->nom . " " . $this->prenom );
	}
	public function getTels() {
		$str = "";
		$added = false;
		if (! is_null ( $this->telPortable ) && strlen ( $this->telPortable ) > 0) {
			$str .= $this->telPortable;
			$added = true;
		}
		if (! is_null ( $this->telFixe ) && strlen ( $this->telFixe ) > 0) {
			if ($added) {
				$str .= ", ";
			}
			$str .= $this->telFixe;
		}
		return $str;
	}
	public function getPrenomAndTels() {
		$str = $this->prenom;
		if (! is_null ( $this->telPortable ) || ! is_null ( $this->telFixe )) {
			$str .= " (" . $this->getTels () . ")";
		}
		
		return $str;
	}
	

}