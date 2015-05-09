<?php

    include('assets/header.php');
    include('assets/navbar.php');

	require_once('classes/Registration.php');
	$registration = new Registration();

    if ($login->isUserLoggedIn() == true) {
        include("views/logged_in.php");
    } else {
        include("views/v-register.php");
    }

    include('assets/footer.php');
?>