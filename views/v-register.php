<style type="text/css">
    body {
        background: /* top, transparent black */ linear-gradient( rgba(8, 8, 8, 0.25), rgba(0, 0, 0, 0.15) ), /* bottom, image */ url(<?php echo $domain; ?>assets/img/bg.jpg) no-repeat center center;
        background-size: cover;
        background-position: top center;
        padding-top: 200px;
    }
</style>

<script>

    $("#wizard").steps({
        bodyTag: "fieldset",
        onFinished: function (event, currentIndex)
        {
            // Submission code
            $(this).submit();
        }
    });
</script>

<link rel="stylesheet" type="text/css"  href="<?php echo $domain; ?>assets/css/steps.css">

<script type="text/javascript" src="<?php echo $domain; ?>assets/js/jquery.steps.js"></script>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4 contentbox">
            <!-- show registration form, but only if we didn't submit already -->

            <?php
            // show potential errors / feedback (from registration object)
            if (isset($registration)) {
                if ($registration->errors) {
                    echo '<div class="alert alert-danger" role="alert" alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
                    foreach ($registration->errors as $error) {
                        echo $error;
                        echo '</div>';
                    }
                }
                if ($registration->messages) {
                    echo '<div class="alert alert-info" role="alert" alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
                    foreach ($registration->messages as $message) {
                        echo $message;
                        echo '</div>';
                    }
                }
            }
            ?>
            <a class="brand animate" href="<?php echo $domain; ?>" style="display: inline; margin-top: -10px;"><img src="<?php echo $domain; ?>assets/img/logo.png" style="max-width:100%"></a>
            <?php if (!$registration->registration_successful && !$registration->verification_successful) { ?>
                <form id="wizard" method="post" action="<?php $siteFunctions->url("register"); ?>" name="registerform">
                    <!-- STEP 1 -->
                    <h1>Your Details</h1>
                    <fieldset>
                        <legend>Your Details</legend>
                        <input id="user_name" type="text" pattern="[a-zA-Z0-9]{2,64}" name="user_name" required placeholder="Enter your username" value="<?php if (isset($_POST['user_name'])) {echo $_POST['user_name']; } ?>"/>
                        <input id="user_email" type="email" name="user_email" required placeholder="Enter your email" value="<?php if (isset($_POST['user_email'])) {echo $_POST['user_email']; } ?>"/>
                    </fieldset>

                    <!-- STEP 2 -->
                    <h1>Your Password</h1>
                    <fieldset>
                        <legend>Your Password</legend>
                        <input id="user_password_new" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" placeholder="Enter your password"/>
                        <input id="user_password_repeat" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" placeholder="Repeat your password"/>
                    </fieldset>

                    <!-- STEP 3 -->
                    <h1>Captcha Verification</h1>
                    <fieldset>
                        <legend>Captcha Verification</legend>
                        <img src="lib/showCaptcha.php" class="captcha" alt="captcha" />
                        <input type="text" name="captcha" required placeholder="Fill out the captcha"/>
                    </fieldset>
                </form>

            <?php } ?>

            <a class="animate" href="<?php echo $domain; ?>login<?php echo $dotPHP; ?>">Back to account login</a>

        </div>
    </div>
</div>