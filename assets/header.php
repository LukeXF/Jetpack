<?php


	// load classes
	require_once('lib/config.php');
	require_once('classes/siteFunctions.php');
	require_once('classes/Login.php');
	require_once('classes/Weather.php');
	require_once('classes/ImageUpload.php');
	require_once('classes/Payments.php');


	// initialize classes
	$siteFunctions = new siteFunctions();
	$login = new Login();

	if ($login->isUserLoggedIn() == true) {

		$weather = new Weather();
		$imageUpload = new imageUpload();
		$payments = new Payments();

		$navbar = array(
			"Home" 	=> array( "active" => "", "logo" => "flaticon-two114",	 	"url" => $domain),
			"Admin" 	=> array( "active" => "", "logo" => "flaticon-id1", 		"url" => $domain . "settings")
		);
		$avatar = $siteFunctions->getAvatar($_SESSION['user_email']);
		// $siteFunctions->debug($_SESSION);

	} else {

		$navbar = array(
			"Login" 	=> array( "active" => "", "logo" => "flaticon-lock22", "url" => "login" , "submenu" => array() ),
			"Register" 	=> array( "active" => "", "logo" => "flaticon-lock23", "url" => "register" , "submenu" => array() )
		);

	}

?>

<html>
	<head>
	    <meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="<?php echo $brand; ?>">
		<meta name="author" content="Luke Brown, <?php echo $email; ?>">

	    <title><?php echo $siteFunctions->setPageTitle(); ?></title>

		<link rel="stylesheet" type="text/css"  href="<?php echo $domain; ?>assets/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css"  href="<?php echo $domain; ?>assets/css/weather-icons.min.css">
		<link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet' type='text/css'>
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

		<link rel="stylesheet" type="text/css" href="<?php echo $domain; ?>assets/css/style.css">
		<link rel="stylesheet" type="text/css" href="<?php echo $domain; ?>assets/css/black-tie.min.css">

		<link rel="icon" type="image/png" href="<?php echo $domain; ?>assets/img/logo.png">

		<script src="//use.typekit.net/eoe6bhb.js"></script>
		<script>try{Typekit.load();}catch(e){}</script>

		<script type="text/javascript" src="<?php echo $domain; ?>assets/js/jquery.min.js"></script>
		<script type="text/javascript" src="<?php echo $domain; ?>assets/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="<?php echo $domain; ?>assets/js/jquery.steps.js"></script>
		<script type="text/javascript" src="<?php echo $domain; ?>assets/js/jquery-pack.js"></script>
		<script type="text/javascript" src="<?php echo $domain; ?>assets/js/jquery.imgareaselect.min.js"></script>

	</head>


	<script type="text/javascript">
		$(function () {
		$('[data-toggle="tooltip"]').tooltip()
		})
		$(document).ready(function() {
		    $('body').tooltip({
		        selector: "[data-tooltip=tooltip]",
		        container: "body"
		    });
		});
	</script>

