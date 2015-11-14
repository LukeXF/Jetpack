<?php
    include('assets/header.php');
    $activeTab = "Profile";

    if ($login->isUserLoggedIn() == true) {
        include('assets/navbar.php');
        include("views/v-account.php");
    } else {
        include("views/v-login.php");
    }

    include('assets/footer.php');
?>