<?php
    include('assets/header.php');

    // check to see if the user is logged in
    if ($login->isUserLoggedIn() == true) {
        $siteFunctions->debug();
        $siteFunctions->debug($_SERVER);

        // if the admin is logged in, then load admin classes
        if ($_SESSION['user_account_type'] == "admin") {
            $adminUsers = new adminUsers();
        }

        if(isset($_POST['process'])) {
            if ($_POST['process'] == "loginAsUser") {
                $adminUsers->loginAsUser($_POST['user_id']);
            }
            if ($_POST['process'] == "updateUsersData") {
                $adminUsers->updateUsersData($_POST['data']);
            }
        }

    } else {
        $siteFunctions->callback("login");
    }
?>