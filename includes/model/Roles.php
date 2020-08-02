<?php
class Roles {
	public static $SUPER_ADMIN = 500;
	public static $PRESIDENT = 450;
	public static $COMPTABLE = 300;
	public static $GESTIONNAIRE_GLOBAL = 200;
	public static $GESTIONNAIRE_CATEGORIE = 100;
	
	/**
	 * Retourne la description d'un rôle.
	 *
	 * @param unknown_type $role        	
	 * @throws Exception
	 * @return string
	 */
	public static function getRoleLibelle($role) {
		switch ($role) {
			case Roles::$SUPER_ADMIN :
				return "Administrateur du site";
			case Roles::$PRESIDENT :
				return "Président de l'association";
			case Roles::$COMPTABLE :
				return "Comptable";
			case Roles::$GESTIONNAIRE_GLOBAL :
				return "Gestionnaire global";
			case Roles::$GESTIONNAIRE_CATEGORIE :
				return "Gestionnaire de catégorie";
			default :
				throw new Exception ( "Le rôle '" . $role . "' n'existe pas." );
		}
	}
	
	/**
	 * Retourne un tableau associatif où le nombre
	 * du rôle donne la description du rôle.
	 */
	public static function getRolesArray() {
		$ret = array ();
		
		$ret [Roles::$SUPER_ADMIN] = Roles::getRoleLibelle ( Roles::$SUPER_ADMIN );
		$ret [Roles::$PRESIDENT] = Roles::getRoleLibelle ( Roles::$PRESIDENT );
		$ret [Roles::$COMPTABLE] = Roles::getRoleLibelle ( Roles::$COMPTABLE );
		$ret [Roles::$GESTIONNAIRE_GLOBAL] = Roles::getRoleLibelle ( Roles::$GESTIONNAIRE_GLOBAL );
		$ret [Roles::$GESTIONNAIRE_CATEGORIE] = Roles::getRoleLibelle ( Roles::$GESTIONNAIRE_CATEGORIE );
		
		return $ret;
	}
	public static function isMembre() {
		if (session_id () == '' || is_null ( $_SESSION ))
			return false;
		if(!$_SESSION ["allowedToConnect"] ){
		    return false;	    
		}
			
		return array_key_exists ( "userRoles", $_SESSION );
	}
	
	public static function isInvite() {
	    if (session_id () == '' || is_null ( $_SESSION ))
	        return false;
        if($_SESSION ["allowedToConnect"] === false){
            return true;
        }
        
        return false;
	       
	}
	
	private static function hasRole($role) {
		if (session_id () == '' || is_null ( $_SESSION ) || ! array_key_exists ( "userRoles", $_SESSION ))
			return false;
		$userRolesArray = $_SESSION ["userRoles"];
		return in_array ( $role, $userRolesArray );
	}
	public static function isSuperAdmin() {
		return Roles::hasRole ( Roles::$SUPER_ADMIN );
	}
	public static function isPresitent() {
		return Roles::hasRole ( Roles::$PRESIDENT ) || Roles::isSuperAdmin ();
	}
	public static function isComptable() {
		return Roles::hasRole ( Roles::$COMPTABLE );
	}
	public static function isGestionnaireGlobal() {
		return Roles::hasRole ( Roles::$GESTIONNAIRE_GLOBAL ) || Roles::isComptable () || Roles::isSuperAdmin ();
	}
	public static function isGestionnaireCategorie() {
		return Roles::hasRole ( Roles::$GESTIONNAIRE_CATEGORIE ) || Roles::isGestionnaireGlobal ();
	}
	public static function isGestionnaireOfCategorie($categorie) {
		if (Roles::isGestionnaireGlobal ())
			return true;
		
		if (! Roles::hasRole ( Roles::$GESTIONNAIRE_CATEGORIE ))
			return false;
		
		if (is_int ( $categorie )) {
			$cat = new Categorie ( $categorie );
		} else {
			$cat = $categorie;
		}
		
		return $cat->getPersonneGestionnaire ()->getPrimaryKey () == thisUserId();
	}
	public static function canAdministratePersonne() {
		return Roles::isGestionnaireGlobal () || Roles::isComptable () || Roles::isSuperAdmin ();
	}
	public static function canManageCategories() {
		return Roles::isSuperAdmin ();
	}
	public static function canPublishPage() {
		return Roles::isGestionnaireCategorie ();
	}
}
class RoleException extends Exception {
}

?>