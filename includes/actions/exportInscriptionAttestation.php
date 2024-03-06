<?php
require 'includes/libs/pdf/html2pdf.class.php';

$page->setStandardOuputDisabled ( true );

try {
    
    $inscription = new Inscription($ARGS ["idInscription"] + 0);

    
    $headerContent = Param::getValue ( PKeys::$PUBLICATION_HEADER );
    
    $html2pdf = new HTML2PDF ( 'P', 'A4', 'fr', true, 'UTF-8', array (
        10,
        15,
        10,
        15
    ) );
    
    $message = $inscription->getConfirmationInscriptionHtml();
    
    $message = preg_replace ( '#<a.*?>(.*?)</a>#i', '\1', $message );
    
    ob_start ();
    include 'includes/actions/exportInscriptionAttestationPage.php';
    $page = ob_get_clean ();
    $html2pdf->pdf->startPageGroup ();
    $html2pdf->writeHTML ($page, isset ( $_GET ['vuehtml'] ) );
    
    $html2pdf->Output ( 'Inscription' . SITE_TITLE . '.pdf' );
} catch ( HTML2PDF_exception $e ) {
    echo $e;
    exit ();
}








