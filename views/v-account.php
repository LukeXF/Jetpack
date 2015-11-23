	<?php

	$navbar = array(
		"Personal Details" 	=> array( "active" => "", "logo" => "edit",	 	 	"tooltip"=>"change your personal details", "url" => "personal"),
		"Billing Details" 	=> array( "active" => "", "logo" => "credit-card", 	"tooltip"=>"manage your payment options", "url" => "billing"),
		"Notifications" 	=> array( "active" => "", "logo" => "inbox", 	 	"tooltip"=>"change how we message you", "url" => "notifications"),
		"Subscriptions" 	=> array( "active" => "", "logo" => "key", 	 		"tooltip"=>"review previous invoices", "url" => "subscriptions")
	);
	if ( empty($_GET['p']) ) {
		$activeTab = "personal";
	} else {
		$activeTab = $_GET['p'];
	}

	if (!empty($_GET['p'])) {
		if ($_GET['p'] == 'personal'){
			$select_tab = 'views/dash/d-personal.php';
		} elseif ($_GET['p'] == 'billing'){
			$select_tab = 'views/dash/d-billing.php';
		} elseif ($_GET['p'] == 'notifications'){
			$select_tab = 'views/dash/d-notifications.php';
		} elseif ($_GET['p'] == 'subscriptions'){
			$select_tab = 'views/dash/d-subscriptions.php';
		} else {
			$select_tab = 'views/dash/d-personal.php';
		}
	} else {
		$select_tab = 'views/dash/d-personal.php';
	}

?>
<div class="container">
	<div class="row">

		<div class="col-md-10 col-md-offset-1">

			<?php // $siteFunctions->debug($_POST); ?>
			<?php // $siteFunctions->debug($_FILES); ?>
			<?php // $siteFunctions->debug($_SESSION); ?>
			<ul class="nav nav-tiles">
				<h3 align="center">Account Settings</h3>
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
