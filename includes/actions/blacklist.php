<?php 

if(!Roles::isSuperAdmin()){
	throw new RoleException("Pas d'accès cette section si l'on n'est pas administrateur.");
}

blackListIp($ARGS["blacklist"]);


?>