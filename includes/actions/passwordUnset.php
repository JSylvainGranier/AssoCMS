<?php
if (! Roles::isSuperAdmin ()) {
	throw new RoleException ( "Vous n'êtes pas administrateur !" );
}

$sql = "select * from personne where email is not null and (passwordHash = 'a3353d5c1554bb411d9773ee585732b7f673563f' or passwordHash is null)";
$persRoot = new Personne ();
$persList = $persRoot->getObjectListFromQuery ( $sql );

$page->appendBody ( "<ul>" );

foreach ( $persList as $aPersonne ) {
	$generationTk = $aPersonne->clearPasswordAndGetGenerationToken ();
	$aPersonne->save ();
	
	$corpus = Param::getValue ( PKeys::$MAIL_RAPPEL_SET_PASSWORD, "NANDA" );
	
	$corpus = str_ireplace ( "GTK", $generationTk, $corpus );
	
	$corpus = str_ireplace ( "MMAAIILL", $aPersonne->email, $corpus );
	
	sendSimpleMail ( SITE_TITLE . " : Choisir un mot de passe ", $corpus, $aPersonne->email, true );
	
	$page->appendBody ( "<li> Un email préparé pour {$aPersonne->nom} {$aPersonne->prenom} {$aPersonne->email}" );
}

$page->appendBody ( "</ul>" );