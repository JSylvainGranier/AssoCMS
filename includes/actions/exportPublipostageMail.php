<?php
require 'includes/libs/pdf/html2pdf.class.php';

$page->setStandardOuputDisabled ( true );

try {
	
	$publipostage = new Publipostage ( $ARGS ["id"] + 0 );
	$pubDestList = new PublipostageDestinataire ();
	$pubDestList = $pubDestList->getAllForPublipostage ( $publipostage->getPrimaryKey () );
	
	$headerContent = Param::getValue ( PKeys::$PUBLICATION_HEADER );
	
	$html2pdf = new HTML2PDF ( 'P', 'A4', 'fr', true, 'UTF-8', array (
			10,
			15,
			10,
			15 
	) );
	
	$message = $publipostage->message;
	
	$message = preg_replace ( '#<a.*?>(.*?)</a>#i', '\1', $message );
	
	if (sizeof ( $pubDestList ) == 0) {
		$emptyDesti = new PublipostageDestinataire ();
		$pers = new Personne ();
		$pers->wantPaperRecap = true;
		$emptyDesti->destinataire = $pers;
		$pubDestList [] = $emptyDesti;
	}
	
	foreach ( $pubDestList as $aDesti ) {
		/* @var $aDesti PublipostageDestinataire */
		if (! is_null ( $aDesti->getDestinataire () ) && ! $aDesti->getDestinataire ()->wantPaperRecap) {
			continue;
		}
		
		ob_start ();
		include 'includes/actions/exportPublipostageMailOne.php';
		$page = ob_get_clean ();
		$html2pdf->pdf->startPageGroup ();
		$html2pdf->writeHTML ( $page, isset ( $_GET ['vuehtml'] ) );
	}
	
	$html2pdf->Output ( 'publipostage-' . $publipostage->objet . '.pdf' );
} catch ( HTML2PDF_exception $e ) {
	echo $e;
	exit ();
}
	
	
	
	



