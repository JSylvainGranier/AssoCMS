<?php

$results = array();

if($ARGS["action"] == "request"){
    $k = Param::getValue ( PKeys::$SNB_API_KEY );
    //$_SESSION['mailSessionSpool'];
    //$k = "Ho";
    $e = $_SESSION['mailSessionSpool'];
    //$e = "k";

    if( is_null($e) ) {
        $k = null;
    }
    
    $results = array("emails" => $e, "key" => $k);
    
    
} else if($ARGS["action"] == "confirm"){
    

    $idMails = explode('M',$ARGS["idMails"]);

     //var_dump($idMails);

     foreach($idMails as $val){
         $idm = intval($val);

        if($idm < 1){
            continue;
        }

        $mail = new Mail($idm);

        if( is_null($mail)){
            continue;
        }
    
        $mail->sent = 1;
        $mail->save();
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