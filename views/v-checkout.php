<div class="container">
    <div class="tile-checkout col-md-8 col-md-offset-2">
        <h1><b>Checkout</b></h1>
        <?php
            $siteFunctions->displayCallbackMessage();

            if (!isset($_POST['payment_method_nonce'])) {
                $store->displayCheckout();
                $siteFunctions->debug();
            } else {
                $store->generateToken();
                $result = $store->pay($_POST['payment_method_nonce']);
                $siteFunctions->debug($result);
                $siteFunctions->debug();

                if ($result->success) {
                    print_r("Success ID: " . $result->transaction->id);
                } else {
                    print_r("Error Message: " . $result->message);
                }
            }

        ?>
    </div>
</div>