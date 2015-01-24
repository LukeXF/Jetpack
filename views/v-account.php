<div class="jumbotron smaller">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="slideDown">Account Overview</h1>
            </div>
        </div>
    </div>

</div>

<div class="container">
    <div class="row">
    	<div class="col-md-6 col-md-offset-3 contentbox">

    		<div class="row">
		    	<div class="col-md-3">
					<img src="<?php echo $grav_url; ?>">
				</div>
		    	<div class="col-md-9">

		    		<h5>You are logged in as <b><?php echo $_SESSION['user_name']; ?></b> </h5>

				    <a href="?logout"><?php echo WORDING_LOGOUT; ?></a> - 
				    <a href="edit.php"><?php echo WORDING_EDIT_USER_DATA; ?></a>
		    	</div>
			</div>

		</div>
    	<div class="col-md-6 col-md-offset-3 contentbox">

    		<div class="row">
		    	<div class="col-md-12">
		    		<h5>Accessiable Information:</h5>
		    		<table style="width:100%">
						<tr>
							<th width="20%">Type</th>
							<th width="65%">Code</th>
							<th width="15%">Output</th>
						</tr>
						<tr>
							<td>User ID</td>
							<td><code>&#60;?php echo $_SESSION['user_id']; ?&#62;</code></td>
							<td><?php echo $_SESSION['user_id'] ?></td> 
						</tr>
						<tr>
							<td>User Name</td>
							<td><code>&#60;?php echo $_SESSION['user_name']; ?&#62;</code></td>
							<td><?php echo $_SESSION['user_name'] ?></td> 
						</tr>
						<tr>
							<td>User Email</td>
							<td><code>&#60;?php echo $_SESSION['user_email; ?&#62;</code></td>
							<td><?php echo $_SESSION['user_email'] ?></td> 
						</tr>
						<tr>
							<td>User Status</td>
							<td><code>&#60;?php echo $_SESSION['user_logged_in']; ?&#62;</code></td>
							<td><?php echo $_SESSION['user_logged_in'] ?></td> 
						</tr>
						<tr>
							<td>User Image</td>
							<td><code>&#60;img src="&#60;?php echo $grav_url ?&#62;"&#62;</code></td>
							<td><img style="width: 65px;" src="<?php echo $grav_url; ?>"></td> 
						</tr>
						<tr>
							<td></td>
							<td></td> 
						</tr>
					</table>
				</div>
			</div>

		</div>
	</div>
</div>