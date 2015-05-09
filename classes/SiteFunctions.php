<?php

// Handles the site functions
class SiteFunctions
{
<<<<<<< HEAD
	// setup the predefined variables of this class
	private $db_connection            = null; // object $db_connection The database connection
	public  $errors                   = array(); // array collection of error messages
	public  $messages                 = array(); // array collection of success / neutral messages

	// the function "__construct()" automatically starts whenever an object of this class is created,
	// this is done with "$login = new Login();"
	public function __construct()
	{
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

				// return true now that the connection is opened
				return true;

			} catch (PDOException $e) {

				// If an error is catched, database connection failed
				$this->errors[] = MESSAGE_DATABASE_ERROR;

				// return false :(
				return false;

			}
		}
	}

	// created the navbar
	public function createNavbar($array){
		foreach($array as $x => $x_value) {

			if (!empty($x_value["active"])) {
				$class = $x_value["active"];
			} else {
				$class = "";
			}

			if (!empty($x_value["url"])) {
				$url = $x_value["url"];
			} else {
				$url = $x;
			}

			if (isset($activeTab)) {
				if ($x == $activeTab) {
					$class = "current";
				}
			}
			if (!empty($x_value["submenu"])) {
				echo "<li class='dropdown animate" . $class . "'>";

			

					//echo "<a class='animate'>" . $x . " <i class='fa fa-caret-down'></i></a>";
					echo "  <a href='#' class='dropdown-toggle animate' data-toggle='dropdown' role='button' aria-expanded='false'>" . $x . "       <i class='fa fa-caret-down'></i>
							</a>
							<ul class='dropdown-menu' role='menu'><li>";

							foreach ($x_value['submenu'] as $key => $value) {
								echo "<a href='$value'>$key</a>";
							}  

						//echo "<li><a href='#''>Action</a></li>";
					echo "</li></ul>";
				echo "</li>";
				
			} else {
				echo "<li class='" . $class . "'><a class='animate' href='$url'>";
				echo $x;
				echo "</a></li>";
			}
		}
	}
=======
    // setup the predefined variables of this class
    private $db_connection            = null; // object $db_connection The database connection
    public  $errors                   = array(); // array collection of error messages
    public  $messages                 = array(); // array collection of success / neutral messages

    // the function "__construct()" automatically starts whenever an object of this class is created,
    // this is done with "$login = new Login();"
    public function __construct()
    {
        // create a new session
        session_start();

        // if we have such a POST request, call the registerNewUser() method
        if (isset($_POST["register"])) {

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

                // return true now that the connection is opened
                return true;

            } catch (PDOException $e) {

                // If an error is catched, database connection failed
                $this->errors[] = MESSAGE_DATABASE_ERROR;

                // return false :(
                return false;

            }
        }
    }
>>>>>>> origin/master
}
?>