<div class="jumbotron smaller">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="slideDown">Edit Your Account</h1>
            </div>
        </div>
    </div>

</div>

<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3 contentbox">


            <h5><?php echo $_SESSION['user_name']; ?> <?php echo WORDING_EDIT_YOUR_CREDENTIALS; ?></h5>

            <form method="post" action="edit.php" name="user_edit_form_name">
                
                <input placeholder="<?php echo WORDING_NEW_USERNAME; ?>" id="user_name" type="text" name="user_name" pattern="[a-zA-Z0-9]{2,64}" required /> (<?php echo WORDING_CURRENTLY; ?>: <?php echo $_SESSION['user_name']; ?>)
                <br><br> <input type="submit" class="btn btn-primary" name="user_edit_submit_name" value="<?php echo WORDING_CHANGE_USERNAME; ?>" />
            </form>

        </div>
        <div class="col-md-6 col-md-offset-3 contentbox">

            <form method="post" action="edit.php" name="user_edit_form_email">
                
                <input placeholder="<?php echo WORDING_NEW_EMAIL; ?>" id="user_email" type="email" name="user_email" required /> (<?php echo WORDING_CURRENTLY; ?>: <?php echo $_SESSION['user_email']; ?>)
                <br><br> <input type="submit" class="btn btn-primary" name="user_edit_submit_email" value="<?php echo WORDING_CHANGE_EMAIL; ?>" />
            </form>

        </div>
        <div class="col-md-6 col-md-offset-3 contentbox">

            <form method="post" action="edit.php" name="user_edit_form_password">
                
                <input placeholder="<?php echo WORDING_OLD_PASSWORD; ?>" id="user_password_old" type="password" name="user_password_old" autocomplete="off" />

                
                <input placeholder="<?php echo WORDING_NEW_PASSWORD; ?>" id="user_password_new" type="password" name="user_password_new" autocomplete="off" />

                
                <input placeholder="<?php echo WORDING_NEW_PASSWORD_REPEAT; ?>" id="user_password_repeat" type="password" name="user_password_repeat" autocomplete="off" />

                <br><br> <input type="submit" class="btn btn-primary" name="user_edit_submit_password" value="<?php echo WORDING_CHANGE_PASSWORD; ?>" />
            </form><hr/>

            <a href="account.php"><?php echo WORDING_BACK_TO_LOGIN; ?></a>
       </div>
    </div>
</div>
        
<?php
// show potential errors / feedback (from login object)
if (isset($login)) {
    if ($login->errors) {
        foreach ($login->errors as $error) {
            echo $error;
        }
    }
    if ($login->messages) {
        foreach ($login->messages as $message) {
            echo $message;
        }
    }
}
?>

<?php
// show potential errors / feedback (from registration object)
if (isset($registration)) {
    if ($registration->errors) {
        foreach ($registration->errors as $error) {
            echo $error;
        }
    }
    if ($registration->messages) {
        foreach ($registration->messages as $message) {
            echo $message;
        }
    }
}
?>