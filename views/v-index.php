<div class="container">
    <div class="row">
        <?php
        // $weather->getWeather();
        //$store->generateToken($_POST['payment_method_nonce']);

        $siteFunctions->debug();
        $token = $store->generateToken();
        $store->pay($_POST['payment_method_nonce']);
        ?>

        <div class="col-md-9">
            <?php $siteFunctions->displayCallbackMessage(); ?>
          <script src="https://js.braintreegateway.com/v2/braintree.js"></script>
            <form id="checkout" method="post">
                <div id="payment-form"></div>
                <input type="submit" value="Pay $10">
            </form>

            <script src="https://js.braintreegateway.com/v2/braintree.js"></script>
            <script>
                // We generated a client token for you so you can test out this code
                // immediately. In a production-ready integration, you will need to
                // generate a client token on your server (see section below).
                var clientToken = '<?php echo $token; ?>';

                braintree.setup(clientToken, "dropin", {
                    container: "payment-form"
                });
            </script>
        </div>
        <hr>
    </div>

</div>
