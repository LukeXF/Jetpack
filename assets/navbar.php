<!-- Static navbar -->
<nav class="navbar navbar-default navbar-static-top">
	<div class="container">
		<div class="navbar-header">

			<a class="navbar-brand animate" href="<?php echo $domain; ?>">
				<img src="<?php echo $domain; ?>assets/img/logo.png" class="img-responsive" alt="<?php echo $brand; ?>">
			</a>

			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<img class="nav-image" src="<?php echo $grav_url ?>">
			</button>
		</div>
		<div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<?php $siteFunctions->navbar($navbar); ?>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<?php  if ($login->isUserLoggedIn() == true) { ?>
					<li class="dropdown animate">
						<a href="#" class="dropdown-toggle animate" data-toggle="dropdown" role="button" aria-expanded="false">

							<?php echo $_SESSION['user_first_name']; ?>
							<?php echo $_SESSION['user_last_name']; ?>
							(<?php echo $_SESSION['user_name']; ?>)
							<i>Account</i>
							<span class="caret"></span>
							<img class="nav-image" src="<?php echo $grav_url ?>"></a>

						<ul class="dropdown-menu" role="menu">
							<li><a href="<?php echo $siteFunctions->url("settings"); ?>">Account Overview</a></li>
							<li><a href="<?php echo $siteFunctions->url("settings", array("p"=>"general-settings") ); ?>">Edit Your Account</a></li>
							<li class="divider"></li>
							<li><a href="?logout">Logout</a></li>
						</ul>
					</li>
				<?php } else { ?>
					<li><a href="<?php echo $domain . "login"; ?>">Login to your dashboard</a></li>
				<?php } ?>
			</ul>
		</div><!--/.nav-collapse -->
	</div>
</nav>