<?php
$page->appendBody ( "<p><a href='index.php?mailSpool&action=run&mode=gui'>Lancer le spool maintenant</a><br />" );
$page->appendBody ( "<a href='index.php?mailSpool&action=purge&scope=sent'>Purger les emails envoyés</a><br />" );
$page->appendBody ( "<a href='index.php?mailSpool&action=purge&scope=error'>Purger les emails en erreur (pas envoyés et trop de tentatives)</a><br />" );
$page->appendBody ( "<a href='index.php?mailSpool&action=purge&scope=all'>Vidanger le spool</a><br /></p>" );

include "./includes/actions/listGeneric.php";


