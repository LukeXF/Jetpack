<?php

$navbar = array(
    "Open Orders" 	    => array( "active" => "", "logo" => "shopping-cart", 	"tooltip"=>"", "url" => "open"),
    "All Orders" 	    => array( "active" => "", "logo" => "sitemap",	 	    "tooltip"=>"", "url" => "all")
);
if ( empty($_GET['p']) ) {
    $activeTab = "open";
} else {
    $activeTab = $_GET['p'];
}

if (!empty($_GET['p'])) {
    if ($activeTab == 'all'){
        $select_tab = 'views/orders/o-all.php';
    } else {
        $select_tab = 'views/orders/o-new.php';
    }
} elseif (!empty($_GET['product'])) {
    $select_tab = 'views/orders/o-id.php';
} else {
    $select_tab = 'views/orders/o-new.php';
}

?>
<div class="container">
    <div class="row">

        <div class="col-md-10 col-md-offset-1">

            <ul class="nav nav-tiles">
                <h3 align="center">Orders Page</h3>
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
