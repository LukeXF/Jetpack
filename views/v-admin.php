<?php

$navbar = array(
    "Users" 	=> array( "active" => "", "logo" => "user",	 	 	"tooltip"=>"", "url" => "users"),
    "Products" 	=> array( "active" => "", "logo" => "shopping-cart", 	"tooltip"=>"", "url" => "products"),
    "Orders" 	=> array( "active" => "", "logo" => "inbox", 	 	"tooltip"=>"", "url" => "notifications"),
    "Subscriptions" 	=> array( "active" => "", "logo" => "key", 	 		"tooltip"=>"review previous invoices", "url" => "subscriptions")
);
if ( empty($_GET['p']) ) {
    $activeTab = "users";
} else {
    $activeTab = $_GET['p'];
}

if (!empty($_GET['p'])) {
    if ($activeTab == 'users'){
        $adminUsers = new adminUsers();
        $select_tab = 'views/admin/a-users.php';
    } elseif ($activeTab == 'products'){
        $adminProducts = new adminProducts();
        $select_tab = 'views/admin/a-products.php';
    } elseif ($activeTab == 'notifications'){
        $adminOrders = new adminOrders();
        $select_tab = 'views/admin/a-orders.php';
    } elseif ($activeTab == 'subscriptions'){
        $select_tab = 'views/admin/a-subscriptions.php';
    } else {
        $select_tab = 'views/admin/d-personal.php';
    }
} else {
    $adminUsers = new adminUsers();
    $select_tab = 'views/admin/a-users.php';
}

?>
<div class="container">
    <div class="row">

        <div class="col-md-10 col-md-offset-1">

            <?php // $siteFunctions->debug($_POST); ?>
            <?php // $siteFunctions->debug($_FILES); ?>
            <?php // $siteFunctions->debug($_SESSION); ?>
            <ul class="nav nav-tiles">
                <h3 align="center">Admin Panel</h3>
                <?php $siteFunctions->navbar($navbar, true, true); ?>
            </ul>
        </div>

        <div class="col-md-12">
            <div class="select-tab">
                <?php
                    include ($select_tab);
                ?>
            </div>
        </div>


    </div>


</div>
