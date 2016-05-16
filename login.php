<?php
    include('assets/header.php');

    if ($login->isUserLoggedIn() == true) {
        $siteFunctions->callback(); // redirect to home page
    } else {
        include('assets/navbar.php');
		include('views/v-login.php');
    }

    include('assets/footer.php');
?>
