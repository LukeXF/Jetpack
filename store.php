<?php
include('assets/header.php');
$activeTab = "Store";

if ($login->isUserLoggedIn() == true) {
    include('assets/navbar.php');
    include("views/v-store.php");
} else {
    include("views/v-login.php");
}

include('assets/footer.php');
?>