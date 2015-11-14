<?php

/**
 * Created by PhpStorm.
 * User: luke.brown
 * Date: 01/10/2015
 * Time: 12:35
 */
class ImageUpload extends siteFunctions
{
    private $upload_dir = "assets/img/avatars_backend";
    public $large_image_prefix = "resize_";
    public $thumb_image_prefix = "avatar_";

    public function displayUpload() {

        global $_SESSION;
        global $_POST;
        global $_SERVER;
        global $_FILES;
        global $_GET;

        error_reporting (E_ALL ^ E_NOTICE);
        // right now the random key (file names) are going off the session id, but timestamp can be used instead
        if (!isset($_SESSION['avatar']['random_key']) || strlen($_SESSION['avatar']['random_key'])==0){
            $_SESSION['avatar']['random_key'] = $_SESSION['user_id']; //assign the timestamp to the session variable
            $_SESSION['avatar']['user_file_ext'] = "";
        }


        // editable options
        $upload_dir = $this->upload_dir;                                 				// The directory for the images to be saved in
        $upload_path = $upload_dir."/";				                                    // The path to where the image will be saved
        $large_image_prefix = $this->large_image_prefix;                       			// The prefix name to large image
        $thumb_image_prefix = $this->thumb_image_prefix;                     			// The prefix name to the thumb image
        $large_image_name = $large_image_prefix.$_SESSION['avatar']['random_key'];      // New name of the large image (append the timestamp to the filename)
        $thumb_image_name = $thumb_image_prefix.$_SESSION['avatar']['random_key'];      // New name of the avatar image (append the timestamp to the filename)
        $max_file = "3"; 							// Maximum file size in MB
        $max_width = "500";							// Max width allowed for the large image
        $thumb_width = "100";						// Width of avatar image
        $thumb_height = "100";						// Height of avatar image




        // only one of these image types should be allowed for upload
        $allowed_image_types = array('image/pjpeg'=>"jpg",'image/jpeg'=>"jpg",'image/jpg'=>"jpg",'image/png'=>"png",'image/x-png'=>"png",'image/gif'=>"gif");
        $allowed_image_ext = array_unique($allowed_image_types); // do not change this
        $image_ext = "";	// initialise variable, do not change this.
        foreach ($allowed_image_ext as $mime_type => $ext) {
            $image_ext.= strtoupper($ext)." "; // will output JPG PNG GIF by default
        }




        // image Locations
        $large_image_location = $upload_path.$large_image_name.$_SESSION['avatar']['user_file_ext'];
        $thumb_image_location = $upload_path.$thumb_image_name.$_SESSION['avatar']['user_file_ext'];







        // create the upload directory with the right permissions if it doesn't exist
        if(!is_dir($upload_dir)){
            mkdir($upload_dir, 0777);
            chmod($upload_dir, 0777);
        }




        // check to see if any images with the same name already exist
        if (file_exists($large_image_location)){
            if(file_exists($thumb_image_location)){
                $thumb_photo_exists = "<img src=\"".$upload_path.$thumb_image_name.$_SESSION['avatar']['user_file_ext']."\" alt=\"Thumbnail Image\"/>";
            }else{
                $thumb_photo_exists = "";
            }
            $large_photo_exists = "<img src=\"".$upload_path.$large_image_name.$_SESSION['avatar']['user_file_ext']."\" alt=\"Large Image\"/>";
        } else {
            $large_photo_exists = "";
            $thumb_photo_exists = "";
        }







        // when the original image is uploaded to the server
        if (isset($_POST["upload"])) {

            // get the file information
            $userfile_tmp = $_FILES['image']['tmp_name'];
            $userfile_size = $_FILES['image']['size'];
            $userfile_type = $_FILES['image']['type'];
            $filename = basename($_FILES['image']['name']);
            $file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));

            // only process if the file is a JPG, PNG or GIF and below the allowed limit
            if((!empty($_FILES["image"])) && ($_FILES['image']['error'] == 0)) {

                foreach ($allowed_image_types as $mime_type => $ext) {
                    // loop through the specified image types and if they match the extension then break out
                    // everything is ok so go and check file size
                    if($file_ext==$ext && $userfile_type==$mime_type){
                        $error = "";
                        break;
                    }else{
                        $error = "Only <strong>" . $image_ext . "</strong> images accepted for upload<br />";
                    }
                }

                // check if the file size is above the allowed limit
                if ($userfile_size > ($max_file*1048576)) {
                    $error.= "Images must be under <b>" . $max_file . "MB</b> in size";
                }

            } else {
                $error= "Select an image for upload";
            }
            // everything is ok, so we can upload the image.
            if (strlen($error) == 0){

                if (isset($_FILES['image']['name'])){
                    // this file could now has an unknown file extension (we hope it's one of the ones set above!)
                    $large_image_location = $large_image_location.".".$file_ext;
                    $thumb_image_location = $thumb_image_location.".".$file_ext;

                    // put the file ext in the session so we know what file to look for once its uploaded
                    $_SESSION['avatar']['user_file_ext']=".".$file_ext;

                    move_uploaded_file($userfile_tmp, $large_image_location);
                    chmod($large_image_location, 0777);

                    $width = $this->getWidth($large_image_location);
                    $height = $this->getHeight($large_image_location);

                    // scale the image if it is greater than the width set above
                    if ($width > $max_width){
                        $scale = $max_width/$width;
                        $this->resizeImage($large_image_location,$width,$height,$scale);
                    } else {
                        $scale = 1;
                        $this->resizeImage($large_image_location,$width,$height,$scale);
                    }
                    // delete the avatar file so the user can create a new one
                    if (file_exists($thumb_image_location)) {
                        unlink($thumb_image_location);
                    }
                }

                // refresh the page to show the new uploaded image
                $this->callback("settings", "uploadscreen");
                exit("Refresh the page to show the new uploaded image");
            }
        }









        // when the large image is cropped by the user and the large photo exists, then create the avatar image
        if (isset($_POST["upload_avatar"]) && strlen($large_photo_exists)>0) {
            //Get the new coordinates to crop the image.
            $x1 = $_POST["x1"]; // start width of the avatar (x cord)
            $y1 = $_POST["y1"]; // star height of the avatar (y cord)
            $w = $_POST["w"]; // width of the avatar
            $h = $_POST["h"]; // height of the avatar

            //Scale the image to the thumb_width set above
            $scale = $thumb_width/$w; // usually 100 divided by new amount
            $this->resizeThumbnailImage($thumb_image_location, $large_image_location,$w,$h,$x1,$y1,$scale);
            $this->callback("settings", "uploadscreen");
            exit("view the avatar");
        }

        // if delete is present and random key (user_id) is more than 0
        if ($_GET['a']=="delete" && strlen($_GET['t'])>0){
            $this->deleteImage($upload_path.$large_image_prefix.$_GET['t'], $upload_path.$thumb_image_prefix.$_GET['t']);
        }

        // display error message if there are any
        if(strlen($error)>0){
            echo "
                <div class='alert alert-danger alert-dismissible' role='alert'>
                    <button type=''button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                    <b>Error: </b> " . $error . "
                </div>
            ";
        }





        // if thumbnail was just created
        if(strlen($large_photo_exists)>0 && strlen($thumb_photo_exists)>0){
            $this->saveLocationToDatabase($_SESSION['user_id'] . $_SESSION['avatar']['user_file_ext']);
            $this->callback("settings");







        }else{
            if(strlen($large_photo_exists)>0){
                echo '
                    <div class="col-md-12">
                        <p>Now you\'ve uploaded your avatar, click and drag on your avatar to select your finalised square avatar. You can preview it on the right hand side.</p>
                        <div align="center">
                            <img src="' . $upload_path.$large_image_name . $_SESSION['avatar']['user_file_ext'] . '" style="float: left;" id="avatar" alt="Create Thumbnail" />
                            <div style="border:1px #e5e5e5 solid; float:left; position:relative; overflow:hidden; width: ' . $thumb_width . 'px; height: ' . $thumb_height . 'px;">
                                <img src="' . $upload_path.$large_image_name . $_SESSION['avatar']['user_file_ext'] . '" style="position: relative;" alt="Thumbnail Preview" />
                            </div>
                            <br style="clear:both;"/>
                            <form name="avatar" action="' . $_SERVER["PHP_SELF"] . '?request=uploadscreen" method="post">
                                <input type="hidden" name="x1" value="" id="x1" />
                                <input type="hidden" name="y1" value="" id="y1" />
                                <input type="hidden" name="x2" value="" id="x2" />
                                <input type="hidden" name="y2" value="" id="y2" />
                                <input type="hidden" name="w" value="" id="w" />
                                <input type="hidden" name="h" value="" id="h" />
                                <div class="form-btn" style="text-align: left">
                                    <button type="submit" class="btn btn-default" name="upload_avatar" value="Save Thumbnail" id="save_thumb" >Save Avatar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                ';
            } else {
                echo '
                    <div class="col-md-12">
                        <form name="photo" enctype="multipart/form-data" action="?request=uploadscreen" method="post">
                            You can upload your own avatar as long as the image is under ' . $max_file . 'mb, once submitted you will have the ability to crop the image before saving.
                            <input type="file" name="image" size="30" />
                            <input type="text" hidden value="hidden" name="isupload" />
                            <div class="form-btn">
                                <button type="submit" class="btn btn-default" name="upload" value="Upload">Upload New Avatar</button>
                            </div>
                        </form>
                    </div>
                ';
            }
        }


        // only display the JavaScript if an image has been uploaded
        if(strlen($large_photo_exists)>0){
            $current_large_image_width = $this->getWidth($large_image_location);
            $current_large_image_height = $this->getHeight($large_image_location);
            ?>
            <script type="text/javascript">
                function preview(img, selection) {
                    var scaleX = <?php echo $thumb_width;?> / selection.width;
                    var scaleY = <?php echo $thumb_height;?> / selection.height;

                    $('#avatar + div > img').css({
                        width: Math.round(scaleX * <?php echo $current_large_image_width;?>) + 'px',
                        height: Math.round(scaleY * <?php echo $current_large_image_height;?>) + 'px',
                        marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px',
                        marginTop: '-' + Math.round(scaleY * selection.y1) + 'px'
                    });
                    $('#x1').val(selection.x1);
                    $('#y1').val(selection.y1);
                    $('#x2').val(selection.x2);
                    $('#y2').val(selection.y2);
                    $('#w').val(selection.width);
                    $('#h').val(selection.height);
                }

                $(document).ready(function () {
                    $('#save_thumb').click(function() {
                        var x1 = $('#x1').val();
                        var y1 = $('#y1').val();
                        var x2 = $('#x2').val();
                        var y2 = $('#y2').val();
                        var w = $('#w').val();
                        var h = $('#h').val();
                        if(x1=="" || y1=="" || x2=="" || y2=="" || w=="" || h==""){
                            alert("You must make a selection first");
                            return false;
                        }else{
                            return true;
                        }
                    });
                });

                $(window).load(function () {
                    $('#avatar').imgAreaSelect({ aspectRatio: '1:<?php echo $thumb_height/$thumb_width;?>', onSelectChange: preview });
                });

            </script>
            <?php
        }
    }


    // deletes both the main and avatar image
    private function deleteImage($large_image_location, $thumb_image_location){

        if (file_exists($large_image_location)) {
            unlink($large_image_location);
        }
        if (file_exists($thumb_image_location)) {
            unlink($thumb_image_location);
        }

        $this->saveLocationToDatabase("", true);

        $this->callback("settings");
        exit("Refresh the page, your avatar has been deleted");
    }

    private function resizeImage($image,$width,$height,$scale) {
        list($imagewidth, $imageheight, $imageType) = getimagesize($image);
        $imageType = image_type_to_mime_type($imageType);
        $newImageWidth = ceil($width * $scale);
        $newImageHeight = ceil($height * $scale);
        $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
        switch($imageType) {
            case "image/gif":
                $source=imagecreatefromgif($image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                $source=imagecreatefromjpeg($image);
                break;
            case "image/png":
            case "image/x-png":
                $source=imagecreatefrompng($image);
                break;
        }
        imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);

        switch($imageType) {
            case "image/gif":
                imagegif($newImage,$image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                imagejpeg($newImage,$image,90);
                break;
            case "image/png":
            case "image/x-png":
                imagepng($newImage,$image);
                break;
        }

        chmod($image, 0777);
        return $image;
    }

    private function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale){
        list($imagewidth, $imageheight, $imageType) = getimagesize($image);
        $imageType = image_type_to_mime_type($imageType);

        $newImageWidth = ceil($width * $scale);
        $newImageHeight = ceil($height * $scale);
        $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
        switch($imageType) {
            case "image/gif":
                $source=imagecreatefromgif($image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                $source=imagecreatefromjpeg($image);
                break;
            case "image/png":
            case "image/x-png":
                $source=imagecreatefrompng($image);
                break;
        }
        imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$newImageWidth,$newImageHeight,$width,$height);
        switch($imageType) {
            case "image/gif":
                imagegif($newImage,$thumb_image_name);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                imagejpeg($newImage,$thumb_image_name,90);
                break;
            case "image/png":
            case "image/x-png":
                imagepng($newImage,$thumb_image_name);
                break;
        }
        chmod($thumb_image_name, 0777);
        return $thumb_image_name;
    }

    private function getHeight($image) {
        $size = getimagesize($image);
        $height = $size[1];
        return $height;
    }

    private function getWidth($image) {
        $size = getimagesize($image);
        $width = $size[0];
        return $width;
    }

    private function saveLocationToDatabase($filename, $delete = false){
        global $_SESSION;

        if ($this->databaseConnection()) {

            // if the filename
            if ($delete == true) {
                $sql = $this->db_connection->prepare('UPDATE `users` SET `user_avatar` = null WHERE `user_id` = :user_id');
                $sql->bindValue(':user_id',      		$_SESSION['user_id']		, PDO::PARAM_INT);

                // execute the Instagram save and check response
                if ($sql->execute()) {
                    unset($_SESSION['avatar']);
                    $_SESSION['user_avatar'] = 1;

                    unset($_SESSION['user_avatar']);
                    return true;
                } else {
                    return $this->callbackMessage("ERROR: " . $sql->errorCode() . ", unable to delete avatar location from database - please contact support." , "danger");
                }

            } else {
                $sql = $this->db_connection->prepare('UPDATE `users` SET `user_avatar` = :filename WHERE `user_id` = :user_id');
                $sql->bindValue(':filename',         	$filename			        , PDO::PARAM_STR);
                $sql->bindValue(':user_id',      		$_SESSION['user_id']		, PDO::PARAM_INT);

                // execute the Instagram save and check response
                if ($sql->execute()) {
                    unset($_SESSION['avatar']);
                    $_SESSION['user_avatar'] = 1;
                    return $this->callbackMessage("Your avatar has now been set.", "success");
                } else {
                    return $this->callbackMessage("ERROR: " . $sql->errorCode() . ", please contact support." , "danger");
                }
            }


        } else {

            return $this->callbackMessage("Database connection open", "danger");

        }
    }

    public function getUsersAvatar($username) {
        // if database connection opened
        if ($this->databaseConnection()) {

            // load pages for the user
            $sql = $this->db_connection->prepare("SELECT `user_avatar` FROM `users` WHERE `user_name` = :username");
            $sql->bindValue(':username', $username , PDO::PARAM_STR);
            $sql->execute();

            // fetch all from the widget
            $sql = $sql->fetchAll();

            // $this->debug($sql);

            if ( isset($sql[0]['user_avatar']) ) {
                return $sql[0]['user_avatar'];
            } else {
                return false;
            }

        } else {

            return false;

        }
    }

    public function displayCurrentAvatar($user_name, $noimageset = false){

        global $domain;
        global $_SESSION;
        $url = $domain . "assets/img/avatar.php?pic=" . $user_name;


        // if delete is present and random key (user_id) is more than 0
        if (isset($_GET['a']) && $_GET['a']=="delete" && strlen($_GET['t'])>0){
            $this->deleteImage($this->upload_dir . "/" . $this->large_image_prefix . $_GET['t'], $this->upload_dir . "/" . $this->thumb_image_prefix . $_GET['t']);
        }

        if ($noimageset) {
            echo "
            <div class='col-md-12'>
                <p>You have yet to submit your own avatar, you can use our default one, your gravatar or upload your own. Use the drop down menu to change your appearance.</p>
             </div>";
        } else {
            echo "
            <div class='col-md-12'>
                <p>You have set your own avatar, you can either submit a new one or remove it.</p>
             </div>";
        }

        echo "
            <div class='col-md-6'>
                <div class='tile-image'>
                    <div class='row'>
                        <div class='col-md-4 col-xs-2' style='padding-right: 0;'>
                             <img src='" . $url . "' class='img-circle' style='width:100%'>
                             </div>

                        <div class='col-md-8 col-xs-10'>
                            <b>Site Avatar:</b><br>
        ";

        if ($noimageset) {
            echo "<span>You have not uploaded an image to the site, therefore a default is set.<br>
                             <a href='?request=uploadscreen'>Upload your own avatar</a></span>";
        } else {
            echo "<span>This is your avatar you have uploaded to this site.<br>
                             <a href='?a=delete&t=" . $this->getUsersAvatar($_SESSION['user_name']) . "'>remove / upload another</a></span>";
        }
        echo "

                        </div>
                    </div>
                 </div>
            </div>


            <div class='col-md-6'>
                <div class='tile-image'>
                    <div class='row'>
                        <div class='col-md-4 col-xs-2' style='padding-right: 0;'>
                             <img src='" . $this->getGravatar($_SESSION['user_email']) . "' class='img-circle' style='width:100%'>
                             </div>

                        <div class='col-md-8 col-xs-10'>
                            <b>Gravatar:</b><br>
                             <span>This is your avatar loaded from the <a href='https://en.gravatar.com/' target='_blank'>Gravatar</a> system. You can change it on their website.</span>
                        </div>
                    </div>
                </div>
            </div>
            ";
    }


}