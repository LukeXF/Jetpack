<div class="jumbotron smaller">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h1 class="slideDown">Sign In Page</h1>
			</div>
		</div>
	</div>

</div>

<div class="container">
	<div class="row">
		<div class="col-md-4 col-md-offset-4 contentbox">

			<form method="post" action="login<?php echo $dotPHP; ?>" name="loginform">
						
				<?php
				// show potential errors / feedback (from login object)
				if (isset($login)) {
				    if ($login->errors) {
				        foreach ($login->errors as $error) {
				            echo $error;
				        }
				    }
				    if ($login->messages) {
				        foreach ($login->messages as $message) {
				            echo $message;
				        }
				    }
				}
				?>

				<?php
				// show potential errors / feedback (from registration object)
				if (isset($registration)) {
				    if ($registration->errors) {
				        foreach ($registration->errors as $error) {
				            echo $error;
				        }
				    }
				    if ($registration->messages) {
				        foreach ($registration->messages as $message) {
				            echo $message;
				        }
				    }
				}
				?>
				<a class="brand animate" href="<?php echo $domain; ?>"><?php echo $brand; ?></a>
			    <input id="user_name" type="text" name="user_name" required placeholder="Your Username" />
			    <input id="user_password" type="password" name="user_password" autocomplete="off" required placeholder="Your Password" />

			    <input type="checkbox" id="user_rememberme" name="user_rememberme" value="1" />
			    <label for="user_rememberme">Keep me logged in (for 2 weeks)</label>
			    <input type="submit" class="btn btn-primary" name="login" value="Log In" />
			</form>

			<a href="register.php">Register New Account</a>
			<a href="password_reset.php">I forgot my password</a>

		</div>
	</div>
</div>
