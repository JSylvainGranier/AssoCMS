<?php

$results = array();

if($ARGS["action"] == "request"){
    $k = Param::getValue ( PKeys::$SNB_API_KEY );
    //$_SESSION['mailSessionSpool'];
    //$k = "Ho";
    $e = $_SESSION['mailSessionSpool'];
    //$e = "k";

    if(true || is_null($e) ) {
        $mDao = new Mail();
        $spoolSize = Param::getValue ( PKeys::$MAIL_SPOOL_SIZE );
        $oldMails = $mDao->getNextSpoolContent($spoolSize, 3, 8);
        if(!is_null($oldMails) && count($oldMails) > 0){
            $e = $oldMails;
        
            
        }
        
    } else {
        unset($_SESSION['mailSessionSpool']);
    }

    if( is_null($e) ) {
        $k = null;
    } else {
        $mDao2 = new Mail();
        foreach($e as $sMail){
            $mFromDb = $mDao2->findById($sMail->idMail); 

            $mFromDb->nbTentatives = 1;
            $mFromDb->sent = true;
            $mFromDb->save();
        }
    }

    if($_SERVER['SERVER_NAME'] != 'visa30.free.fr'){
        $results = array("emails" => $e, "key" => $k, "redirect" => "jsylvain.granier@gmail.com");
    } else {
        $results = array("emails" => $e, "key" => $k);

    }
    
    
    
} else if(false /* $ARGS["action"] == "confirm" */){
   

    $idMails = explode(',',$ARGS["idMails"]);

     //var_dump($idMails);

     $mail = new Mail();

     foreach($idMails as $val){
         $idm = intval($val);

        if($idm < 1){
            continue;
        }

        

        $sMail = $mail->findById($idm);
        
        if( is_null($sMail)){
            continue;
        }
        
    
        $sMail->sent = 1;
        $sMail->save();
     }

     $results["confirm"] = true;
     unset($_SESSION["mailSessionSpool"]);
}

$page->setStandardOuputDisabled ( true );
if (isPhpUp ()) {
    echo json_encode ( $results );
} else {
    echo json_encode ( $results, JSON_UNESCAPED_UNICODE );
}