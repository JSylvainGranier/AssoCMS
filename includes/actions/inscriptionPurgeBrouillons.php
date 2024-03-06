<?php 

if(!Roles::isSuperAdmin()){
	throw new RoleException("Pas d'accès cette section si l'on n'est pas administrateur.");
}

$iscp = new Inscription();
$iscp->clearOldBrouillons();
$iscp->clearOldDeleted();
$page->appendNotification ( "Purge des brouillons effectuée" );

$ACTIONS [] = array (
    "superAdminMenu"
);

?>