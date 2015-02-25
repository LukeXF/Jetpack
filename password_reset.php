<?php
    include('assets/header.php');
    include('assets/navbar.php');

    if ($login->isUserLoggedIn() == true) {
        include("views/v-password-reset.php");
    } else {
        include("views/v-password-reset.php");
    }

    include('assets/footer.php');
?>