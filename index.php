<?php
    include('assets/header.php');
    include('assets/navbar.php');

    if ($login->isUserLoggedIn() == true) {
        include("views/v-index.php");
    } else {
        include("views/v-index.php");
    }

    include('assets/footer.php');
?>