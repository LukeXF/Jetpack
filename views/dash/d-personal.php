<?php
// show potential errors / feedback (from login object)
if (isset($login)) {
	if ($login->errors) {
		echo "<div class='alert alert-danger alert-dismissible'>";
		foreach ($login->errors as $error) {
			echo $error;
		}
		echo "</div>";
	}
	if ($login->messages) {

		echo "<div class='alert alert-success alert-dismissible'>";
		foreach ($login->messages as $message) {
			echo $message;
		}
		echo "</div>";
	}
}
?>

<?php
// show potential errors / feedback (from registration object)
if (isset($registration)) {
	if ($registration->errors) {

		echo "<div class='alert alert-success alert-dismissible'>";
		foreach ($registration->errors as $error) {
			echo $error;
		}
		echo "</div>";
	}
	if ($registration->messages) {

		echo "<div class='alert alert-danger alert-dismissible'>";
		foreach ($registration->messages as $message) {
			echo $message;
		}
		echo "</div>";
	}
}
?>


<div class="row tile">
	<div class="row appearance-form-padding">
		<div class="col-md-12">
			<?php $siteFunctions->displayCallbackMessage(); ?>
			<h3>Edit your appearance</h3>
		</div>
		<form method="post" action="settings.php?p=personal" enctype="multipart/form-data">
			<div class="col-md-8">
				<?php
					// if the user has an avatar already set
					if (isset($_SESSION['user_avatar'])) {
						echo "already set";
					} else {
						$imageUpload->displayUpload();

					}
				?>
			</div>


			<div class="col-md-4">
				<label for="user_first_name">First Name</label>
				<input placeholder="Your first name" id="user_first_name" value="<?php echo $_SESSION['user_first_name']; ?>" type="text" name="user_first_name" pattern="[a-zA-Z0-9]{2,64}"  />

				<label for="user_last_name">Last Name</label>
				<input placeholder="Your last name" id="user_last_name" value="<?php echo $_SESSION['user_last_name']; ?>" type="text" name="user_last_name" pattern="[a-zA-Z0-9]{2,64}"  />
				<div class="form-btn">
					<button type="submit" class="btn btn-default" name="user_update_appearance" value="Change Username" class="btn btn-default" type="submit">Submit</button>
				</div>
			</div>
		</form>
	</div>
</div>



<!--
* Copyright (c) 2008 http://www.webmotionuk.com / http://www.webmotionuk.co.uk
* Date: 2008-11-21
* "PHP & Jquery image upload & crop"
* Ver 1.2
* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
* ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
* WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
* IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
* INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
* PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
* INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
* STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF
* THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*
-->









<div class="row tile">
	<div class="col-md-8">
		<h3>Change your username</h3>
		<p>Your current username is <b><?php echo $_SESSION['user_name']; ?></b>.</p>
	</div>
	<div class="col-md-4 tile-dark">
		<div class="form-btn-center">
			<button type="button" class="btn btn-default" data-toggle="modal" data-target="#username">Edit Username</button>
		</div>
	</div>
</div>

<div class="modal fade" id="username" tabindex="-1" role="dialog" aria-labelledby="username">
	<div class="modal-dialog" role="document">
		<form method="post" action="settings.php?p=personal" name="user_edit_form_name">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h3 class="modal-title" id="myModalLabel">Change your username</h3>
					<p>Your username may be a mixture of alphanumeric characters, ranging from 2 to 32 characters and is case sensitive when logging in.</p>
					<p>Your current username is <b><?php echo $_SESSION['user_name']; ?></b>.</p>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-4">
							<p>New Username</p>
						</div>
						<div class="col-md-8">
							<input placeholder="Be a unique snowflake" id="user_name" type="text" name="user_name" pattern="[a-zA-Z0-9]{2,64}" required />
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<p>Enter password</p>
						</div>
						<div class="col-md-8">
							<input placeholder="For security confirmation" id="user_password" type="password" name="user_password" required />
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button style="" type="submit" class="btn btn-black" name="user_edit_submit_name" value="Change Username" class="btn btn-default" type="submit">Edit Username</button>
				</div>
			</div>
		</form>
	</div>
</div>







<div class="row tile">
	<div class="col-md-8">
		<h3>Change your email</h3>
		<p>Your current email address is <b><?php echo $_SESSION['user_email']; ?></b>.</p>
	</div>
	<div class="col-md-4 tile-dark">
		<div class="form-btn-center">
			<button type="button" class="btn btn-default" data-toggle="modal" data-target="#email">Edit Email</button>
		</div>
	</div>
</div>

<div class="modal fade" id="email" tabindex="-1" role="dialog" aria-labelledby="email">
	<div class="modal-dialog" role="document">
		<form method="post" action="settings.php?p=personal" name="user_edit_form_email" autocomplete="false">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h3 class="modal-title" id="myModalLabel">Change your email</h3>
					<p>We'll send you an email with a link to confirm this change. If you ever get an email change notice from us and you didn't change your email address then the link in the email will revert the changes.</p>
					<p>Your current email address is <b><?php echo $_SESSION['user_email']; ?></b>.	</p>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-4">
							<p>New email</p>
						</div>
						<div class="col-md-8">
							<input placeholder="Your new address" id="user_email" type="email" name="user_email" required />
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<p>Confirm email</p>
						</div>
						<div class="col-md-8">
							<input placeholder="Enter the above address" type="email" name="user_email_repeat" required autocomplete="false" />
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<p>Enter password</p>
						</div>
						<div class="col-md-8">
							<input placeholder="For security confirmation" id="user_password" type="password" name="user_password" required />
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button style="" type="submit" class="btn btn-black" name="user_edit_submit_email" value="Change Username" class="btn btn-default" type="submit">Edit Email</button>
				</div>
			</div>
		</form>
	</div>
</div>







<div class="row tile">
	<div class="col-md-8">
		<h3>Change your password</h3>
		<p>You'll need to confirm your current password first.</p>
	</div>
	<div class="col-md-4 tile-dark">
		<div class="form-btn-center">
			<button type="button" class="btn btn-default" data-toggle="modal" data-target="#password">Change Password</button>
		</div>
	</div>
</div>

<div class="modal fade" id="password" tabindex="-1" role="dialog" aria-labelledby="password">
	<div class="modal-dialog" role="document">
		<form method="post" action="settings.php?p=personal" name="user_edit_form_password" autocomplete="false">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h3 class="modal-title" id="myModalLabel">Change your password</h3>
					<p>Your password must be at least 6 characters in length. We strongly encourage you to include a combination of letters, numbers, and special characters.</p>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-4">
							<p>Old password</p>
						</div>
						<div class="col-md-8">
							<input placeholder="Your old password" id="user_password_old" type="password" name="user_password_old" autocomplete="off" required />
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<p>New password</p>
						</div>
						<div class="col-md-8">
							<input placeholder="Minimum of 6 characters" id="user_password_new" type="password" name="user_password_new" autocomplete="off" required />
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<p>Confirm password</p>
						</div>
						<div class="col-md-8">
							<input placeholder="Repeat the above password" id="user_password_repeat" type="password" name="user_password_repeat" autocomplete="off" required />
						</div>
					</div>


				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button style="" type="submit" class="btn btn-black" name="user_edit_submit_password" value="Change Password" class="btn btn-default" type="submit">Change password</button>
				</div>
			</div>
		</form>
	</div>
</div>