<?php 

$page->setTitle("Contact");

if(isset ($ARGS["message"]) && isset ($ARGS["g-recaptcha-response"]) && strlen($ARGS["g-recaptcha-response"]) > 30){
    
    $email = Param::getValue(PKeys::$CONTACT_EMAIL);
    
    $msg = "<p>Bonjour,</p>
            <p>Quelqu'un vient d'utiliser le formulaire de contact du site ".SITE_TITLE."</p>
            <p>Expéditeur : <a href:'mailto:{$ARGS["email"]}'>{$ARGS["email"]}</a> </p>
            <p style='background-color : #DFECF0; padding : 15px;'>{$ARGS["message"]}</p>    
    ";
    
    
    sendSimpleMail ( "Prise de contact sur le site de " . SITE_TITLE, $msg, $email, true );
    
    
    $page->appendNotification ( "Votre message à bien été envoyé <i class='fa fa-thumbs-o-up ' aria-hidden='true'></i> <br/> Il sera pris en charge dès que possible par un membre du bureau de l'association." );
    
    $redirection = array (
        "home"
    );
    $ACTIONS [] = $redirection;
} else {
    $page->appendBody ( file_get_contents ( "includes/html/contact.html" ) );
    
    $tuid = thisUserId();
    
    if($tuid > 0){
        $personne = new Personne($tuid);
        $page->asset("email", $personne->email);
    }
    
    $page->asset("CONTACT_MESSAGE", Param::getValue(PKeys::$CONTACT_TEXT));
    
    
}


?>