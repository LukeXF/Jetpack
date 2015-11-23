<?php

// Handles the session, login and logout
class Login extends siteFunctions
{

    // setup the predefined variables of this class
    private $user_id                        = null; // int $user_id The user's id
    private $user_name                      = ""; // string $user_name The user's name
    private $user_email                     = ""; // string $user_email The user's mail
    private $user_is_logged_in              = false; // boolean $user_is_logged_in The user's login status
    private $query_edit_user_email          = ""; // string $user_email The user's mail
    public $user_gravatar_image_url         = ""; // string $user_gravatar_image_url The user's gravatar profile pic url (or a default one)
    public $user_gravatar_image_tag         = ""; // string $user_gravatar_image_tag The user's gravatar profile pic url with <img ... /> around
    private $password_reset_link_is_valid   = false; // boolean $password_reset_link_is_valid Marker for view handling
    private $password_reset_was_successful  = false; // boolean $password_reset_was_successful Marker for view handling

    // the function "__construct()" automatically starts whenever an object of this class is created,
    // this is done with "$login = new Login();"
    public function __construct()
    {
        if (version_compare(PHP_VERSION, '5.3.7', '<')) {
            exit('Password Script Failed');
        } else if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            require_once('classes/PasswordCompatibility.php');
        }

        // create/read session
        if (empty($_SESSION)) {
            session_start();
        }

        // if user tried to log out on any page
        if (isset($_GET["logout"])) {


            // run the logout function
            $this->doLogout();


            // if user has an active session on the server
        } elseif (!empty($_SESSION['user_name']) && ($_SESSION['user_logged_in'] == 1)) {

            // PLACE FUNCTIONS HERE FOR IF THE USER IS LOGGED IN

            // run the logged in function
            $this->loginWithSessionData();

            // checking for form submit from editing screen
            // user try to change their username
            if (isset($_POST["user_edit_submit_name"])) {

                // function below uses use $_SESSION['user_id'] and $_SESSION['user_email']
                $this->editUserName($_POST['user_name']);


                // user try to change their email
            } elseif (isset($_POST["user_edit_submit_email"])) {

                // function below uses use $_SESSION['user_id'] and $_SESSION['user_email']
                $this->editUserEmail($_POST['user_email']);


                // user try to change their password
            } elseif (isset($_POST["user_edit_submit_password"])) {

                // function below uses $_SESSION['user_name'] and $_SESSION['user_id']
                $this->editUserPassword($_POST['user_password_old'], $_POST['user_password_new'], $_POST['user_password_repeat']);


                // user tries to update images or their first/last name
            } elseif (isset($_POST['user_update_appearance'])) {

                $this->editAppearance($_POST['user_first_name'], $_POST['user_last_name'], $_POST['avatar']);
            }



            // login with cookie
        } elseif (isset($_COOKIE['rememberme'])) {

            // if there is a cookie present, hmmmm cookies
            $this->loginWithCookieData();




            // if user just submitted a login form
        } elseif (isset($_POST["login"])) {

            // if they did not click the remember me button (do not remember them via cookie)
            if (!isset($_POST['user_rememberme'])) {

                // set/override the post data to null
                $_POST['user_rememberme'] = null;
            }

            // log in the user function, at least we're going to try...
            $this->loginWithPostData($_POST['user_name'], $_POST['user_password'], $_POST['user_rememberme']);


        }








        // PASSWORD RESETTING

        // checking if user requested a password reset mail
        if (isset($_POST["request_password_reset"]) && isset($_POST['user_name'])) {

            // this function will generate a password reset token for the user if they're dumb enough to forget
            $this->setPasswordResetDatabaseTokenAndSendMail($_POST['user_name']);


        } elseif (isset($_GET["user_name"]) && isset($_GET["verification_code"])) {

            // checks if the verification string in the account verification mail is valid and matches to the user.
            // this also handles the expiring of the the verification links for the password reset
            $this->checkIfEmailVerificationCodeIsValid($_GET["user_name"], $_GET["verification_code"]);


        } elseif (isset($_POST["submit_new_password"])) {

            // so they have a valid reset link, now handle the new password with this function
            $this->editNewPassword($_POST['user_name'], $_POST['user_password_reset_hash'], $_POST['user_password_new'], $_POST['user_password_repeat']);


        }









        // get gravatar profile picture if user is logged in
        if ($this->isUserLoggedIn() == true) {

            // generate the gravatar picture of the logged in user with this function
            $this->getGravatarImageUrl($this->user_email);
        }
        // end of construct function... finally.
    }



    // Search into database for the user data of user_name specified as parameter (selects all data)
    private function getUserData($user_name)
    {
        // if database connection opened
        if ($this->databaseConnection()) {

            // database query, getting all the info of the selected user
            $query_user = $this->db_connection->prepare('SELECT * FROM users WHERE user_name = :user_name');
            // prepared statement for the username
            $query_user->bindValue(':user_name', $user_name, PDO::PARAM_STR);
            // excute username
            $query_user->execute();

            // get result row (as an object)
            return $query_user->fetchObject();

        } else {

            // if invalid username
            return false;
        }
    }



    // login with $_SESSION data. Which overrides the current session (this puts session data into session)
    private function loginWithSessionData()
    {
        $this->user_name = $_SESSION['user_name'];
        $this->user_email = $_SESSION['user_email'];

        // set logged in status to true, because we just checked for this:
        // !empty($_SESSION['user_name']) && ($_SESSION['user_logged_in'] == 1)
        // when we called this method (in the constructor)
        $this->user_is_logged_in = true;
    }




    // login with a cookie, yum! (user has been on the site before)
    private function loginWithCookieData()
    {

        // if there is a cookie
        if (isset($_COOKIE['rememberme'])) {


            // extract data from the cookie
            list ($user_id, $token, $hash) = explode(':', $_COOKIE['rememberme']);

            // check cookie hash validity, using sha256 for this
            // if the hash is changed then all signed in users will have their cookies invalidated
            // could cause issues with a mismatch
            if ($hash == hash('sha256', $user_id . ':' . $token . COOKIE_SECRET_KEY) && !empty($token)) {

                // cookie looks good, try to select corresponding user
                if ($this->databaseConnection()) {

                    // get real token from database (and all other data)
                    $sth = $this->db_connection->prepare("SELECT user_id, user_name, user_email, user_first_name, user_last_name, user_avatar, user_display_avatar FROM users WHERE user_id = :user_id
                                                      AND user_rememberme_token = :user_rememberme_token AND user_rememberme_token IS NOT NULL");
                    // prepared statement for the user id from the cookie
                    $sth->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                    // prepared token that compared the remember me token to the database
                    $sth->bindValue(':user_rememberme_token', $token, PDO::PARAM_STR);
                    // run the query and return the user's data
                    $sth->execute();


                    // get result row (as an object)
                    $result_row = $sth->fetchObject();

                    // if there is a user_id returned
                    if (isset($result_row->user_id)) {

                        // write user data into PHP SESSION [a file on the server]
                        // saving to session, finally!
                        $_SESSION['user_id'] = $result_row->user_id;
                        $_SESSION['user_name'] = $result_row->user_name;
                        $_SESSION['user_email'] = $result_row->user_email;
                        $_SESSION['user_first_name'] = $result_row->user_first_name;
                        $_SESSION['user_last_name'] = $result_row->user_last_name;
                        $_SESSION['user_logged_in'] = 1;
                        $_SESSION['user_account_type'] = $result_row->user_account_type;
                        $_SESSION['user_display_avatar'] = $result_row->user_display_avatar;
                        if ($result_row->user_avatar != null) {
                            $_SESSION['user_avatar'] = 1;
                        }

                        // declare user id, set the login status to true
                        $this->user_id = $result_row->user_id;
                        // result row, set to the username for other functions
                        $this->user_name = $result_row->user_name;
                        // result row, set to the email address for other functions
                        $this->user_email = $result_row->user_email;
                        // result row, display the logged in status
                        $this->user_is_logged_in = true;

                        // Cookie token usable only once (regenerate it with this function)
                        $this->newRememberMeCookie();

                        // return to the function that te login was successful
                        return true;
                    }
                }
            }

            // A cookie has been used but is not valid... we delete it
            $this->deleteRememberMeCookie();

            // display errors
            $this->errors[] = "Invalid cookie";
        }
        return false;
    }




    // Logs in with the data provided in $_POST, coming from the login form
    private function loginWithPostData($user_name, $user_password, $user_rememberme)
    {
        if (empty($user_name)) {
            // if the username is empty
            $this->errors[] = "Username field was empty";
        } else if (empty($user_password)) {
            // if the password is empty
            $this->errors[] = "Password field was empty";

            // if POST data (from login form) contains non-empty user_name and non-empty user_password
        } else {
            // user can login with his username or his email address.
            // if user has not typed a valid email address, we try to identify him with his user_name
            if (!filter_var($user_name, FILTER_VALIDATE_EMAIL)) {
                // database query, getting all the info of the selected user
                $result_row = $this->getUserData(trim($user_name));

                // if user has typed a valid email address, we try to identify him with his user_email
            } else if ($this->databaseConnection()) {
                // database query, getting all the info of the selected user
                $query_user = $this->db_connection->prepare('SELECT * FROM users WHERE user_email = :user_email');
                // prepared statement for the user's email address
                // login via email address
                $query_user->bindValue(':user_email', trim($user_name), PDO::PARAM_STR);
                // execute the query
                $query_user->execute();
                // get result row (as an object)
                $result_row = $query_user->fetchObject();
            }

            // if this user not exists
            if (! isset($result_row->user_id)) {

                // this error message does not return any data relating to if the account is an account
                // therefor it stops hackers and display the login error.
                $this->errors[] = "Login failed.";


            } else if (($result_row->user_failed_logins >= 3) && ($result_row->user_last_failed_login > (time() - 30))) {
                // if the password is entered in wrong three times in the last 30 seconds then tell them to slow down
                $this->errors[] = "You have entered an incorrect password 3 or more times already. Please wait 30 seconds to try again.";


                // using PHP 5.5's password_verify() function to check if the provided passwords fits to the hash of that user's password
            } else if (! password_verify($user_password, $result_row->user_password_hash)) {

                // increment the failed login counter for that user (to watch out for stupid people and brute force attacks)
                $sth = $this->db_connection->prepare('UPDATE users '
                    . 'SET user_failed_logins = user_failed_logins+1, user_last_failed_login = :user_last_failed_login '
                    . 'WHERE user_name = :user_name OR user_email = :user_name');

                // excute the failed attempts query into the database
                $sth->execute(array(':user_name' => $user_name, ':user_last_failed_login' => time()));

                // wrong password error (display after the user's failed login attempts are updated)
                $this->errors[] = "Wrong password. Try again.";


                // has the user activated their account with the verification email
            } else if ($result_row->user_active != 1) {

                // they need to stop logging in and start verifying their address
                $this->errors[] = "Your account is not activated yet. Please click on the confirm link in the mail.";


            } else {

                // now we have a successful login at this point

                // write user data into PHP SESSION [a file on your server]
                $_SESSION['user_id'] = $result_row->user_id;
                $_SESSION['user_name'] = $result_row->user_name;
                $_SESSION['user_email'] = $result_row->user_email;
                $_SESSION['user_first_name'] = $result_row->user_first_name;
                $_SESSION['user_last_name'] = $result_row->user_last_name;
                $_SESSION['user_logged_in'] = 1;
                $_SESSION['user_account_type'] = $result_row->user_account_type;
                $_SESSION['user_display_avatar'] = $result_row->user_display_avatar;
                if ($result_row->user_avatar != null) {
                    $_SESSION['user_avatar'] = 1;
                }

                // declare user id, set the login status to true (this->value is used as result_row in other functions)
                $this->user_id = $result_row->user_id;
                $this->user_name = $result_row->user_name;
                $this->user_email = $result_row->user_email;
                $this->user_is_logged_in = true;

                // reset the failed login counter for that user now that they have logged in
                $sth = $this->db_connection->prepare('UPDATE users '
                    . 'SET user_failed_logins = 0, user_last_failed_login = NULL '
                    . 'WHERE user_id = :user_id AND user_failed_logins != 0');
                // excute the code where the user is successful
                $sth->execute(array(':user_id' => $result_row->user_id));

                // if user has check the "remember me" checkbox, then generate token and write cookie
                if (isset($user_rememberme)) {

                    // when logging in run the generate cookie function
                    $this->newRememberMeCookie();


                } else {

                    // Reset remember-me token
                    $this->deleteRememberMeCookie();


                }

                // Will regenerate the password hash if the hash factor is changed in the config
                if (defined('HASH_COST_FACTOR')) {

                    // check if the hash needs to be rehashed
                    if (password_needs_rehash($result_row->user_password_hash, PASSWORD_DEFAULT, array('cost' => HASH_COST_FACTOR))) {

                        // calculate new hash with new cost factor
                        $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT, array('cost' => HASH_COST_FACTOR));

                        // insert the new hash into the database with prepared statements to stop SQL injections
                        $query_update = $this->db_connection->prepare('UPDATE users SET user_password_hash = :user_password_hash WHERE user_id = :user_id');
                        $query_update->bindValue(':user_password_hash', $user_password_hash, PDO::PARAM_STR);
                        $query_update->bindValue(':user_id', $result_row->user_id, PDO::PARAM_INT);
                        // excute the querty to update the new passwrod hash
                        $query_update->execute();

                        if ($query_update->rowCount() == 0) {

                            // inform the user that their password has be better protected (not really relavant to the user)
                            $this->messages['We\'ve updated your password encryption to make it more safer for you.'];
                        } else {

                            // if there was an error generating the hash
                            $this->messages['There was an issue with your password hash, please contact support.'];
                        }
                    }
                }


                // end of successfull statement
            }
            // end of valid user part of login function
        }
        // end of standard login function
    }




    // Create all data needed for remember me cookie connection on client and server side
    private function newRememberMeCookie()
    {
        // if database connection opened
        if ($this->databaseConnection()) {
            // generate 64 char random string and store it in current user data
            $random_token_string = hash('sha256', mt_rand());

            // query to update the newly generated cookie in the database
            $sth = $this->db_connection->prepare("UPDATE users SET user_rememberme_token = :user_rememberme_token WHERE user_id = :user_id");
            // excute the query to update the cookie on a server side
            $sth->execute(array(':user_rememberme_token' => $random_token_string, ':user_id' => $_SESSION['user_id']));

            // generate cookie string that consists of userid, random string and combined hash of both
            $cookie_string_first_part = $_SESSION['user_id'] . ':' . $random_token_string;
            // add the unqiue cookie data for that user and the site cookie key (defined in config file) together
            $cookie_string_hash = hash('sha256', $cookie_string_first_part . COOKIE_SECRET_KEY);
            // add the unique cookie data to the already created unique data and site key
            $cookie_string = $cookie_string_first_part . ':' . $cookie_string_hash;

            // set cookie so that it is now updated (or set) on the client side
            setcookie('rememberme', $cookie_string, time() + COOKIE_RUNTIME, "/", COOKIE_DOMAIN);
        }
    }




    // Delete all data needed for remember me cookie connection on client and server side
    private function deleteRememberMeCookie()
    {
        // if database connection opened
        if ($this->databaseConnection()) {

            // reset rememberme token
            $sth = $this->db_connection->prepare("UPDATE users SET user_rememberme_token = NULL WHERE user_id = :user_id");
            // excute the query to delete the cookie that is stored in the database
            $sth->execute(array(':user_id' => $_SESSION['user_id']));


        }

        // set the rememberme-cookie to ten years ago so that it won't be valid at all
        // becuase that's the best way to do it when goolging it although I think
        // there should be a function to set all site cookies to null :/
        setcookie('rememberme', false, time() - (3600 * 3650), '/', COOKIE_DOMAIN);
    }





    // Perform the logout, resetting the session DESTORY DESTROY DESTROY RAAAAWWWWRRRR!!!
    public function doLogout()
    {
        // run the delete function of the cookie
        $this->deleteRememberMeCookie();

        // set the session to a blank array
        $_SESSION = array();

        // just because we're over protective, I'm going to destroy the emptied session muwhaha
        session_destroy();

        // set the user logged status to false, effective signing them out of all pages
        $this->user_is_logged_in = false;

        // send a lovely message telling them they have now been logged out
        $this->messages[] = "You have been logged out.";
    }




    // Simply return the current state of the user's login, check if the user is logged in
    public function isUserLoggedIn()
    {
        // yep, that's it
        return $this->user_is_logged_in;
    }




    // Edit the user's name, provided in the editing form
    public function editUserName($user_name)
    {
        // prevent database flooding, shorts the name when they go to edit it (on the account page when logged in)
        $user_name = substr(trim($user_name), 0, 64);

        // if the user is that special they decided to set their own name again, then display an error
        if (!empty($user_name) && $user_name == $_SESSION['user_name']) {
            $this->errors[] = "Sorry, that username is the same as your current one. Please choose another one.";

            // username cannot be empty and must be azAZ09 and 2-64 characters
        } elseif (empty($user_name) || !preg_match("/^(?=.{2,32}$)[a-zA-Z][a-zA-Z0-9]*(?: [a-zA-Z0-9]+)*$/", $user_name)) {

            // return an error that the username is invalid
            $this->errors[] = "Username does not fit the correct format: only a-Z and numbers are allowed, 2 to 32 characters";

        } else {

            // check if new username already exists
            $result_row = $this->getUserData($user_name);

            // if the user name is already in use
            if (isset($result_row->user_id)) {

                // the error message to display the user is already taken
                $this->errors[] = "Sorry, that username is already taken. Please choose another one.";


            } else {

                // write user's new data into database
                $query_edit_user_name = $this->db_connection->prepare('UPDATE users SET user_name = :user_name WHERE user_id = :user_id');
                $query_edit_user_name->bindValue(':user_name', $user_name, PDO::PARAM_STR);
                $query_edit_user_name->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                // execute the query to edit the user's name
                $query_edit_user_name->execute();

                // if the user name successfull update
                if ($query_edit_user_name->rowCount()) {

                    // set the new username into session
                    $_SESSION['user_name'] = $user_name;

                    // message to say the username has been updated
                    $this->messages[] = "Your username has been changed successfully. New username is " . $user_name;
                } else {

                    // message if we have a failure to change the address
                    $this->errors[] = "Sorry, your chosen username renaming failed";
                }
            }
        }
    }




    // Edit the user's email, provided in the editing form
    public function editUserEmail($user_email)
    {
        // prevent database flooding, shorts the name when they go to edit it (on the account page when logged in)
        $user_email = substr(trim($user_email), 0, 64);

        // if they email is the same as before
        if (!empty($user_email) && $user_email == $_SESSION["user_email"]) {

            // output error
            $this->errors[] = "Sorry, that email address is the same as your current one. Please choose another one.";

            // user mail cannot be empty and must be in email format
        } elseif (empty($user_email) || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {

            // most modern browers should even prevent the username from the email
            $this->errors[] = "Your email address is not in a valid email format";

        } else if ($this->databaseConnection()) {
            // check if new email already exists
            $query_user = $this->db_connection->prepare('SELECT * FROM users WHERE user_email = :user_email');
            $query_user->bindValue(':user_email', $user_email, PDO::PARAM_STR);
            $query_user->execute();
            // get result row (as an object)
            $result_row = $query_user->fetchObject();

            // if this email exists
            if (isset($result_row->user_id)) {

                // send error to explain the email address is already registered
                $this->errors[] = "This email address is already registered. Please use the \"I forgot my password\" page if you don't remember it.";


            } else {

                // write users new data into database
                $query_edit_user_email = $this->db_connection->prepare('UPDATE users SET user_email = :user_email WHERE user_id = :user_id');
                $query_edit_user_email->bindValue(':user_email', $user_email, PDO::PARAM_STR);
                $query_edit_user_email->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                // execute the query to update the new email address over the old one
                $query_edit_user_email->execute();

                // if thee is a returned user
                if ($query_edit_user_email->rowCount()) {

                    // override the session email address to replace the old one
                    $_SESSION['user_email'] = $user_email;
                    // tell the user the message has been updated
                    $this->messages[] = "Your email address has been changed successfully. New email address is " . $user_email;


                } else {

                    // if there was no returned rows then display the error
                    $this->errors[] = "Sorry, your email changing failed.";


                }
            }
        }
    }




    // Edit the user's password, provided in the editing form
    public function editUserPassword($user_password_old, $user_password_new, $user_password_repeat)
    {
        // if the any of the variables are empty
        if (empty($user_password_new) || empty($user_password_repeat) || empty($user_password_old)) {

            // error message for empty password field(s)
            $this->errors[] = "Password field was empty";


            // is the repeat password identical to password
        } elseif ($user_password_new !== $user_password_repeat) {

            // passwords are the same error message
            $this->errors[] = "Password and password repeat are not the same";

            // password need to have a minimum length of 6 characters
        } elseif (strlen($user_password_new) < 6) {

            // if the password is shorter than six characters
            $this->errors[] = "Password has a minimum length of 6 characters";

            // all the above tests are ok
        } else {

            // database query, getting hash of currently logged in user (to check with just provided password)
            $result_row = $this->getUserData($_SESSION['user_name']);

            // if this password hash exists for the user logged in
            if (isset($result_row->user_password_hash)) {

                // using PHP 5.5's password_verify() function to check if the provided passwords fits to the hash of that user's password
                if (password_verify($user_password_old, $result_row->user_password_hash)) {

                    // set hash to variable, if not hash assigned in the config then set hash to null
                    $hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);

                    // hash and salt the users password with the password hash function
                    $user_password_hash = password_hash($user_password_new, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));

                    // write users new hash into database
                    $query_update = $this->db_connection->prepare('UPDATE users SET user_password_hash = :user_password_hash WHERE user_id = :user_id');
                    $query_update->bindValue(':user_password_hash', $user_password_hash, PDO::PARAM_STR);
                    $query_update->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                    // exectute password update query
                    $query_update->execute();

                    // check if exactly one row was successfully changed:
                    if ($query_update->rowCount()) {

                        // output that password was changed if the MySQL query
                        $this->messages[] = "Password successfully changed!";


                    } else {

                        // if the MySQL query returns nothing then fail
                        $this->errors[] = "Sorry, your password changing failed.";
                    }
                } else {

                    // if the current password was entered incorrectly
                    $this->errors[] = "Your OLD password was wrong.";
                }
            } else {

                // if the password hash is invalid
                $this->errors[] = "This user does not exist";
            }
        }
    }




    public function editAppearance($user_first_name, $user_last_name, $avatar_display){

        $this->saveFullName($user_first_name, $user_last_name, $avatar_display);
    }


    Private function saveFullName($user_first_name, $user_last_name, $avatar_display) {
        global $_SESSION;

        if ($this->databaseConnection()) {

            $sql = $this->db_connection->prepare('UPDATE `users` SET `user_first_name` = :user_first_name, `user_last_name` = :user_last_name, `user_display_avatar` = :user_display_avatar WHERE `user_id` = :user_id');

            $sql->bindValue(':user_first_name', 	$user_first_name			, PDO::PARAM_STR);
            $sql->bindValue(':user_last_name', 	    $user_last_name			    , PDO::PARAM_STR);
            $sql->bindValue(':user_display_avatar', $avatar_display			    , PDO::PARAM_STR);
            $sql->bindValue(':user_id',      		$_SESSION['user_id']		, PDO::PARAM_INT);

            // execute the Instagram save and check response
            if ($sql->execute()) {
                $_SESSION['user_first_name'] =      $user_first_name;
                $_SESSION['user_last_name'] =       $user_last_name;
                $_SESSION['user_display_avatar'] =  $avatar_display;
                return $this->callbackMessage("Your first and last name has been successfully updated", "success");
            } else {
                return $this->callbackMessage("ERROR: " . $sql->errorCode() . ", please contact support." , "danger");
            }

        } else {

            return $this->callbackMessage("Database connection open", "danger");

        }
    }

    private function saveAvatar($files){

    }

    // Sets a random token into the database (that will verify the user when he/she comes back via the link
    // in the email) and sends the according email.
    public function setPasswordResetDatabaseTokenAndSendMail($user_name)
    {
        // stop database flooding by timing the usenrame
        $user_name = trim($user_name);

        // if the username is empty when trying to reset the email
        if (empty($user_name)) {
            $this->errors[] = "Username field was empty";

        } else {
            // generate timestamp (to see when exactly the user (or an attacker) requested the password reset mail)
            // btw this is an integer ;)
            $temporary_timestamp = time();
            // generate random hash for email password reset verification (40 char string)
            $user_password_reset_hash = sha1(uniqid(mt_rand(), true));
            // database query, getting all the info of the selected user
            $result_row = $this->getUserData($user_name);

            // if this user exists
            if (isset($result_row->user_id)) {

                // database query to update the password reset hash before sending the email link
                $query_update = $this->db_connection->prepare('UPDATE users SET user_password_reset_hash = :user_password_reset_hash,
                                                               user_password_reset_timestamp = :user_password_reset_timestamp
                                                               WHERE user_name = :user_name');
                // prepared statement of the password reset hash
                $query_update->bindValue(':user_password_reset_hash', $user_password_reset_hash, PDO::PARAM_STR);
                // prepared statement of the timestamp to allow expiring of links
                $query_update->bindValue(':user_password_reset_timestamp', $temporary_timestamp, PDO::PARAM_INT);
                // prepared statement of the username
                $query_update->bindValue(':user_name', $user_name, PDO::PARAM_STR);
                // excute the query to update the password reset hash
                $query_update->execute();

                // check if exactly one row was successfully changed:
                if ($query_update->rowCount() == 1) {
                    // send a mail to the user, containing a link with that token hash string
                    $this->sendPasswordResetMail($user_name, $result_row->user_email, $user_password_reset_hash);
                    return true;
                } else {
                    // if there is no return data
                    $this->errors[] = "Database connection problem.";
                }
            } else {
                // if the user id is not valid
                $this->errors[] = "This user does not exist";
            }
        }
        // return false (this method only returns true when the database entry has been set successfully)
        return false;
    }




    // sends an email to the provided email address to reset password, returns if the email could be sent or not (boolean)
    public function sendPasswordResetMail($user_name, $user_email, $user_password_reset_hash)
    {


        // include the Mandrill PHP Wrapper
        require 'lib/mandrill/src/Mandrill.php';


        // start a new instants of the Mandrill class
        $mandrill = new Mandrill($GLOBALS['mandrillAPIKey']);


        // generate the message that is sent to the user upon sending an email
        $message = array(

            // the subject will be sent as the email subject.
            'subject' => 'Reset Password on ' . $GLOBALS['brand'] . ' for ' . $user_name,

            // this variable is stored in the config file
            // for this to work over SMTP you must allow Mandrill's confirmation txt record
            // on your DNS for your domain that you are using
            'from_email' => $GLOBALS['email'],

            // the visible name that is displayed as the sender on things like Google Mail
            'from_name' => 'The ' . $GLOBALS['brand'] . ' Team',

            // array of where to send the email to, multiple arrays for multiple people
            'to' => array(
                array('email' => $user_email, 'name' => $user_name)
            )

        );


        // the name of the template stored on the Mandrill website (defined in the config)
        $template_name = $GLOBALS['mandrillTemplateName'];


        // generates the link for the verfication using URL enconde to support all email types
        $link = $GLOBALS['domain'] . 'password_reset' . $GLOBALS['dotPHP'] . '?user_name=' . urlencode($user_name) . '&verification_code=' . urlencode($user_password_reset_hash);



        // generate the body of the email, the email supports HTML
        $template_content = array(
            array(
                'name' => 'main',
                'content' => '<h2>Welcome back to ' . $GLOBALS['brand'] . ' ' .  $user_name . '</h2> <p> You have requested a password reset via our website, if this was not you then please ignore this email or contact our support if you believe this an issue.</p>'),
            array(
                'name' => 'footer',
                'content' => '<p>Simply click <a href=\'' . $link .' \'>here</a> to join the community.</p>')

        );

        // the returned array sent from Mandrill's servers, print this to debug
        $returned_message = $mandrill->messages->sendTemplate($template_name, $template_content, $message);


        // the final part of this function, if the Mandril repsonse is good, then return true
        if($returned_message[0]['status'] == "sent") {

            // everything worked, so return true
            return true;


        } else {

            // Mandrill returns an error message, so tell the user that it was unsuccessful and return false
            $this->errors[] = "The reset password mail NOT successfully sent! Error: " . $mail->ErrorInfo;
            return false;
        }
    }





    // Checks if the verification string in the account verification mail is valid and matches to the user.
    public function checkIfEmailVerificationCodeIsValid($user_name, $verification_code)
    {
        // prevent database flooding - trim the username
        $user_name = trim($user_name);

        // if the username is empty or the code is empty
        if (empty($user_name) || empty($verification_code)) {

            // empty message error
            $this->errors[] = "Empty link parameter data.";


        } else {

            // database query, getting all the info of the selected user
            $result_row = $this->getUserData($user_name);

            // if this user exists and have the same hash in database
            if (isset($result_row->user_id) && $result_row->user_password_reset_hash == $verification_code) {

                // calculate an hour ago in seconds
                $timestamp_one_hour_ago = time() - 3600; // 3600 seconds are 1 hour

                // if the time is within an hour ago
                if ($result_row->user_password_reset_timestamp > $timestamp_one_hour_ago) {

                    // set the marker to true, making it possible to show the password reset edit form view
                    $this->password_reset_link_is_valid = true;

                } else {

                    // the link has expired because it has been longer than an hour, return error
                    $this->errors[] = "Your reset link has expired. Please use the reset link within one hour.";
                }
            } else {

                //the user or link is invalid
                $this->errors[] = "This user does not exist";

            }
        }
    }





    // Checks and writes the new password as the password has been reset
    public function editNewPassword($user_name, $user_password_reset_hash, $user_password_new, $user_password_repeat)
    {
        // trip the username to prevent database flooding
        $user_name = trim($user_name);

        // if anyhing is empty when changing the password
        if (empty($user_name) || empty($user_password_reset_hash) || empty($user_password_new) || empty($user_password_repeat)) {

            // error message
            $this->errors[] = "Password field was empty";


            // is the repeat password identical to password
        } else if ($user_password_new !== $user_password_repeat) {

            // passwords do not match error
            $this->errors[] = "The passwords do not match";


            // password need to have a minimum length of 6 characters
        } else if (strlen($user_password_new) < 6) {

            // the password is too short error
            $this->errors[] = "Password has a minimum length of 6 characters";


            // if database connection opened
        } else if ($this->databaseConnection()) {
            // get the hash cost factor from the config file, or set to null if it doesn't exist there.
            $hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);

            // crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 character hash string
            $user_password_hash = password_hash($user_password_new, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));

            // write users new hash into database along with the setting the reset hash to null
            $query_update = $this->db_connection->prepare('UPDATE users SET user_password_hash = :user_password_hash,
                                                           user_password_reset_hash = NULL, user_password_reset_timestamp = NULL
                                                           WHERE user_name = :user_name AND user_password_reset_hash = :user_password_reset_hash');
            // prepared statement for the password hash
            $query_update->bindValue(':user_password_hash', $user_password_hash, PDO::PARAM_STR);
            // prepared statement for the reset password hash (this is only used to find the password hash so it can be set to null)
            $query_update->bindValue(':user_password_reset_hash', $user_password_reset_hash, PDO::PARAM_STR);
            // prepared statement for the username to confirm that the user that is been edited is the correct one
            $query_update->bindValue(':user_name', $user_name, PDO::PARAM_STR);
            // excute out the password hash update
            $query_update->execute();

            // check if exactly one row was successfully changed:
            if ($query_update->rowCount() == 1) {

                // return the password reset check to true
                $this->password_reset_was_successful = true;

                // inform the user everything worked and the password has been updated
                $this->messages[] = "Password successfully changed!";

            } else {

                // if there was an error
                $this->errors[] = "Sorry, your password changing failed.";

            }
        }
    }





    // Gets the success state of the password-reset-link-validation.
    public function passwordResetLinkIsValid()
    {
        // used in the verification check function
        return $this->password_reset_link_is_valid;
    }





    // Gets the success state of the password-reset action.
    public function passwordResetWasSuccessful()
    {
        // if the password reset was successful
        return $this->password_reset_was_successful;
    }





    // Gets the username
    public function getUsername()
    {
        // simple function to return the username
        return $this->user_name;
    }






    /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     * Gravatar is the #1 (free) provider for email address based global avatar hosting.
     * The URL (or image) returns always a .jpg file !
     * For deeper info on the different parameter possibilities:
     * @see http://de.gravatar.com/site/implement/images/
     *
     * @param string $email The email address
     * @param string $s Size in pixels, defaults to 50px [ 1 - 2048 ]
     * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
     * @param array $atts Optional, additional key/value attributes to include in the IMG tag
     * @source http://gravatar.com/site/implement/images/php/
     */
    public function getGravatarImageUrl($email, $s = 50, $d = 'mm', $r = 'g', $atts = array() )
    {
        // this is a function for the Gravatar website
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r&f=y";

        // the image url (on gravatarr servers), will return in something like
        // http://www.gravatar.com/avatar/205e460b479e2e5b48aec07710c08d50?s=80&d=mm&r=g
        // note: the url does NOT have something like .jpg
        $this->user_gravatar_image_url = $url;

        // build img tag around
        $url = '<img src="' . $url . '"';
        foreach ($atts as $key => $val)
            $url .= ' ' . $key . '="' . $val . '"';
        $url .= ' />';

        // the image url like above but with an additional <img src .. /> around
        $this->user_gravatar_image_tag = $url;
    }




}