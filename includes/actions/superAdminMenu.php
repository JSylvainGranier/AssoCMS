<?php 

if(!Roles::isSuperAdmin()){
	throw new RoleException("Pas d'accès cette section si l'on n'est pas administrateur.");
}

$page->setTitle("Menu Administration");
$page->appendBody(file_get_contents("includes/html/superAdminMenu.html"));


?>