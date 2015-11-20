<?php

	include('assets/header.php');
	require_once('classes/Payments.php');
	$Payments = new Payments();

	$Payments->submitPayment();
?>
