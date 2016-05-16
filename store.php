<?php
    include('assets/header.php');
    $activeTab = "Store";
    include('assets/navbar.php');

    if ($login->isUserLoggedIn() == true) {
        include("views/v-store.php");
    } else {
        include("views/v-store.php");
    }

include('assets/footer.php');
?>

