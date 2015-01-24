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

			<form method="post" action="login" name="loginform">
						
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
				<a class="brand animate" href="http://tickets.ashour.pw/"><?php echo $brand; ?></a>
			    <input id="user_name" type="text" name="user_name" required placeholder="<?php echo WORDING_USERNAME; ?>" />
			    <input id="user_password" type="password" name="user_password" autocomplete="off" required placeholder="<?php echo WORDING_PASSWORD; ?>" />

			    <input type="checkbox" id="user_rememberme" name="user_rememberme" value="1" />
			    <label for="user_rememberme"><?php echo WORDING_REMEMBER_ME; ?></label>
			    <input type="submit" class="btn btn-primary" name="login" value="<?php echo WORDING_LOGIN; ?>" />
			</form>

			<a href="register.php"><?php echo WORDING_REGISTER_NEW_ACCOUNT; ?></a>
			<a href="password_reset.php"><?php echo WORDING_FORGOT_MY_PASSWORD; ?></a>

		</div>
	</div>
</div>
