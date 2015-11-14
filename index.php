<?php
	include('assets/header.php');

	if ($login->isUserLoggedIn() == true) {
		$activeTab = "Home";
		include('assets/navbar.php');
		include("views/v-index.php");
	} else {
		$siteFunctions->callback("login");
	}

	include('assets/footer.php');
?>
