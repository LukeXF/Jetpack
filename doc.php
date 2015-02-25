<!DOCTYPE html>
<html lang="en-US"><head>
    <meta charset="utf-8">
    <title>Documentation | Stripe Advanced Payment Terminal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
	<!-- Begin CSS -->
	<link href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet">
 
	<!-- Begin JS -->
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
 
    <!--[if lt IE 9]>
        <script src="js/html5shiv.min.js"></script>
    <![endif]-->

    <style>
    	body {
    		position: relative;
    	}
    	.nav-wrapper {
    		position: relative;
    		width: 223px;
    		margin-right: 40px;
			margin-top: 40px;
    	}
		.nav-wrapper .nav {
			text-align: right;
		}
		.nav-wrapper .nav.affix {
			top: 20px;
			width: inherit;
		}
		.column-right {
			padding-bottom: 600px;
		}
		.column-right > div {
			padding-bottom: 30px;
		}
		.column-right > hr {
			margin: 100px 0;
		}
		.column-right > div > h3 {
			padding-top: 30px;
		}
		.copyright {
			text-align: center;
			margin-bottom: 20px;
		}
    </style>

</head>    
<body data-spy="scroll" data-target=".nav-wrapper">
 

 
	<div class="container">

		
		<div class="page-header">
			<h2 class="colorprimary">Documentation <small>Stripe Advanced Payment Terminal</small></h2>
		</div>

		
		<div class="row">
			<div class="col-sm-3 column-left">
				
				<div class="nav-wrapper">
					<ul class="nav nav-pills nav-stacked" data-spy="affix" data-offset-top="134">
						<li role="presentation" class="active"><a href="#about">About</a></li>
						<li role="presentation"><a href="#requirements">Requirements</a></li>
						<li role="presentation"><a href="#support">Support</a></li>
						<li role="presentation"><a href="#installation">Installation</a></li>
						<li role="presentation"><a href="#configuration">Configuration</a></li>
						<li role="presentation"><a href="#subscription_settings">Subscription Settings</a></li>
						<li role="presentation"><a href="#production_vs_sandbox">Production vs Sandbox</a></li>
						<li role="presentation"><a href="#changelog">Changelog</a></li>
					</ul>
				</div>


			</div>
			<div class="col-sm-9 column-right">

				<div id="about">
					<h3>About</h3>
					<p>
						This PHP script allows you to accept one time payments as well as recurring payments (subscriptions) directly on your website. Credit cards are processed using your Stripe account and customers can complete that process without leaving your site.  PayPal Standard is also available, in which case your customers will be sent to PayPal to complete payment.  There is also a secure admin page where you can view and manage all of your payments, subscriptions, items, invoices, settings, etc. 
					</p>
					<div><strong>Date Created:</strong> December 31st, 2014</div>
					<div><strong>Last Updated:</strong> January 5th, 2015</div>
					<div><strong>Version:</strong> 1.1</div>
					<div><strong>Author:</strong> Devin Lewis <small>(<a href="http://www.devinlewis.com" target="_blank">www.devinlewis.com</a>)</small></div>
					
					<br>
					<div class="alert alert-warning">
						<strong>Heads Up!</strong>
						<p>If you find a bug or if you run into any issues, <strong><em>please</em></strong> <a href="mailto:devinlewis@gmail.com">contact me</a> and let me know about it so I can take care of it for you.  Also, if you are thinking about leaving a bad review or negative feedback, I'd <strong><em>really, really</em></strong> appreciate it if you'd <a href="mailto:devinlewis@gmail.com">contact me</a> first and give me the chance to make things right for you. My number one goal is to have happy customers who are 100% satisfied. :)</p>
					</div>

					<div class="alert alert-info">
						<strong>Quick Start Guide:</strong>
						<ol>
							<li>Unzip the file that you downloaded from CodeCanyon.</li>
							<li>Edit the <code>/stripe-advanced-payment-terminal/lib/config.php</code> file with your own values.</li>
							<li>Upload the entire contents of the <code>/stripe-advanced-payment-terminal/</code> directory to your server.</li>
							<li>Visit your uploaded site in a browser and click on the "Install Application" button.</li>
							<li>Login to the admin page (<code>admin.php</code>) to set your Stripe API credentials and the required settings.</li>
							<li>You're now ready to accept online payments!</li>
						</ol>
					</div>

				</div>

				<hr>

				<div id="requirements">
					<h3>Requirements</h3>
					<ol>
						<li>PHP 5.2 or higher</li>
						<li>MySQL Database</li>
						<li>cURL must be enabled <small>(This is most likely already enabled on your server, but you can contact your hosting provider if you are unsure)</small></li>
						<li>PDO must be enabled <small>(This is most likely already enabled on your server, but you can contact your hosting provider if you are unsure)</small></li>
						<li>SSL certificate installed <small>(in order to process live transactions)</small></li>
						<li>Stripe merchant account</li>
					</ol>
				</div>

				<hr>

				<div id="support">
					<h3>Support</h3>
					<p>To contact me or receive support for this item, you can email me personally at <a href="mailto:devinlewis@gmail.com">devinlewis@gmail.com</a>. Please allow up to 2 business days for a response (although I will generally get back to you within 12-24 hours, except on weekends).</p>
					<br>
					<p>
						Support for my items includes:
						<ul>
							<li>Responding to questions or problems regarding the item and its features.</li>
							<li>Fixing bugs and reported issues.</li>
							<li>Providing updates to ensure compatibility with new software versions.</li>
							<li>FREE installation services <small>(please contact me to learn more)</small></li>
						</ul>
					</p>
					<p>
						Item support does not include:
						<ul>
							<li>Customization services</li>
							<li>Support for third party software and plug-ins</li>
						</ul>
					</p>
				</div>

				<hr>

				<div id="installation">
					<h3>Installation</h3>
					<ol>
						<li>Extract the contents of the zip file that you downloaded from CodeCanyon.</li>
						<li>
							Open the <code>/stripe-advanced-payment-terminal/lib/config.php</code> file and edit the following values:
							<ul>
								<li><code>admin_username</code> <small>(the username you want to use to access the admin page)</small></li>
								<li><code>admin_password</code> <small>(the password you want to use to access the admin page)</small></li>
								<li><code>db_host</code> <small>(your database host value, usually localhost)</small></li>
								<li><code>db_username</code> <small>(your database username)</small></li>
								<li><code>db_password</code> <small>(your database password)</small></li>
								<li><code>db_name</code> <small>(your database name)</small></li>
							</ul>
						</li>
						<li>Save the config.php file and upload the contents of the <code>/stripe-advanced-payment-terminal/</code> folder to your server.</li>
						<li>Visit your newly uploaded site in a web browser <small>(e.g. www.yourdomain.com/path/to/install/)</small></li>
						<li>You should be shown the installation page.  Simply click on the "Install Application" button to complete the installation.</li>
						<li>After completing installation, you need to access the admin page (<code>admin.php</code>) to set your Stripe API credentials and also configure the terminal settings.</li>
					</ol>
				</div>

				<hr>

				<div id="configuration">
					<h3>Configuration</h3>
	
					<ol>
						<li>After completing installation, login to the admin page (<code>admin.php</code>).</li>
						<li>Click on the "Settings" tab to view all of the various settings that are available.</li>
						<li>You will need to enter in your Stripe API credentials before you can accept credit card payments.</li>
						<li>You will need to enter you PayPal email address before you can accept PayPal Standard payments.</li>
					</ol>

					<p>Within the admin area, you can view and manage your payments, subscriptions, invoices, items and settings.  For all of the different settings available, there is some help text right underneath each input field that exlains what the setting does. </p>
						  
					
				</div>

				<hr>

				<div id="subscription_settings">
					<h3>Subscription Settings</h3>
	
					<p>
						On the admin settings page, you can set your subscription/recurring payment settings by clicking on the "Subscription Settings" tab.  
					</p>
					<p>
						For PayPal subscriptions, all of the settings that you specify will work just fine.  However, for Stripe subscriptions, the "Subscription Length" setting has no effect.  That is because Stripe subscriptions do not allow you to set an end date, they will run indefinitely or until they are canceled.
					</p>
						  
					
				</div>

				<hr>

				<div id="production_vs_sandbox">
					<h3>Production vs Sandbox</h3>
					<p>
						When first setting up the payment terminal, it is recommended that you enter in your Stripe test API credentials and also set the PayPal environment to <code>sandbox</code> mode.  After you have done some testing and verified that everything is working properly, you can switch the environment over to <code>production</code>.  Once in <code>production</code>, you are ready to accept live payments.  Also, in <code>production</code> mode, you must have a valid SSL certificate installed.
					</p>
				</div>

				<hr>

				<div id="changelog">
					<h3>Changelog</h3>
					<p>
						<strong>v1.1 - January 5th, 2015</strong>
						<ul>
							<li>Added ability to specify custom invoice number</li>
							<li>Minor bug fixes</li>
						</ul>
					</p>
					<p>
						<strong>v1.0 - December 29th, 2014</strong>
						<ul>
							<li>Initial release</li>
						</ul>
					</p>
				</div>

			</div>
		</div>

    	<div class="copyright">

			<div class="alert alert-success">
				Thank you so much for purchasing this item. I sincerely hope it helps in whatever you are trying to accomplish. :)
			</div>

    		<small>Copyright &copy; 2014 DevinLewis.com</small>
    	</div>

	</div>
        
    
	 
</body>
</html>