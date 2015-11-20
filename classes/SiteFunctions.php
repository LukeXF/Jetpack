<?php

// Handles the site functions
class siteFunctions
{
	// setup the predefined variables of this class
	public $db_connection            = null; // object $db_connection The database connection
	public  $errors                   = array(); // array collection of error messages
	public  $messages                 = array(); // array collection of success / neutral messages
	public  $pageTitle                = null; // the page title of each page
	public 	$jumbotronTitle      	  = null;

	// the function "__construct()" automatically starts whenever an object of this class is created,
	// this is done with "$login = new Login();"
	public function __construct()
	{
		global $tidyLinks;

		if ($tidyLinks) {
			$GLOBALS['dotPHP'] = ".php";
		} else {
			$GLOBALS['dotPHP'] = "";
		}

		// set the page title
		$this->setPageTitle();

		// the title loaded from the SetPageTitle function
		$workingTitle = $this->SetPageTitle();

	}

	// Checks if database connection is opened and open it if not (the start of all queries for registration)
	public function databaseConnection()
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
				$this->errors[] = "Database connection problem.";

				// return false :(
				return false;

			}
		}
	}




	// Sets the title of all the pages
	public function setPageTitle()
	{

		// break up the page name just to get the file name with .php
		$pageName = ucfirst(basename($_SERVER['PHP_SELF'],'.php'));

		// if there is is an override on the file name then set the page title
		if (!empty($GLOBALS['overrideTitleName'])) {

			// override page the page name
			// useful to use if there is a page like password-reset and you want to name it 'Forgot Password'
			$pageName = $GLOBALS['overrideTitleName'];


		// if we're on the home page then assume we want to call the index page home
		} elseif ($pageName == "Index") {

			// then name the title
			$pageName = "Home";


		}

		// return the page title ready to be referenced
		return $GLOBALS['brand'] . " | " . $pageName;
	}




	// generates the header jumbotron
	private function createJumbotron($jumbotronTitleInput, $animate, $class)
	{
		// build the jumbotron that displays the title and class name
		echo "<div class='jumbotron " . $class . "'>
				<div class='container'>
					<div class='row'>
						<div class='col-md-12'>
							<h1 class='" . $animate . "'>" . $jumbotronTitleInput . "</h1>
						</div>
					</div>
				</div>
			</div>
		";
	}




	// display the function
	public function displayJumbotron($lol = null, $animate = "slideDown", $class = 'smaller')
	{
		// if there is no defined text called at the start of each page then generate text form the setPageTitle function
		if (empty($lol)) {

			// replace the brand name with nothing as it has been set by the page title function
			$formattedPreText = str_replace($GLOBALS['brand'] . " | ", "", $this->setPageTitle() );

			// return the generated jumbotron with page title (loaded form the page name)
			return $this->createJumbotron($formattedPreText . " page", $class);

		}

		// return the generated jumbotron with the parameter string
		return $this->createJumbotron($lol, $animate, $class);


	}



	// generate the usertag
	public function userTag($user_name)
	{
		// check if the user_name exists
		if ($this->getUserData($user_name)) {

			// set the email for this function
			$userTag_email = $this->getUserData($user_name)->user_email;
			// set the username for this function
			$userTag_username = $this->getUserData($user_name)->user_name;

		} else {

			// If there is no error, output the the error function
			$this->error('No user found');
		}

		return "
		<a class='profile' href='" . $GLOBALS['domain'] . "user/" . $userTag_username . "'>
			By&nbsp;&nbsp;
			<h4 class='animate'>
			<img src='" . $this->get_gravatar($userTag_email) . "'>
			" . $userTag_username  . "
			</h4>
		</a>";

	}





	// Search into database for the user data of user_name specified as parameter (selects all data)
	private function getUserData($username, $searchByID = false)
	{
		// if database connection opened
		if ($this->databaseConnection()) {

			// if you want to search by ID
			if ($searchByID) {
				// database query, getting all the info of the selected user
				$query_user = $this->db_connection->prepare("SELECT `user_id`, `user_name`, `user_email`
														FROM `users`
														WHERE `user_id` = :username");
			} else {
				// database query, getting all the info of the selected user
				$query_user = $this->db_connection->prepare("SELECT `user_id`, `user_name`, `user_email`
														FROM `users`
														WHERE `user_name` = :username OR `user_email` = :username");
			}

			// prepared statement for the username
			$query_user->bindValue(':username', $username, PDO::PARAM_STR);
			// excute username
			$query_user->execute();

			// get result row (as an object)
			return $query_user->fetchObject();

		} else {

			// if connection issues
			return false;
		}
	}


	// Get User from ID (used in the news system and other database queries)
	public function getUserDatafromID($username, $info = "user_name")
	{
		// search by user ID to get the returned object of the user data
		// the true is set to search by ID and not username or email
		if ($this->getUserData($username, true) != false) {

			// output the desired user data
			return $this->getUserData($username, true)->$info;

		} else {

			// If there is no error, output the the error function
			$this->error('No user found');
		}
	}





	// Display the latest registered users
	public function getNewestRegistered($amount = 20)
	{

		// if database connection opened
		if ($this->databaseConnection()) {

			// database query, getting the latest registered users in descending ordered
			$query_latestUsers = $this->db_connection->prepare("SELECT `user_name`, `user_email`
																FROM `users`
																ORDER BY `user_registration_datetime`
																DESC LIMIT :amount");
			// prepared statement for the amount, note: has been trimmed and set to int before the query can accept an int
			$query_latestUsers->bindValue(':amount', $amount, PDO::PARAM_INT);
			// execute query
			$query_latestUsers->execute();

			// set variable for the returned data
			$latestUsers = $query_latestUsers->fetchAll();

				// if there is actually some results then continue to echo them out
				if ($query_latestUsers->rowCount() > 0) {

					// count the amount of users returned from the query
					$count = count($latestUsers);

					// add a counter to start incrementing
					$i = 0;

					// place all results inside a class for formatting
					echo "<div class='recently_registered_row'>";

					// loop through each user with this while loop until $i equals the total amount of users
					while ($i < $count) {

							// echo out the clickable link and class name
							echo "<a class='profile animateAll' href='" . $GLOBALS['domain'] . "user/" . $latestUsers[$i]['user_name'] . "'>";

							// set variable for the users profile picture
							$gravImg = $this->get_gravatar($latestUsers[$i]['user_email']);

							// echo out the image
							echo "<div class='recently_registered'
							data-placement='bottom' title='' data-tooltip='tooltip'
							data-original-title='" . $latestUsers[$i]['user_name'] . "'
							style='background: url(" . $gravImg . ") #09C6E8'>
							</div>";

							// close off the clickable link
							echo "</a>";
						// add one to the counter
						$i++;
					}

					// close off the class and therefore all data inside
					echo "</div>";



				} else {

					// assume that the system is with no users so return a message informing there is no users yet
					echo "There is no users to display, <a href='" . $GLOBALS['domain'] . "users/" . $latestUsers[$i]['user_name'] . ">";
				}

		} else {

			// output there is a failed connection
			$this->error('No database connection');
		}


	}




	// debug function for error logging
	public function debug($array) {

		// echo out html fomratting
		echo "<pre>";
		// print the array in an easy to read format
		print_r($array);
		// echo out close html formatting
		echo "</pre>";
	}






	// load the gravatar image for a defined email address
	public function get_gravatar($email, $size = 80)
	{


		// detect if on localhost then display a default imageset by gravtar
		// this is because Gravatar does not allow custom default images on a private network
		// it must be publicly accessible, therefor if on localhost the image will fail
		if ($this->detectLocalhost()){

			// because we're on localhost, use gravatar's pre-provided identicon
			$defaultSiteLogo = "identicon";

		} else {

			// the default logo loaded in the config file for production environments only
			// cannot be on localhost url
			$defaultSiteLogo = urlencode($GLOBALS['logo']);

		}


		// create the gravtar variable by loading the gravatar url
		$gravatar = "https://www.gravatar.com/avatar/";
		// remove blank spaces after and convert the lowercase email address to then hd5 it up
		$gravatar .= md5( strtolower( trim( $email ) ) );
		// finally add the get variables for the fallback image and the size of the image to load
		$gravatar .= "?d=" . $defaultSiteLogo . "&s=" . $size;

		// return the image as the url ready to be inputted into a <img> tag
		return $gravatar;
	}






	// a simple function to detect if on localhost or now
	private function detectLocalhost()
	{

		// tell the function that theses are the localhost names
		$localhost = array('127.0.0.1', '::1', 'localhost');


		// if the webpage matches a localhost name
		if(!in_array($_SERVER['REMOTE_ADDR'], $localhost)){

			// then return false because you're on a production environment
			return false;

		} else {

			// then return true because the domain matches localhost and is in the array
			return true;

		}
	}

	// build url with get parameters
	public function url($page = "index", $getArray = false) {

		// access the site domain
		global $domain;
		global $dotPHP;
		global $_SERVER;

		if ($getArray) {

			$params = false; // check if it's a new GET param

			foreach ($getArray as $key => $value) {

				if ($params == false) {
					$params .= '?' . $key . '=' . $value;
				} else {
					$params .= '&' . $key . '=' . $value;
				}

			}
			return $domain . $page . $dotPHP . $params;

		} else {

			return $domain . $page . $dotPHP;

		}

	}

	function _isCurl(){
		return function_exists('curl_version');
	}


	// for callbacks on the processing page
	public function callback($callback = false, $request = false) {

		// access the site domain
		global $domain;
		global $dotPHP;
		global $_SERVER;


	    //if ($callback == $_SERVER['HTTP_REFERER'] ) {
		//	$callback = $domain . $callback;
	    // }

		if ($request)  {
			// if the GET request is already started, then add to it
			if (strpos($callback,'?') !== false) {
				$and = "&";
			} else {
				$and = "?";
			}

			// alright, send user on their way.
			echo "
				<meta http-equiv='Refresh' content='0; " . $callback . $dotPHP . $and . "request=" . $request . "'>
				<script>window.location = '" . $callback . $dotPHP . $and . "request=" . $request . ";</script>
			";

		} elseif ($callback == false) {
			// if there is no callback and the user is going to the indx page (e.g. after logging in)
			echo "
				<meta http-equiv='Refresh' content='0; " . $domain . "'>
				<script>window.location = '" . $domain . ";</script>
			";

		} else {

			// normal
			echo "
				<meta http-equiv='Refresh' content='0; " . $callback . $dotPHP . "'>
				<script>window.location = '" . $callback . $dotPHP . ";</script>
			";
		}
	}

	// build the callback message
	public function displayCallbackMessage() {

		// access the session array for the message
		global $_SESSION;

		// if there is a message to display
		if (isset($_SESSION['info'])) {

			echo "
				<div class='alert alert-" . $_SESSION['info']['class'] . " alert-dismissible' role='alert'>
				<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
				" . $_SESSION['info']['message'] . "
				</div>
			";

			// destroy the message now that it has been displayed
			unset($_SESSION['info']);
		}

	}

	private function addDashes($string){
		return str_replace(' ','-',$string);
	}

	// create the circle function
	public function circle($percentage, $name, $data = false){

		$subtitle = "<div class='radial-title'>" . $name . "</div>";

		// if there is a data therem then replace the percentage
		if ($data) {
			$data = $this->seprateCurrencyFromString($data);
			$firstTitle = "<div class='percentage subdata'>" . $data . "</div>";
			$hidenumber = " style='display:none' ";
		} else {
			$hidenumber = "";
		}


		$lotsofpercentage = "<div class='percentage' " . $hidenumber . ">
			<div class='numbers'><span>-</span>
			<span>0%</span><span>1%</span><span>2%</span><span>3%</span><span>4%</span><span>5%</span><span>6%</span><span>7%</span><span>8%</span><span>9%</span><span>10%</span><span>11%</span><span>12%</span><span>13%</span><span>14%</span><span>15%</span><span>16%</span><span>17%</span><span>18%</span><span>19%</span><span>20%</span><span>21%</span><span>22%</span><span>23%</span><span>24%</span><span>25%</span><span>26%</span><span>27%</span><span>28%</span><span>29%</span><span>30%</span><span>31%</span><span>32%</span><span>33%</span><span>34%</span><span>35%</span><span>36%</span><span>37%</span><span>38%</span><span>39%</span><span>40%</span><span>41%</span><span>42%</span><span>43%</span><span>44%</span><span>45%</span><span>46%</span><span>47%</span><span>48%</span><span>49%</span><span>50%</span><span>51%</span><span>52%</span><span>53%</span><span>54%</span><span>55%</span><span>56%</span><span>57%</span><span>58%</span><span>59%</span><span>60%</span><span>61%</span><span>62%</span><span>63%</span><span>64%</span><span>65%</span><span>66%</span><span>67%</span><span>68%</span><span>69%</span><span>70%</span><span>71%</span><span>72%</span><span>73%</span><span>74%</span><span>75%</span><span>76%</span><span>77%</span><span>78%</span><span>79%</span><span>80%</span><span>81%</span><span>82%</span><span>83%</span><span>84%</span><span>85%</span><span>86%</span><span>87%</span><span>88%</span><span>89%</span><span>90%</span><span>91%</span><span>92%</span><span>93%</span><span>94%</span><span>95%</span><span>96%</span><span>97%</span><span>98%</span><span>99%</span><span>100%</span></div>
		</div>";

		// generate the script
		echo "<script type='text/javascript'>
			$('head style[type=\"text/css\"]').attr('type', 'text/less');
			less.refreshStyles();
			window.randomize = function() {
				$('.radial-" . $this->addDashes($name) . "').attr('data-progress'," . $percentage . ");
			}
			setTimeout(window.randomize, 200);
		</script>";

		// generate the cirlce
		echo "<div class='radial-progress radial-" . $this->addDashes($name) . "' data-progress='0'>
				<div class='circle'>
					<div class='mask full'>
						<div class='fill'></div>
					</div>
					<div class='mask half'>
						<div class='fill'></div>
						<div class='fill fix'></div>
					</div>
					<div class='shadow'></div>
				</div>
				<div class='inset'>
						" . $firstTitle . "
						" . $lotsofpercentage . "
						" . $subtitle . "
				</div>
			</div>";
	}

	// Move the currency out of string to allow the count up
	private function seprateCurrencyFromString($string){

		// if currency has dollar
		if (strpos($string, "$") !== false) {

			$search = '$';
			$trimmed = number_format(str_replace($search, '', $string));
			return "<span>$</span><span class='counter'>" . $trimmed . "</span>";

		// if currecny has £
		} elseif (strpos($string, "£") !== false) {

			$search = '£';
			$trimmed = number_format(str_replace($search, '', $string));
			return "<span>£</span><span class='counter'>" . $trimmed . "</span>";

		// just return the currency
		} else {

			return "<span class='counter'>" . number_format($string) . "</span>";

		}

	}

    /**
     *  Similar to the error message, but used in the proccessing page
     *  to give feedback and color on if the action was successful
     *
    **/
    public function callbackMessage($message, $class) {

        // clear previous data
        unset($_SESSION['info']);

				// create/read session
        if (!isset($_SESSION)) {
      		session_start();
        }

        // place request into session
        $_SESSION['info']['message'] = $message;
        $_SESSION['info']['class'] = $class;


        return true;
    }



	function navbar($denavbar, $image = false, $pageQueryLinks = false){
		global $activeTab;
		$i = 0;
		foreach($denavbar as $x => $x_value) {
			if (!empty($x_value["active"])) { $class = $x_value["active"]; } else { $class = ""; }
			if (!empty($x_value["url"])) { $url = $x_value["url"]; } else { $url = $x; }
			if ($x == $activeTab) { $class = "active"; }
			if (!empty($x_value["submenu"])) {
				echo "<li class='dropdown animate" . $class . "'>";
					echo "	<a href='#' class='dropdown-toggle animate' data-toggle='dropdown' role='button' aria-expanded='false'>" . $x . " 		<i class='fa fa-caret-down'></i>
							</a>
							<ul class='dropdown-menu' role='menu'><li>";
							foreach ($x_value['submenu'] as $key => $value) {
								echo "<a href='$value'>$key</a>";
							}

						//echo "<li><a href='#''>Action</a></li>";
					echo "</li></ul>";
				echo "</li>";

			} elseif ($image && $pageQueryLinks) {

				// else treat it as a normal tab
				if ($i == 0) { $divider = ""; } else { $divider = "divider"; }
				if ($activeTab == $url) { $class = "active"; } else { $class = ""; }
				echo "<li title='" . $x_value['tooltip'] . "' data-toggle='tooltip' data-placement='bottom' align='center' class='$divider col-md-3 " . $class . "'><a class='animate' href='?p=" . $url . "'><i class='btl bt-" .  $x_value['logo'] . "'></i> ";
				echo $x;
				echo "</a></li>";

			} elseif ($image) {

				// else treat it as a normal tab
				echo "<li class='" . $class . "'><a class='animate' href='" . $url . "'><i class='btl bt-" .  $x_value['logo'] . "'></i>";
				echo $x;
				echo "</a></li>";

			} elseif ($pageQueryLinks) {

				if ($activeTab == $url) { $class = "active"; } else { $class = ""; }
				echo "<li class='" . $class . "'><a class='animate' href='?p=$url'>";
				echo $x;
				echo "</a></li>";
			} else {
				echo "<li class='" . $class . "'><a class='animate' href='$url'>";
				echo $x;
				echo "</a></li>";
			}
		$i++;
		}
	}


    /**
     *  Outputs a human time ago string rather than numbered time
     *
    **/
    function timeAgo($ptime, $isMySQLDate = true){  // Past time as MySQL DATETIME value


		if ($isMySQLDate) {
	        $ptime = strtotime($ptime);

	        // Current time as MySQL DATETIME value
	        $csqltime = date('Y-m-d H:i:s');

	        // Current time as Unix timestamp
	        $ctime = strtotime($csqltime);

		} else {

	        $ptime = $ptime;

	        $ctime = time();

		}
        // Elapsed time
        $etime = $ctime - $ptime;

        if ($etime < 1)
        {
            return '0 seconds';
        }

        $a = array( 365 * 24 * 60 * 60  =>  'year',
                     30 * 24 * 60 * 60  =>  'month',
                          24 * 60 * 60  =>  'day',
                               60 * 60  =>  'hour',
                                    60  =>  'minute',
                                     1  =>  'second'
                    );
        $a_plural = array( 'year'   => 'years',
                           'month'  => 'months',
                           'day'    => 'days',
                           'hour'   => 'hours',
                           'minute' => 'minutes',
                           'second' => 'seconds'
                    );

        foreach ($a as $secs => $str)
        {
            $d = $etime / $secs;
            if ($d >= 1)
            {
                $r = round($d);
                return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
            }
        }
    }

	function clean($string) {
		$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
		$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
		return preg_replace('/-+/', ' ', $string); // Replaces multiple hyphens with single one.
	}

}

?>