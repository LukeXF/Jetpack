<!-- Static navbar -->
<?php
	if (isset($onHomePage) && $onHomePage == true) {
		$homepage = "navbar-homepage";
	} else {
		$homepage = "";
	}
?>
<nav class="navbar navbar-default navbar-static-top <?php echo $homepage; ?> navbar-mini">
	<div class="container">
		<div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<li><a><i class="btl bt-phone"></i> 07534 12345 </a></li>
				<li><a><i class="btl bt-envelope"></i> <?php echo $email; ?> </a></li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<?php  if ($login->isUserLoggedIn() == true) { ?>
					<li><a href="?logout">Logout</a></li>
					<li><a href="<?php echo $siteFunctions->url("settings"); ?>">Your Account</a></li>
					<li><a href="<?php echo $siteFunctions->url('login'); ?>"><?php echo $_SESSION['user_name']; ?> &nbsp;<img class="nav-image" src="<?php echo $avatar ?>"></a></li>
				<?php } else { ?>
					<li><a href="<?php echo $siteFunctions->url('login'); ?>">Log in</a></li>
					<li><a href="<?php echo $siteFunctions->url('register'); ?>">Register</a></li>
				<?php } ?>
			</ul>
		</div><!--/.nav-collapse -->
	</div>
</nav>
<nav class="navbar navbar-default navbar-static-top <?php echo $homepage; ?>">
	<div class="container">
		<div class="navbar-header">

			<a class="navbar-brand animate" href="<?php echo $domain; ?>">
				<img src="<?php echo $domain; ?>assets/img/logo.png" class="img-responsive" alt="<?php echo $brand; ?>">
			</a>

			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<img class="nav-image" src="<?php echo $avatar ?>">
			</button>
		</div>
		<div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<?php $siteFunctions->navbar($navbar); ?>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<?php  if ($login->isUserLoggedIn() == true) { ?>
				<?php } else { ?>
					<li><a href="<?php echo $domain . "login"; ?>">Login to your dashboard</a></li>
				<?php } ?>
				<li>
					<?php $store->navbarCart(); ?>
				</li>
				<li>
					<a href="<?php echo $siteFunctions->url("cart"); ?>">
							<span class="bt-stack bt-3x">
		  						<i class="btl bt-circle bt-stack-1x"></i>
	  							<i class="btl bt-shopping-cart bt-stack-sm"></i>
							</span>
					</a>
				</li>
			</ul>
		</div><!--/.nav-collapse -->
	</div>
</nav>