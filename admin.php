<?php
include('assets/header.php');
$activeTab = "Admin";

if ($login->isUserLoggedIn() == true) {

    if (isset($_SESSION['user_account_type']) && $_SESSION['user_account_type'] == "admin") {
        include('assets/navbar.php');
        include("views/v-admin.php");
    } else {
        $siteFunctions->callback(); // redirect to home page
    }
} else {
   include("views/v-login.php");
}

include('assets/footer.php');
?>