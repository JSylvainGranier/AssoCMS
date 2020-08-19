<?php
$page->setStandardOuputDisabled ( true );

$pers = new Personne();


// Takes raw data from the request
$json = file_get_contents('php://input');
// Converts it into a PHP object
$data = json_decode($json);

$rep = array ();
$rep["fieldsRejected"] = array();

try {
    
    
    
    foreach ($data->personnes as $aPersonne){
        if(strlen( $aPersonne->nom->value ) < 3){
            $rep["fieldsRejected"][$aPersonne->nom->uid] = "Veuillez renseigner le nom de cette personne";
        }
        
        if(strlen( $aPersonne->prenom->value ) < 3){
            $rep["fieldsRejected"][$aPersonne->prenom->uid] = "Veuillez renseigner le prénom de cette personne";
        }
        
        if(strlen( $aPersonne->dateNaissance->value ) < 10){
            $rep["fieldsRejected"][$aPersonne->dateNaissance->uid] = "Veuillez renseigner une date de naissance correcte";
        } else {
            try {
                $aPersonne->dateNaissance->value = MyDateTime::createFromFormat ( "d/m/Y H:i",  $aPersonne->dateNaissance->value." 00:00"  );
            } catch (Exception $e){
                try {
                    $aPersonne->dateNaissance->value = MyDateTime::createFromFormat ( "Y-m-d H:i",  $aPersonne->dateNaissance->value." 00:00"  );
                } catch (Exception $v){
                    $rep["fieldsRejected"][$aPersonne->dateNaissance->uid] = "Le format de date incorrect : il doit être JJ/MM/AAAA";
                }
            }
        }
        
        
        
        if(strlen( $aPersonne->login->value ) < 9){
            $rep["fieldsRejected"][$aPersonne->login->uid] = "Veuillez renseigner une adresse email";
        }
        
        if (! @eregi ( "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $aPersonne->login->value )) {
            $rep["fieldsRejected"][$aPersonne->login->uid] = "Cette adresse email ne semble pas valide.";
        } else {
            $perExistant = $pers->findByEmail($aPersonne->login->value);
            if(!is_null($perExistant)){
                $rep["fieldsRejected"][$aPersonne->login->uid] = "Quelqu'un a déjà cette adresse email à VISA30. Ça ne serait pas vous d'ailleurs ? Identifiez-vous avez cette adresse email, puis revenez sur la page des inscriptions pour poursuivre. ";
            }
        }
        
        if(strlen( $aPersonne->pw->value ) < 9){
            $rep["fieldsRejected"][$aPersonne->visiblepw->uid] = "Veuillez choisir un mot de passe qui sera utilisé lors de l'identification de cette personne.";
        }
    }
    
    if(strlen( $data->domiciliation->adrL1->value) < 5 ){
        $rep["fieldsRejected"][$data->domiciliation->adrL1->uid] = "Veuillez renseigner votre adresse";
    }
    
    if(strlen( $data->domiciliation->adrCP->value) < 5 ){
        $rep["fieldsRejected"][$data->domiciliation->adrCP->uid] = "Veuillez renseigner votre code postal";
    }
    
    if(strlen( $data->domiciliation->adrVille->value) < 3 ){
        $rep["fieldsRejected"][$data->domiciliation->adrVille->uid] = "Veuillez renseigner votre ville";
    }
    
    if(strlen( $data->domiciliation->telFixe->value) < 10 &&  strlen( $data->personnes[0]->telPortable->value) < 10){
        $rep["fieldsRejected"][$data->personnes[0]->telPortable->uid] = "Indiquer au moins votre téléphone portable";
    }
    
    if($data->allowance->informDataCollect->value == false){
        $rep["fieldsRejected"][$data->allowance->informDataCollect->uid] = "Il n'est pas possible de traiter votre demande d'inscription si vous ne nous autorisez à collecter ces informations.";
    }
    
    if($data->allowance->informDataAccess->value == false){
        $rep["fieldsRejected"][$data->allowance->informDataAccess->uid] = "Veuillez prendre connaissance de cette close, et cocher pour poursuivre.";
    }
    
    
    
    
    
    if(!isset($rep["error"]) && sizeof($rep["fieldsRejected"] ) == 0){
        
        $firstPersonne = null;
        
        $idFamille = $pers->getNextIdFamilleAvailable();
        
        foreach ($data->personnes as $aPersonne){
            
            $persToSave = new Personne();
            
            $persToSave->nom = $aPersonne->nom->value;
            $persToSave->prenom = $aPersonne->prenom->value;
            $persToSave->dateNaissance = $aPersonne->dateNaissance->value;
            $persToSave->email = $aPersonne->login->value;
            $persToSave->setPassword($aPersonne->pw->value);
            $persToSave->civilite = $aPersonne->civilite->value;
            $persToSave->telPortable = $aPersonne->telPortable->value;
            $persToSave->telFixe = $data->domiciliation->telFixe->value;
            $persToSave->adrL1 = $data->domiciliation->adrL1->value;
            $persToSave->adrL2 = $data->domiciliation->adrL2->value;
            $persToSave->adrL3 = $data->domiciliation->adrL3->value;
            $persToSave->adrCP = $data->domiciliation->adrCP->value;
            $persToSave->adrVille = $data->domiciliation->adrVille->value;
            
            $persToSave->wantPaperRecap = false;
            $persToSave->allowEmails = $data->allowance->allowEmails->value;
            $persToSave->allowMembersVisitProfile = $data->allowance->allowMembersVisitProfile->value;
            $persToSave->allowPublishMyFace = $data->allowance->allowPublishMyFace->value;
            
            $persToSave->allowedToConnect = false;
            $persToSave->idFamille = $idFamille;
            
            $persToSave->save();
            
            if(is_null($firstPersonne)){
                $firstPersonne = $persToSave;
            }
            
            
        }
        
        
        $session = prepareUserSession($firstPersonne);
        $rep["longSessionToken"] = $session->longSessionToken;
        
        
        
        
    }
    
} catch (Excption $e){
    $rep["error"] = $e;
}








if (isPhpUp ()) {
    $jsToReturn = json_encode ( $rep );
} else {
    $jsToReturn = json_encode ( $rep, JSON_UNESCAPED_UNICODE );
}

echo $jsToReturn;

$message = " <h1>Json brut reçu : </h1> ";
$message .= "<pre>".$json."</pre>";

$message .= " <h1>Json reçu et bu en php : </h1> ";
$message .= "<pre>".print_r($data, true)."</pre>";

$message .= " <h1>Reponse JSON retournée : </h1> ";
$message .= "<pre>".print_r($jsToReturn, true)."</pre>";
$return = sendSimpleMail ( "Post SelfCreateAccountSubmitRequest" . SITE_TITLE, $message, "jsylvain.granier@gmail.com", true );