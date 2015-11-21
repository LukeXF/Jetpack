<?php

// Handles site payments, currently only PayPal
class Payments extends siteFunctions
{

    public function testPayment(){
        require("lib/braintree-php-3.5.0/lib/Braintree.php");
        Braintree_Configuration::environment('sandbox');
        Braintree_Configuration::merchantId('w7gz99fhtbrhj6y6');
        Braintree_Configuration::publicKey('p5hhp9ctz3znhsq3');
        Braintree_Configuration::privateKey('2baa5dd4adcb171001ce768fc2edf041');
        $this->debug($clientToken = Braintree_ClientToken::generate());
    }

}
?>
