<?php
    include('assets/header.php');

    // check to see if the user is logged in
    if ($login->isUserLoggedIn() == true) {
        $siteFunctions->debug();
        // $siteFunctions->debug($_SERVER);

        // if the admin is logged in, then load admin classes
        if ($_SESSION['user_account_type'] == "admin") {
            $adminUsers = new adminUsers();
            $adminProducts = new adminProducts();
        }

        if(isset($_POST['process'])) {

            if ($_POST['process'] == "addToCart") {
                $store->addToCart($_POST['data']);
                $siteFunctions->callback("store", $_POST['data']['product_id'], "product");
            }

            if ($_POST['process'] == "loginAsUser") {
                $adminUsers->loginAsUser($_POST['user_id']);
            }

            if ($_POST['process'] == "updateUsersData") {
                $adminUsers->updateUsersData($_POST['data']);
                $siteFunctions->callback("admin", "users");
            }

            if ($_POST['process'] == "createNewProduct") {
                $adminProducts->createNewProduct($_POST['data']);
                $siteFunctions->callback("admin", "products");
            }

            if ($_POST['process'] == "updateProductData") {
                $adminProducts->updateProductData($_POST['data']);
                $siteFunctions->callback("admin", "products");
            }
        }

    } else {
        $siteFunctions->callback("login");
    }
?>