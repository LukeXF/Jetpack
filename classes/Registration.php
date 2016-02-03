<?php


// Handles the user registration
class Registration
{
    // setup the predefined variables of this class
    private $db_connection            = null; // object $db_connection The database connection
    public  $registration_successful  = false; // boolean success state of registration
    public  $verification_successful  = false; // boolean success state of verification
    public  $errors                   = array(); // array collection of error messages
    public  $messages                 = array(); // array collection of success / neutral messages

    // the function "__construct()" automatically starts whenever an object of this class is created,
    // this is done with "$login = new Login();"
    public function __construct()
    {
        // create a new session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // if we have such a POST request, call the registerNewUser() method
        if (isset($_POST["user_name"])) {

            // the create new user function is carried out if post data is submitted
            $this->registerNewUser($_POST['user_name'], $_POST['user_email'], $_POST['user_password_new'], $_POST['user_password_repeat'], $_POST["captcha"]);

        // if we have such a GET request, call the verifyNewUser() method 
        } else if (isset($_GET["id"]) && isset($_GET["verification_code"])) {

            // run the verify new user function to confirm the users email address
            $this->verifyNewUser($_GET["id"], $_GET["verification_code"]);

        }
    }

    // Checks if database connection is opened and open it if not (the start of all queries for registration)
    private function databaseConnection()
    {
        // connection already opened
        if ($this->db_connection != null) {

            // there is already a connection open so return true
            return true;

        } else {

            // create a database connection, using the constants from config/config.php
            try {

                // create the start of PDO query
                $this->db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
                $this->db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // return true now that the connection is opened
                return true;

            } catch (PDOException $e) {

                // If an error is catched, database connection failed
                $this->errors[] = "Database connection problem." . $e;

                // return false :(
                return false;

            }
        }
    }



    // handles the entire registration process. checks all error possibilities, and creates a new user in the database if
    // everything is fine :)
    private function registerNewUser($user_name, $user_email, $user_password, $user_password_repeat, $captcha)
    {
        // we just remove extra space on username and email
        $user_name  = trim($user_name);
        $user_email = trim($user_email);

        // check provided data validity
        if (strtolower($captcha) != strtolower($_SESSION['captcha'])) {
            // if captcha is wrong
            $this->errors[] = "Captcha was wrong!";

        } elseif (empty($user_name)) {
            // if there user field is empty
            $this->errors[] = "Username field was empty.";

        } elseif (empty($user_password) || empty($user_password_repeat)) {
            // if the password field is empty
            $this->errors[] = "Password field was empty.";

        } elseif ($user_password !== $user_password_repeat) {
            // if the passwords do not match
            $this->errors[] = "The passwords do not match.";

        } elseif (strlen($user_password) < 6) {
            // if the password is shorter than 6 characters
            $this->errors[] = "The password is too short, please try more then 6 character.";

        } elseif (strlen($user_name) > 64 || strlen($user_name) < 2) {
            // if the username field is shorter than 2 and bigger then 64 characters
            $this->errors[] = "Username cannot be shorter than 2 or longer than 64 characters.";

        } elseif (!preg_match('/^[a-z\d]{2,64}$/i', $user_name)) {
            // if someone is been special...
            $this->errors[] = "Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters.";

        } elseif (empty($user_email)) {
            // if the email field is empty
            $this->errors[] = "Email field cannot be empty";

        } elseif (strlen($user_email) > 64) {
            // if the email fiel is longer than 64 characters
            $this->errors[] = "Email cannot be longer than 64 characters";

        } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            // if the email is not a vaild format
            $this->errors[] = "Your email address is not in a valid email format";

        } else if ($this->databaseConnection()) {

            // if everything else passes then continue with the connection


            // check if username or email already exists, select username and email address with PDO
            $query_check_user_name = $this->db_connection->prepare('SELECT user_name, user_email FROM users WHERE user_name=:user_name OR user_email=:user_email');
            // prepared statement of username
            $query_check_user_name->bindValue(':user_name', $user_name, PDO::PARAM_STR);
            // prepared statement of email address
            $query_check_user_name->bindValue(':user_email', $user_email, PDO::PARAM_STR);
            // excuste the query check on the username
            $query_check_user_name->execute();
            // set result to the query array under PDO's fetch all
            $result = $query_check_user_name->fetchAll();

            // if username or/and email find in the database
            if (count($result) > 0) {



                // loop through the returned query and compare it agains the entered data
                for ($i = 0; $i < count($result); $i++) {

                    // if the check is there, then display error
                    $this->errors[] = ($result[$i]['user_name'] == $user_name) ? "Sorry, that username is already taken. Please choose another one." : "This email address is already registered. Please use the \"I forgot my password\" page if you don't remember it.";
                }



            } else {




                // set the level of hashing of the password (defined in the config)
                $hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);
                // crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 character hash string
                // this part uses the lib/password_compatibility.php library
                $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));
                // generate random hash for email verification (40 char string)
                $user_activation_hash = sha1(uniqid(mt_rand(), true));
                // write new users data into database (finally!)
                $query_new_user_insert = $this->db_connection->prepare('INSERT INTO users (user_name, user_password_hash, user_email, user_activation_hash, user_registration_ip, user_registration_datetime) VALUES(:user_name, :user_password_hash, :user_email, :user_activation_hash, :user_registration_ip, now())');
                // prepared statement for the username field
                $query_new_user_insert->bindValue(':user_name', $user_name, PDO::PARAM_STR);
                // prepared statement for the hashed password (not plain text)            
                $query_new_user_insert->bindValue(':user_password_hash', $user_password_hash, PDO::PARAM_STR);
                // prepared statement for the email address field    
                $query_new_user_insert->bindValue(':user_email', $user_email, PDO::PARAM_STR);
                // prepared statement for the verification of email hash 
                $query_new_user_insert->bindValue(':user_activation_hash', $user_activation_hash, PDO::PARAM_STR);
                // prepared statement for the user's IP address using REMOTE_ADDR
                // the server array can be a bit unreliable if using CloudFlare or DDoS protection  
                $query_new_user_insert->bindValue(':user_registration_ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);

                // execute it all! 
                $query_new_user_insert->execute();



                // get the last entered ID and set it to variable user_id for use in the verification link sent via email
                $user_id = $this->db_connection->lastInsertId();




                if ($query_new_user_insert) {


                    // send a verification email
                    if ($this->sendVerificationEmail($user_id, $user_email, $user_activation_hash, $user_name)) {

                        // when mail has been send successfully
                        $this->messages[] = "Your account has been created, check your emails to verify.";
                        // return true for this function
                        $this->registration_successful = true; 


                    } else {


                        // delete this users account immediately, as there is no verification email sent
                        $query_delete_user = $this->db_connection->prepare('DELETE FROM users WHERE user_id=:user_id');
                        // prepared statement for the user id
                        $query_delete_user->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                        // execute out the delete query
                        $query_delete_user->execute();

                        // display error messages
                        $this->errors[] = "Sorry, we could not send you an verification mail. Your account has NOT been created.";
                        // we do not need to return function to false as it is already assumed by default

                    }


                } else {

                    // display error message
                    $this->errors[] = "Sorry, your registration failed. Please go back and try again.";
                    // we do not need to return function to false as it is already assumed by default

                }
            }
        }
    }



    // sends an email to the provided email address, returns if the email could be sent or not (boolean)
    public function sendVerificationEmail($user_id, $user_email, $user_activation_hash, $user_name)
    {


        // include the Mandrill PHP Wrapper
        require 'lib/mandrill/src/Mandrill.php';


        // start a new instants of the Mandrill class
        $mandrill = new Mandrill($GLOBALS['mandrillAPIKey']);


        // generate the message that is sent to the user upon sending an email
        $message = array(

            // the subject will be sent as the email subject.
            'subject' => 'Welcome to ' . $GLOBALS['brand'] . ' ' . $user_name,

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
        $link = $GLOBALS['domain'] . 'register' . $GLOBALS['dotPHP'] . '?id=' . urlencode($user_id) . '&verification_code=' . urlencode($user_activation_hash);



        // generate the body of the email, the email supports HTML
        $template_content = array(
            array(
                'name' => 'main',
                'content' => '<h2>Welcome to ' . $GLOBALS['brand'] . ' ' .  $user_name . '</h2> <p> You\'re almost there but before we can begin you need to confirm your email address.</p>'),
            array(
                'name' => 'footer',
                'content' => '<p>Simply click <a href=\'' . $link .' \'>here</a> to join the community.</p>')

        );

        // the returned array sent from Mandrill's servers, print this to debug
        $returned_message = $mandrill->messages->sendTemplate($template_name, $template_content, $message);


        // the final part of this function, if the Mandril repsonse is good, then return true
        if($returned_message[0]['status'] == "sent") {
            return true;
        } else {
            $this->errors[] = "Verification Mail NOT successfully sent! Error: " . $returned_message['message'];
            return false;
        }
    }



    // checks the id/verification code combination and set the user's activation status to true (=1) in the database
    public function verifyNewUser($user_id, $user_activation_hash)
    {


        // if database connection opened (incase of errors)
        if ($this->databaseConnection()) {

            // try to update user with specified information
            $query_update_user = $this->db_connection->prepare('UPDATE users SET user_active = 1, user_activation_hash = NULL WHERE user_id = :user_id AND user_activation_hash = :user_activation_hash');
            $query_update_user->bindValue(':user_id', intval(trim($user_id)), PDO::PARAM_INT);
            $query_update_user->bindValue(':user_activation_hash', $user_activation_hash, PDO::PARAM_STR);
            $query_update_user->execute();

            // if the amount of users (should be one) is returned with more than 0
            if ($query_update_user->rowCount() > 0) {

                // return true
                $this->verification_successful = true;
                $this->messages[] = "Activation was successful! You can now log in!";

            } else {

                $this->errors[] = "Sorry, invalid verification code.";

            }

        }
    }

}
