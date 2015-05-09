<body>
	<!-- Fixed navbar -->
	<nav class="navbar navbar-default navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand animate" href="<?php echo $domain; ?>"><?php echo $brand; ?></a>
			</div>
			<div id="navbar" class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<?php
						$functions->createNavbar($navbar);
					?>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<?php
						$functions->createNavbar($navbar2);
					?>
				</ul>
			</div><!--/.nav-collapse -->
		</div>
	</nav>
	<div class="wrapper">