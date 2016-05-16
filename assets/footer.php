

			
		</div>
	</div>
</div>

<footer>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				hi
			</div>
		</div>
	</div>
</footer>

</body>
</html>

<script type="text/javascript" src="<?php echo $domain; ?>assets/js/app.js"></script>

<?php

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);
$siteFunctions->log("Page loaded in " . number_format($totaltime, 2) . " seconds");
?>


