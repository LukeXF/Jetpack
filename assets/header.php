<?php


	// load classes
	require_once('lib/config.php');
	require_once('classes/siteFunctions.php');
	require_once('classes/Login.php');
	require_once('classes/Weather.php');
	require_once('classes/ImageUpload.php');
	require_once('classes/Store.php');
	require_once('classes/Orders.php');
	require_once('classes/AdminUsers.php');
	require_once('classes/AdminProducts.php');
	require_once('classes/AdminOrders.php');


	// initialize classes
	$siteFunctions = new siteFunctions();
	$login = new Login();

	if ($login->isUserLoggedIn() == true) {

		$weather = new Weather();
		$imageUpload = new imageUpload();
		$store = new Store();
		$orders = new Orders();
		$avatar = $siteFunctions->getAvatar();
		// $siteFunctions->debug($_SESSION);

		$navbar = array(
			"Home" 		=> array( "active" => "", "logo" => "flaticon-two114",	 	"url" => $siteFunctions->url()),
			"Store" 	=> array( "active" => "", "logo" => "flaticon-two114",	 	"url" => $siteFunctions->url("store") ),
			"Orders" 	=> array( "active" => "", "logo" => "flaticon-two114",	 	"url" => $siteFunctions->url("orders") )
		);

		if (isset($_SESSION['user_account_type']) && $_SESSION['user_account_type'] == "admin") {
			$navbar['Admin'] = array( "active" => "", "logo" => "flaticon-id1", 		"url" => $siteFunctions->url("admin") );
		}


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

		<link rel="stylesheet" type="text/css" href="<?php echo $domain; ?>assets/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="<?php echo $domain; ?>assets/css/weather-icons.min.css">
		<link rel="stylesheet" type="text/css" href='//fonts.googleapis.com/css?family=Montserrat'>
		<link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="<?php echo $domain; ?>assets/css/style.css">
		<link rel="stylesheet" type="text/css" href="<?php echo $domain; ?>assets/css/flag-icon.min.css">
		<link rel="stylesheet" type="text/css" href="<?php echo $domain; ?>assets/css/black-tie.min.css">
		<link rel="icon" 	   type="image/png" href="<?php echo $domain; ?>assets/img/logo.png">

		<script src="//use.typekit.net/eoe6bhb.js"></script>
		<script>try{Typekit.load();}catch(e){}</script>

		<script type="text/javascript" src="<?php echo $domain; ?>assets/js/jquery.min.js"></script>
		<script type="text/javascript" src="<?php echo $domain; ?>assets/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="<?php echo $domain; ?>assets/js/stupidtable.min.js"></script>


		<script>
			$( document ).ready(function() {
				$(function () {
					$('[data-toggle="tooltip"]').tooltip()
				})
				$('[data-tooltip="tooltip"]').tooltip();
			});
		</script>

	</head>
