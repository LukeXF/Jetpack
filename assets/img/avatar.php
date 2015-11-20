<?php
if(isset($_GET['pic']))
{


    // load classes
    require_once('../../lib/config.php');
    require_once('../../classes/siteFunctions.php');
    require_once('../../classes/ImageUpload.php');

    // initialize classes
    $siteFunctions = new siteFunctions();
    $imageUpload = new imageUpload();

    $pic = $imageUpload->getUsersAvatar($_GET['pic']);


    if ($pic == false) {
        $pic = "avatars_backend/logo.png";
    } else {
        $pic = 'avatars_backend/' . $pic;
    }
    // only strip slashes if magic quotes is enabled.

    //Change this to the correct path for your file on the server.

    //This will get info about the image, including the mime type.
    //The function is called getimagesize(), which is misleading
    //because it does much more than that.
    $size = getimagesize($pic);

    //Now that you know the mime type, include it in the header.
    header('Content-type: '.$size['mime']);

    //Read the image and send it directly to the output.
    readfile($pic);
}
?>