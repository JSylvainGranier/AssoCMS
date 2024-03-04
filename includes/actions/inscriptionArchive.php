<?php 

if(!Roles::isSuperAdmin()){
	throw new RoleException("Pas d'accès cette section si l'on n'est pas administrateur.");
}

if(array_key_exists("confirm", $ARGS)){
    $arr = Persistant::getDataFromQuery("update inscription set archive = true where archive = false;");
    $arr = Persistant::getDataFromQuery("update remise_en_banque set archive = true where archive = false;");
    $arr = Persistant::getDataFromQuery("update produit set archive = true where archive = false;");
    $arr = Persistant::getDataFromQuery("update reglement set archive = true where archive = false;");

    $page->appendNotification ( "Archivage effectué. Bonne nouvelle année !" );

    $ACTIONS [] = array (
        "superAdminMenu"
    );
} else {
    $page->appendBody("<p>Vous allez archiver tous les éléments qui permettent de gérer les adhésions des membres de l'association.</p>");
    $page->appendBody("<p>Vous ne devriez avoir à faire ça qu'en préparation d'une nouvelle année, entre juin et la mi-aout.</p>");

    $page->appendBody("<p><a class='adminLink' href='index.php?inscriptionArchive&confirm'>Je suis sûr(e) de vouloir tout envoyer aux oubliettes</a></p>");
}



?>