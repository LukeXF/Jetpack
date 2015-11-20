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
        $pic = imagecreatefromjpeg('avatars_backend/logo.jpg');
        // $size = getimagesize($pic);
        header('Content-Type: image/jpeg');

        imagejpeg($pic);
        imagedestroy($pic);

    } else {
        $pic = 'avatars_backend/' . $imageUpload->thumb_image_prefix . $pic;
        $size = getimagesize($pic);
        header('Content-Type: ' . $size['mime']);
    }

    // $siteFunctions->debug($size);
    //Read the image and send it directly to the output.
    readfile($pic);
}
?>