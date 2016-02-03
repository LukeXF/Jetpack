<?php

// Handles site payments, currently only PayPal
class Store extends siteFunctions
{

    public function generateToken()
    {
        require("lib/braintree-php-3.5.0/lib/Braintree.php");
        Braintree_Configuration::environment('sandbox');
        Braintree_Configuration::merchantId('w7gz99fhtbrhj6y6');
        Braintree_Configuration::publicKey('p5hhp9ctz3znhsq3');
        Braintree_Configuration::privateKey('2baa5dd4adcb171001ce768fc2edf041');
        $clientToken = Braintree_ClientToken::generate();
        return $clientToken;
    }

    public function pay($nonce){

        global $_SESSION;
        $result = Braintree_Transaction::sale([
            'amount' => '10.00',
            'paymentMethodNonce' => $nonce,
            'shipping' => [
                'firstName' => $_SESSION['user_first_name'],
                'lastName' => $_SESSION['user_last_name'],
                'company' => 'Braintree',
                'streetAddress' => '1 E 1st St',
                'extendedAddress' => 'Suite 403',
                'locality' => 'Bartlett',
                'region' => 'IL',
                'postalCode' => '60103',
                'countryCodeAlpha2' => 'US'
            ],
            'options' => [
                'submitForSettlement' => true
            ]
        ]);

        $this->debug($result);
    }

    /*
    * Loads all user data from table
    */
    private function getAllVisibleProducts($product_id = false)
    {
        // if database connection opened
        if ($this->databaseConnection()) {

            if ($product_id) {
                $sql = $this->db_connection->prepare("SELECT * FROM `products` WHERE `product_id` = :product_id");
                $sql->bindValue(':product_id', $product_id, PDO::PARAM_INT);
            } else {
                $sql = $this->db_connection->prepare("SELECT * FROM `products` WHERE `product_visibility` != 0");
            }

            // load pages for the user
            $sql->execute();

            // fetch all from the widget
            $sql = $sql->fetchAll();

            // $this->debug($sql);

            if (isset($sql)) {
                return $sql;
            } else {
                return false;
            }

        } else {

            return false;

        }
    }

    public function displayStore()
    {
        global $currency;
        $products = $this->getAllVisibleProducts();

        // $this->debug($products);

        if ($products != false) {

            for ($i = 0; $i < count($products); $i++) {

                $urlArray = array(
                    "product" => $products[$i]['product_id'],
                    "name" => $this->addDashes($products[$i]['product_name'])
                );
                echo "
                    <div class='col-md-6 tile-store'>
                        <div class='tile'>
                            <div class='row'>
                                <div class='col-xs-3 col-md-5'>
                                   <img src='" . $products[$i]['product_image_one'] . "'>
                                </div>
                                <div class='col-xs-9 col-md-7'>
                                    <h3>" . $products[$i]['product_name'] . " <span>" . $currency . $products[$i]['product_price'] . "</span></h3>
                                    <p>" . $this->truncate($products[$i]['product_description'], 180) . "</p>
                                    <a href='" . $this->url("store", $urlArray ) . "' type='button' class='btn btn-default'>
                                        <i class='btl bt-shopping-cart'></i> More details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                ";
            }
        }
    }

    public function displayProduct($id){

        global $currency;
        $products = $this->getAllVisibleProducts($id);

        if ($products != false) {
            echo "
                    <div class='col-md-12 tile-store'>
                        <div class='tile'>
                            <div class='row'>
                                <div class='col-md-5'>
                                   <img src='" . $products[0]['product_image_one'] . "'>
                                </div>
                                <div class='col-md-7'>
                                    <h1><b>" . $products[0]['product_name'] . "</b></h1>
                                    <h3>" . $currency . $products[0]['product_price'] . "</h3>
                                    <p>" . $this->truncate($products[0]['product_description'], 180) . "</p>

                                    ";

            if (isset($_POST['payment_method_nonce'])) {
                $store->pay($_POST['payment_method_nonce']);
            }

            $this->orderForm($products);

            echo "
                                </div>
                            </div>
                        </div>
                    </div>
            ";
        } else {
            $this->callbackMessage("Product not found", "danger");
        }
    }

    private function orderForm($products){
        echo '

            <form method="post" action="' . $this->url("processing") . '" name="process">
                <div id="payment-form">
                    <b>Purchase</b>
                    <div class="row">
                        <div class="col-md-4">
                            <input name="data[quantity]" placeholder="Amount" value="1" type="text">
                        </div>
                        <div class="col-md-8">
                            <input name="data[option]" readonly placeholder="Amount" value="Standard Size" type="text">
                        </div>
                        <div class="col-md-12">
                            <input name="data[product_id]" type="hidden" value="' . $products[0]['product_id'] . '">
                            <button type="submit" class="btn btn-black" name="process" value="addToCart" class="btn btn-default">Add to Cart</button>
                        </div>
                    </div>
                </div>
            </form>';

    }

    public function checkoutForm($amount){
        echo '

          <script src="https://js.braintreegateway.com/v2/braintree.js"></script>
            <form id="checkout" method="post">
                <div id="payment-form"></div>
                <input type="submit" value="Pay ' . $amount . '">
            </form>

            <script>
                var clientToken = "' . $this->generateToken() . '";
                braintree.setup(clientToken, "dropin", { container: "payment-form" });
            </script>
';
    }

    public function addToCart($data){

        global $_SESSION;
        $products = $this->getAllVisibleProducts($data["product_id"]);


        $cartItem["product_id"] = $products[0]["product_id"];
        $cartItem["product_name"] = $products[0]["product_name"];
        $cartItem["product_price"] = $products[0]["product_price"];
        $cartItem["product_quantity"] = $data["quantity"];

        if (!isset($_SESSION['cart'][0])) {
            $_SESSION['cart'] = array();
        }

        array_push($_SESSION['cart'], $cartItem);

    }

    public function navbarCart(){

        global $currency;
        global $_SESSION;
        // unset($_SESSION['cart']);
        $totalPrice = 0;
        $totalProducts = 0;

        if(isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $product) {

                $i = 0;
                if ($product['product_quantity'] > 1) {
                    while ($i < $product['product_quantity']) {
                        $i++;
                        $totalPrice += $product['product_price'];
                        $totalProducts++;
                    }
                } else {
                    $totalPrice += $product['product_price'];
                    $totalProducts++;
                }
            }
        }

        echo '

            <a class="nav-checkout">
                <b>' . $currency . number_format($totalPrice, 2) . '</b><br>
                ' . $totalProducts . ' Item
            </a>
        ';

    }

    public function displayCart() {

        global $currency;
        global $_SESSION;
        // unset($_SESSION['cart']);

        $totalPrice = 0;
        $totalProducts = 0;

        if(isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $product) {

                $i = 0;
                if ($product['product_quantity'] > 1) {
                    while ($i < $product['product_quantity']) {
                        $i++;
                        $totalPrice += $product['product_price'];
                        $totalProducts++;
                    }
                } else {
                    $totalPrice += $product['product_price'];
                    $totalProducts++;
                }
            }

            $checkout = $_SESSION['cart'];
            // $this->debug($_SESSION['cart']);

            echo '<table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th data-sort="string"></th>
                        <th data-sort="string"></th>
                        <th data-sort="string">Product Name</th>
                        <th data-sort="string">Price</th>
                        <th data-sort="string">Orders Placed</th>
                        <th data-sort="string">Total Price</th>
                    </tr>
                </thead>
                <tbody>';


            for ($i = 0; $i < count($checkout); $i++) {

                $product = $this->getAllVisibleProducts($checkout[$i]['product_id'])[0];
                echo "
                    <tr>
                        <td>" . "</td>
                        <td><img src='" . $product['product_image_one'] . "'></td>
                        <td>" . $product['product_name'] . "</td>
                        <td>" . $currency . number_format($product['product_price'], 2) . "</td>
                        <td>" . $checkout[$i]['product_quantity'] . "</td>
                        <td>" . $currency . number_format($product['product_price'] * $checkout[$i]['product_quantity'], 2) . "</td>
                    </tr>
                 ";
            }

            echo "

                        <tr class='table-footer'>
                            <td colspan='6'>
                               <a href='" . $this->url("checkout") . "' class='btn btn-black'><b>Continue to checkout</b></a>
                            </td>
                        </tr>
                   </tbody>
                </table>

                ";

            // $this->debug($product);

            $this->grandTotal($totalProducts, $totalPrice);
        } else {

        }
    }

    private function grandTotal($amountOfItems, $pricing) {
        global $currency;
        global $_SESSION;

        echo '
            <div class="row">
                <div class="col-md-6 col-md-offset-6">
                <h1><b>Final Totals:</b></h1>
                    <table class="table table-bordered">
                        <tr>
                            <td><b>Sub Total:</b></td>
                            <td class="table-grey">' . $currency . number_format($pricing, 2) . '</td>
                        </tr>
                        <tr>
                            <td><b>Shipping:</b></td>
                            <td class="table-grey">' . $currency . number_format($amountOfItems * 5.45, 2) . '</td>
                        </tr>
                        <tr>
                            <td><b>Grand Total:</b></td>
                            <td class="table-grey">' . $currency . number_format($amountOfItems * 5.45 + $pricing, 2) . '</td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <a href="' . $this->url("checkout") . '" class="btn btn-black"><b>Continue to checkout</b></a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        ';
    }

    public function displayCheckout(){

        global $_SESSION;
        global $avatar;

        if(isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == 1) {
            echo '
                <div class="tile tile-checkout">
                    <div class="tile-padding">
                        <div class="row logged-in">
                            <div class="col-md-2">
                                <img class="img-circle" src="' . $avatar . '">
                            </div>
                            <div class="col-md-9">
                                <h3>
                                     Welcome Back ' . $_SESSION['user_first_name'] . ',
                                </h3>
                                <h5>
                                    You will be able to track all stages of your orders because you are logged in.
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tile tile-checkout">
                    <div class="tile-padding">
                        <div class="row logged-in">
                            <div class="col-md-2">
                                <span class="bt-stack bt-5x">
                                  <i class="btl bt-circle bt-stack-1x"></i>
                                  <i class="btl bt-gift bt-stack-sm"></i>
                                </span>
                            </div>
                            <div class="col-md-6">
                                <h3>
                                     Forgot to add your coupon code?
                                </h3>
                                <h5>
                                    Click <a data-toggle="modal" data-target="#addCoupon">here</a> to apply an active code.
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal fade" id="addCoupon" tabindex="-1" role="dialog" aria-labelledby="addCoupon">
                    <div class="modal-dialog" role="document">
                        <form method="post" action="' . $this->url("processing") . '" name="process">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h3 class="modal-title" id="myModalLabel">Add Coupon</h3>
                                    <p>If you have been given an coupon please apply it here. Please note you can only apply one coupon per order and is subject to fair use.</p>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p>Username</p>
                                        </div>
                                        <div class="col-md-8">
                                            <input name="data[user_name]" value=""" . $user["user_name"] . "" />
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-black" name="process" value="updateUsersData" class="btn btn-default">Save Changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


            ';
        }
        $this->debug();
    }
}

?>
