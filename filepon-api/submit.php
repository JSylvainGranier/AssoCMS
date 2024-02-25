<?php

// Comment if you don't want to allow posts from other domains
header('Access-Control-Allow-Origin: *');

// Allow the following methods to access this file
header('Access-Control-Allow-Methods: POST');

// Load the FilePond class
require_once('FilePond.class.php');

// Load our configuration for this server
require_once('config.php');

require_once('../includes/config.php');

// Catch server exceptions and auto jump to 500 response code if caught
FilePond\catch_server_exceptions();

FilePond\route_form_post(ENTRY_FIELD, array(
    'FILE_OBJECTS' => 'handle_file_post',
    'BASE64_ENCODED_FILE_OBJECTS' => 'handle_base64_encoded_file_post',
    'TRANSFER_IDS' => 'handle_transfer_ids_post'
));



function handle_file_post($files) {
    
   
    // This is a very basic implementation of a classic PHP upload function, please properly
    // validate all submitted files before saving to disk or database, more information here
    // http://php.net/manual/en/features.file-upload.php

    $order = 0;
    
    foreach($files as $file) {
       FilePond\move_file($file, UPLOAD_DIR);

        $order++;
        /*

         $idPage = $_POST['idPage'];

        $sql = "INSERT INTO visa30.attachment
        (fkPage,
        originalFileName,
        serverFileName,
        typeMime,
        ordre,
        lastUpdateOn,
        fkLastUpdateBy,
        isPublic)
        VALUES
        ({$idPage}>,
        {originalFileName: },
        {serverFileName: },
        {typeMime: },
        {$order},
        now(),
        {fkLastUpdateBy: },
        0);";

        echo var_dump(UPLOAD_DIR);
        echo var_dump($file);
        echo var_dump($sql);


        */
      

    }

  

}

function handle_base64_encoded_file_post($files) {
   
    foreach ($files as $file) {
        // Suppress error messages, we'll assume these file objects are valid
        /* Expected format:
        {
            "id": "iuhv2cpsu",
            "name": "picture.jpg",
            "type": "image/jpeg",
            "size": 20636,
            "metadata" : {...}
            "data": "/9j/4AAQSkZJRgABAQEASABIAA..."
        }
        */
        $file = @json_decode($file);

        // Skip files that failed to decode
        if (!is_object($file)) continue;

        // write file to disk
        FilePond\write_file(
            UPLOAD_DIR, 
            base64_decode($file->data), 
            FilePond\sanitize_filename($file->name)
        );
    }

    

}

function handle_transfer_ids_post($ids) {

    
   
    foreach ($ids as $id) {
        
        // create transfer wrapper around upload
        $transfer = FilePond\get_transfer(TRANSFER_DIR, $id);
        
        // transfer not found
        if (!$transfer) continue;
        
        // move files
        $files = $transfer->getFiles(defined('TRANSFER_PROCESSOR') ? TRANSFER_PROCESSOR : null);

        if($files != null){

            $order = 0;
            
            $idPage = $_POST['idPage'];

            $uploadDir = "../documents/pages/{$idPage}";

            mkdir($uploadDir, 777, true);
            
           foreach($files as $file) {

                FilePond\move_file($file, $uploadDir);

                $order++;
                

                $fkUserId = $_POST['fkUserId'];

                $sql = "INSERT INTO attachment
                (fkPage,
                originalFileName,
                serverFileName,
                typeMime,
                ordre,
                lastUpdateOn,
                fkLastUpdateBy,
                isPublic)
                VALUES
                ({$idPage},
                '{$file['name']}',
                '{$file['name']}',
                '{$file['type']}',
                {$order},
                now(),
                {$fkUserId },
                0);";

                echo var_dump(UPLOAD_DIR);
                echo var_dump($file);
                echo var_dump($sql);

                insertInDatabase($sql);

                mignature($file['type'], $uploadDir."/".$file['name']);


            } 
        }
        

        // remove transfer directory
        FilePond\remove_transfer_directory(TRANSFER_DIR, $id);
    }

    header('Location: ../index.php?show&class=Page&idPage='.$idPage);

}

function insertInDatabase($sql){

    
    // Create connection
    $conn = new mysqli(DB_HOST, DB_LOGIN, DB_PASSWORD, DB_NAME);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
    
    
    
    if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
    
    $conn->close();

}

function mignature($typeMime, $imagePath){
    switch ($typeMime) {
        case "image/jpeg" :
            break;
        case "image/gif" :
            break;
        case "image/png" :
            break;
        default : 
            return;
    }

    $thumbnailPath = $imagePath.".thumbnail";

    list ( $width, $height ) = getimagesize ( $imagePath );
			
    $imgRatio = $height / $width;
    
    $thumbWidth = min ( $width, 280 );
    $thumbHeight = $thumbWidth * $imgRatio;
    
    $thumbImage = imagecreatetruecolor ( $thumbWidth, $thumbHeight );
    
    switch ($typeMime) {
        case "image/jpeg" :
            $source = imagecreatefromjpeg ( $imagePath );
            break;
        case "image/gif" :
            $source = imagecreatefromgif ( $imagePath );
            break;
        case "image/png" :
            $source = imagecreatefrompng ( $imagePath );
            break;
    }
    
    // Crop
    
    imagecopyresampled ( $thumbImage, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height );
    
    switch ($typeMime) {
        case "image/jpeg" :
            imagejpeg ( $thumbImage, $thumbnailPath, 80 );
            break;
        case "image/gif" :
            imagegif ( $thumbImage, $thumbnailPath );
            break;
        case "image/png" :
            imagepng ( $thumbImage, $thumbnailPath, 80 );
            break;
    }
}