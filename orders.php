<?php
include('assets/header.php');
$activeTab = "Orders";

if ($login->isUserLoggedIn() == true) {
    include('assets/navbar.php');
    include("views/v-orders.php");
} else {
    include("views/v-login.php");
}

include('assets/footer.php');
?>