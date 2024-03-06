<?php

// where to get files from
define ('ENTRY_FIELD', 'filepond');

$DIR = __DIR__;

// where to write files to
define ('TRANSFER_DIR', $DIR . '/tmp');
define ('UPLOAD_DIR', $DIR . '/uploads');
define ('VARIANTS_DIR', $DIR . '/variants');



// name to use for the file metadata object
define ('METADATA_FILENAME', '.metadata');

function ALLOWED_FILE_FORMATS(){
    return array(
        // images
        'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/webp',
    
        // video
        'video/mpeg', 'video/mp4', 'video/x-msvideo', 'video/webm', 'video/ogg',
    
        // audio
        'audio/mpeg', 'audio/ogg', 'audio/mpeg', 'audio/webm',
    
        // docs
        'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.oasis.opendocument.spreadsheet','application/vnd.oasis.opendocument.text',
        'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    );
}



// this automatically creates the upload and transfer directories, if they're not there already
if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755);
if (!is_dir(TRANSFER_DIR)) mkdir(TRANSFER_DIR, 0755);

// this is optional and only needed if you're doing server side image transforms, if images are transformed on the clients, this can stay commented
// require_once('config_doka.php');

