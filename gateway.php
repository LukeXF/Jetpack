<?php
    include('assets/header.php');
    include('assets/navbar.php');

    if ($login->isUserLoggedIn() == true) {
        include("views/v-gateway.php");
    } else {
        include("views/v-signin.php");;
    }

    include('assets/footer.php');
?>