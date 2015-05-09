<div class="jumbotron smaller">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="slideDown">Register Page</h1>
            </div>
        </div>
    </div>

</div>

<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4 contentbox">

            <!-- show registration form, but only if we didn't submit already -->
             <p>        
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
                </p>
            <?php if (!$registration->registration_successful && !$registration->verification_successful) { ?>
            <form method="post" action="register.php" name="registerform">
                <a class="brand animate" href="<?php echo $domain; ?>"><?php echo $brand; ?></a>
                <label for="user_name"></label>
                <input id="user_name" type="text" pattern="[a-zA-Z0-9]{2,64}" name="user_name" required placeholder="Enter your username"/>

                <label for="user_email"></label>
                <input id="user_email" type="email" name="user_email" required placeholder="Enter your email"/>

                <label for="user_password_new"></label>
                <input id="user_password_new" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" placeholder="Enter your password"/>

                <label for="user_password_repeat"></label>
                <input id="user_password_repeat" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" placeholder="Repeat your password"/>

                <img src="tools/showCaptcha.php" class="captcha" alt="captcha" />

                <label></label>
                <input type="text" name="captcha" required placeholder="Fill out the captcha above"/>

                <input type="submit" class="btn btn-primary" name="register" value="Register" />
            </form>
            <?php } ?>

                <a href="login<?php echo $dotPHP; ?>">Back to account login</a>

        </div>
    </div>
</div>
