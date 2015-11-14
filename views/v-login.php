<style type="text/css">
    body {
        background: /* top, transparent black */ linear-gradient( rgba(8, 8, 8, 0.25), rgba(0, 0, 0, 0.15) ), /* bottom, image */ url(<?php echo $domain; ?>assets/img/bg.jpg) no-repeat center center;
        background-size: cover;
        padding-top: 200px;
    }
</style>

<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4 contentbox">

            <form method="post" action="login<?php echo $dotPHP; ?>" name="loginform">

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
                <a class="brand animate" href="<?php echo $domain; ?>" style="padding: auto 20px;"><img src="<?php echo $domain; ?>assets/img/logo.png" style="max-width:100%"></a>
                <input id="user_name" type="text" name="user_name" required placeholder="Your Username" />
                <input id="user_password" type="password" name="user_password" autocomplete="off" required placeholder="Your Password" />

                <input type="checkbox" id="user_rememberme" name="user_rememberme" value="1" />
                <label class="animate" for="user_rememberme">Keep me signed in</label>
                <input type="submit" class="box" name="login" value="Log In" />
            </form>
            <a class="animate" href="<?php echo $siteFunctions->url('register'); ?>">Register</a>
            <a class="animate" href="password_reset.php">I forgot my password</a>

        </div>
    </div>
</div>