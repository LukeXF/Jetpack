<?php
    include('assets/header.php');
    require_once('classes/Registration.php');
    $registration = new Registration();

    if ($login->isUserLoggedIn() == true) {
        $siteFunctions->callback(""); // redirect to home page
    } else {
        include("views/v-register.php");
    }
    include('assets/footer.php');
?>