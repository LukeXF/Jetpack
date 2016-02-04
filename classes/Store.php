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

    public function pay($nonce)
    {

        global $_SESSION;
        return Braintree_Transaction::sale([
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
                                    <a href='" . $this->url("store", $urlArray) . "' type='button' class='btn btn-default'>
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

    public function displayProduct($id)
    {

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

    private function orderForm($products)
    {
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

    public function checkoutForm($amount)
    {
        echo '

          <script src="https://js.braintreegateway.com/v2/braintree.js"></script>
            <form id="checkout" method="post">
                <div id="payment-form"></div>
                <input type="hidden" name="test" value="test">
                <input type="hidden" name="billingAddress" id="billingAddress">
                <input type="hidden" name="shippingAddress" id="shippingAddress">
                <input type="submit" value="Pay ' . $amount . '">
            </form>

            <script>
                var clientToken = "' . $this->generateToken() . '";
                braintree.setup(clientToken, "dropin", { container: "payment-form" });
            </script>
';
    }

    public function addToCart($data)
    {

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

    public function navbarCart()
    {

        global $currency;
        global $_SESSION;
        // unset($_SESSION['cart']);
        $totalPrice = 0;
        $totalProducts = 0;

        if (isset($_SESSION['cart'])) {
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

    public function displayCart()
    {

        global $currency;
        global $_SESSION;
        // unset($_SESSION['cart']);

        $totalPrice = 0;
        $totalProducts = 0;

        if (isset($_SESSION['cart'])) {
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
                        <td><a href='" . $this->url("processing", array("removeItem" => $i)) . "'><i class='btl bt-times bt-sm'></i></a></td>
                        <td><img src='" . $product['product_image_one'] . "'></td>
                        <td>" . $product['product_name'] . "</td>
                        <td>" . $currency . number_format($product['product_price'], 2) . "</td>
                        <td><input class='quantity' value='" . $checkout[$i]['product_quantity'] . "'></td>
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
            echo "
                <div class='tile'>
                    <div class='tile-padding'>
                        <h3>Oh dear, it looks like your cart is a bit empty.</h3>
                        <a href='" . $this->url("store") . "' class='btn btn-black'><b>Continue back to shop</b></a>
                    </div>
                </div>
            ";
        }
    }

    private function grandTotal($amountOfItems, $pricing, $onCheckoutPage = false)
    {
        global $currency;
        global $_SESSION;
        $checkout = $_SESSION['cart'];
        if ($onCheckoutPage) {
            echo '
            <div class="row">
                <div class="col-md-12">
                <h1><b>Final Totals:</b></h1>
                    <table class="table table-bordered">';


            for ($i = 0; $i < count($checkout); $i++) {

                $product = $this->getAllVisibleProducts($checkout[$i]['product_id'])[0];
                echo '
                    <tr>
                        <td>' . $product['product_name'] . ' <b>x' . $checkout[$i]['product_quantity'] . '</b>
                        <br>' . $currency . $checkout[$i]['product_price'] . ' each
                        </td>
                        <td>' . $currency . number_format($product['product_price'] * $checkout[$i]['product_quantity'], 2) . '</td>
                    </tr>
                 ';
            }

            echo '
                        <tr style="border-top: 2px solid #DDD;">
                            <td><b>Sub Total:</b>

                            </td>
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
            ';
            $this->checkoutForm($currency . number_format($amountOfItems * 5.45 + $pricing, 2));
            echo '
                            </td>
                        </tr>
                    </table>
                </div>
            </div>';

        } else {

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
            </div>';
        }
    }

    public function displayCheckout()
    {

        global $_SESSION;
        global $avatar;

        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == 1) {
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
            ';

            $this->displayCouponTile();


            $totalPrice = 0;
            $totalProducts = 0;

            if (isset($_SESSION['cart'])) {
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

                $this->displayAddresses();

                $this->grandTotal($totalProducts, $totalPrice, 1);
            }
        }
    }

    public function removeItemFromCart($item)
    {
        global $_SESSION;
        unset($_SESSION['cart'][$item]);

        // if the last item was removed from cart, remove cart
        if (!$_SESSION['cart']) {
            $this->callbackMessage("Final item successfully removed from cart, your cart is now empty", "success");
            unset($_SESSION['cart']);
        } else {
            $this->callbackMessage("Item successfully removed", "success");
        }
    }

    public function applyCoupon($coupon)
    {

        $sql = $this->checkValidCoupon($coupon);

        if ($sql) {
            $this->debug($sql);
            echo $currentDate = date('Y-m-d H:i:s', time());

            if ($currentDate < $sql[0]['coupon_date_start']) {
                unset($_SESSION['cart_coupon']);
                $this->callbackMessage("You coupon code was found, but it isn't valid yet", "info");
            } elseif ($currentDate > $sql[0]['coupon_date_end']) {
                unset($_SESSION['cart_coupon']);
                $this->callbackMessage("You coupon code was found, but it is no longer valid", "info");
            } else {
                $this->callbackMessage("You coupon has been applied", "success");

                global $_SESSION;
                $_SESSION['cart_coupon']['code'] = $sql[0]['coupon_code'];
                $_SESSION['cart_coupon']['amount'] = $sql[0]['coupon_amount'];
                $_SESSION['cart_coupon']['is_percentage'] = $sql[0]['coupon_is_percent'];
            }

        } else {
            unset($_SESSION['cart_coupon']);
            $this->callbackMessage("Your coupon code was not found, sorry", "danger");
        }
    }

    private function checkValidCoupon($coupon)
    {
        // if database connection opened
        if ($this->databaseConnection()) {

            $sql = $this->db_connection->prepare("SELECT * FROM `coupons` WHERE `coupon_code` = :coupon_code");
            $sql->bindValue(':coupon_code', $coupon, PDO::PARAM_STR);

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

    private function displayCouponTile()
    {

        global $_SESSION;
        global $currency;

        echo '
            <div class="tile tile-checkout">
                <div class="tile-padding">
                    <div class="row logged-in">
                        <div class="col-md-2">
                            <span class="bt-stack bt-5x">
                              <i class="btl bt-circle bt-stack-1x"></i>
                              <i class="btl bt-gift bt-stack-sm"></i>
                            </span>
                        </div>
        ';

        if (isset($_SESSION['cart_coupon'])) {

            if ($_SESSION['cart_coupon']['is_percentage']) {
                $discount = $_SESSION['cart_coupon']['amount'] . "%";
            } else {
                $discount = $currency . $_SESSION['cart_coupon']['amount'];
            }
            echo '
                <div class="col-md-8">
                    <h3>
                         Code <b>' . $_SESSION['cart_coupon']['code'] . '</b> has been added to your checkout.
                    </h3>
                    <h5>
                        ' . $discount . ' has been remove from your total. click <a data-toggle="modal" data-target="#addCoupon">here</a> to change the code.
                    </h5>
                </div>

                <div class="modal fade" id="addCoupon" tabindex="-1" role="dialog" aria-labelledby="addCoupon">
                    <div class="modal-dialog" role="document">
                        <form method="post" action="' . $this->url("processing") . '" name="process">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h3 class="modal-title" id="myModalLabel">Change Coupon</h3>
                                    <p>is the previous code not working out for you? Change it below or leave it blank then click the button to remove the code.</p>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p>Coupon</p>
                                        </div>
                                        <div class="col-md-8">
                                            <input name="coupon" />
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-black" name="process" value="applyCoupon" class="btn btn-default">Apply Discount</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            ';
        } else {
            echo '

                        <div class="col-md-6">
                            <h3>
                                 Have a coupon code?
                            </h3>
                            <h5>
                                Click <a data-toggle="modal" data-target="#addCoupon">here</a> to apply an active code.
                            </h5>
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
                                            <p>Coupon</p>
                                        </div>
                                        <div class="col-md-8">
                                            <input name="coupon" />
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-black" name="process" value="applyCoupon" class="btn btn-default">Apply Discount</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            ';
        }

        echo '
                    </div>
                </div>
            </div>
        ';
    }

    private function displayAddresses()
    {
        echo '
        <div class="row">
            <div class="col-md-12">
                <div class="tile tile-checkout">
                    <div class="tile-padding">
                        <b>Billing Address</b> (Select One)
                        <div class="row">
        ';
        $this->displayAddress(true);
        echo '
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="tile tile-checkout">
                    <div class="tile-padding">
                        <b>Shipping Address</b> (Select One)
                        <div class="row">
        ';
        $this->displayAddress();
        echo '
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ';
    }

    public function displayAddress($billing = false)
    {

        global $_SESSION;

        if ($billing) {
            $addresses = $this->getAddress("Billing");
            $account_type = "billing-tile";
            $jQuery_function = "billingSelect";
            echo '
                <script>
                    function ' . $jQuery_function . '(id) {
                         // alert(".address_id" + id);
                         $(".' . $account_type . '").removeClass("selected");
                         $(".' . $account_type . '.address_id" + id).addClass("selected");
                         $( "#billingAddress" ).val(id);
                    }
                </script>
            ';
        } else {
            $addresses = $this->getAddress("Shipping");
            $account_type = "shipping-tile";
            $jQuery_function = "shippingSelect";
            echo '
                <script>
                    function ' . $jQuery_function . '(id) {
                         // alert(".address_id" + id);
                         $(".' . $account_type . '").removeClass("selected");
                         $(".' . $account_type . '.address_id" + id).addClass("selected");
                         $( "#shippingAddress" ).val(id);
                    }
                </script>
            ';
        }

        for ($i = 0; $i < count($addresses); $i++) {

            // $this->debug($addresses[$i]);
            echo '
            <div class="col-xs-6 col-sm-4">
                <div class="tile-address ' . $account_type . ' address_id' . $addresses[$i]['address_id'] . '" data-placement="top" onclick="' . $jQuery_function . '(' . $addresses[$i]['address_id'] . ')" title="Use this address" data-tooltip="tooltip">
                    <b>Address ' . ($i + 1) . ':</b>
                    <br>
                    ' . $addresses[$i]['address_first_name'] . '
                    ' . $addresses[$i]['address_last_name'] . ',
                    ' . $addresses[$i]['address_company_name'] . '<br>
                    ' . $addresses[$i]['address_apartment'] . '
                    ' . $addresses[$i]['address_street'] . '<br>
                    ' . $addresses[$i]['address_city'] . ',
                    ' . $addresses[$i]['address_county'] . '<br>
                    ' . $addresses[$i]['address_postcode'] . '<br>
                    ' . $addresses[$i]['address_country'] . '<br>
                </div>

                <div class="options">
                    <i data-placement="bottom" onclick="' . $jQuery_function . '(' . $addresses[$i]['address_id'] . ')" title="Use this address" data-tooltip="tooltip" class="btl bt-check-circle bt-lg"></i>
                    <i data-placement="bottom" title="Remove this address" data-toggle="modal" data-tooltip="tooltip" data-target="#removeAddress" class="btl bt-times-circle bt-lg"></i>
                    <i data-placement="bottom" title="Edit this address"   data-toggle="modal" data-tooltip="tooltip" data-target="#editExistingAddress' . $addresses[$i]['address_id'] . '" class="btl bt-pencil bt-lg"></i>
                </div>
            </div>
            ';

            $this->editExistingAddressModal($addresses[$i]);
        }
        echo '

            <div class="col-xs-6 col-sm-4">
                <div class="tile-address address-new" data-placement="bottom" title="Add new address" data-toggle="modal" data-tooltip="tooltip" data-target="#addNewAddress">
                    <i class="btl bt-plus bt-5x"></i>
                </div>
            </div>
        ';

        $this->addNewAddressModal();
    }

    private function editExistingAddressModal($data)
    {

        if ($data['address_type'] == "Billing"){
            $address_type_dropdown = '
                <option value="Billing" selected>Billing (current)</option>
                <option value="Shipping">Shipping</option>
                <option value="Billing & Shipping">Billing & Shipping</option>
            ';
        } elseif ($data['address_type'] == "Shipping"){
            $address_type_dropdown = '
                <option value="Billing">Billing</option>
                <option value="Shipping" selected>Shipping (current)</option>
                <option value="Billing & Shipping">Billing & Shipping</option>
            ';
        } elseif ($data['address_type'] == "Billing & Shipping"){
        $address_type_dropdown = '
                <option value="Billing">Billing</option>
                <option value="Shipping">Shipping</option>
                <option value="Billing & Shipping" selected>Billing & Shipping (current)</option>
            ';
    }
        echo "

        <div class='modal fade' id='editExistingAddress" . $data['address_id'] . "' tabindex='-1' role='dialog' aria-labelledby='editExistingAddress'>
            <div class='modal-dialog' role='document'>
                <form method='post' action='" . $this->url("processing") . "' name='process'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                            <h3 class='modal-title' id='myModalLabel'>Edit Address</h3>
                        </div>
                        <div class='modal-body'>
                            <div class='row'>
                                <div class='col-md-4'><p>First Name</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_first_name]' value='" . $data['address_first_name'] . "'/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Last Name</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_last_name]' value='" . $data['address_last_name'] . "'/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Company Name</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_company_name]' value='" . $data['address_company_name'] . "'/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Apartment No.</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_apartment]' value='" . $data['address_apartment'] . "'/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Street</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_street]' value='" . $data['address_street'] . "'/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>City</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_city]' value='" . $data['address_city'] . "'/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Region</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_county]' value='" . $data['address_county'] . "'/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Postal Code</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_postcode]' value='" . $data['address_postcode'] . "'/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Country</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_country]' value='" . $data['address_country'] . "'/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Address Type</p></div>
                                <div class='col-md-8'>
                                    <select name='data[address_type]' class='form-control'>
                                    ' . $address_type_dropdown . '
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class='modal-footer'>

                            <input type='hidden' name='data[address_id]' value='" . $data['address_id'] . "'>
                            <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                            <button type='submit' class='btn btn-black' name='process' value='editExistingAddress' class='btn btn-default'>Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        ";
    }

    private function addNewAddressModal()
    {
        echo "

        <div class='modal fade' id='addNewAddress' tabindex='-1' role='dialog' aria-labelledby='addNewAddress'>
            <div class='modal-dialog' role='document'>
                <form method='post' action='" . $this->url("processing") . "' name='process'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                            <h3 class='modal-title' id='myModalLabel'>Add Address</h3>
                            <p>Here you can add a new address to the system which can be used next time you come to checkout. Select at the bottom if you want this address to appear for just billing, just shipping or both.</p>
                        </div>
                        <div class='modal-body'>
                            <div class='row'>
                                <div class='col-md-4'><p>First Name</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_first_name]' value='" . $_SESSION['user_first_name'] . "'/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Last Name</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_last_name]' value='" . $_SESSION['user_last_name'] . "'/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Company Name</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_company_name]' placeholder=''/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Apartment No.</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_apartment]' placeholder=''/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Street</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_street]' placeholder=''/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>City</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_city]' placeholder=''/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Region</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_county]' placeholder=''/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Postal Code</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_postcode]' placeholder=''/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Country</p></div>
                                <div class='col-md-8'>
                                    <input name='data[address_country]' placeholder=''/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'><p>Address Type</p></div>
                                <div class='col-md-8'>
                                    <select name='data[address_type]' class='form-control'>
                                          <option>Billing</option>
                                          <option>Shipping</option>
                                          <option>Billing & Shipping</option>
                                    </select>
                                </div>
                            </div>

                            <input type='hidden' name='data[user_id]' value='" . $_SESSION['user_id'] . "'>
                        </div>
                        <div class='modal-footer'>
                            <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                            <button type='submit' class='btn btn-black' name='process' value='addNewAddress' class='btn btn-default'>Add Address</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        ";
    }

    private function getAddress($address_type)
    {
        // if database connection opened
        if ($this->databaseConnection()) {

            if ($address_type) {
                $sql = $this->db_connection->prepare("SELECT * FROM `addresses` WHERE `address_type` = :address_type OR `address_type` = 'Billing & Shipping'");
                $sql->bindValue(':address_type', $address_type, PDO::PARAM_STR);
            }

            // load pages for the user
            $sql->execute();
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

    public function addNewAddress($data)
    {
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('INSERT INTO addresses
(address_user, address_first_name, address_last_name, address_company_name, address_street, address_apartment, address_city, address_county, address_postcode, address_type, address_country)
VALUES(:address_user, :address_first_name, :address_last_name, :address_company_name, :address_street, :address_apartment, :address_city, :address_county, :address_postcode, :address_type, :address_country)');
            // prepared statement for the username field
            $sql->bindValue(':address_user', $data['user_id'], PDO::PARAM_INT);
            $sql->bindValue(':address_first_name', $data['address_first_name'], PDO::PARAM_STR);
            $sql->bindValue(':address_last_name', $data['address_last_name'], PDO::PARAM_STR);
            $sql->bindValue(':address_company_name', $data['address_company_name'], PDO::PARAM_STR);
            $sql->bindValue(':address_street', $data['address_street'], PDO::PARAM_STR);
            $sql->bindValue(':address_apartment', $data['address_apartment'], PDO::PARAM_STR);
            $sql->bindValue(':address_city', $data['address_city'], PDO::PARAM_STR);
            $sql->bindValue(':address_county', $data['address_county'], PDO::PARAM_STR);
            $sql->bindValue(':address_postcode', $data['address_postcode'], PDO::PARAM_STR);
            $sql->bindValue(':address_type', $data['address_type'], PDO::PARAM_STR);
            $sql->bindValue(':address_country', $data['address_country'], PDO::PARAM_STR);
            // execute it all!
            $sql->execute();


            // get the last entered ID and set it to variable user_id for use in the verification link sent via email
            $user_id = $this->db_connection->lastInsertId();


            if ($sql) {
                $this->callbackMessage("Address has been added to the system", "success");
            } else {
                $this->callbackMessage("Address has not been added", "danger");
            }
        }
    }

    public function editExistingAddress($data)
    {
        if ($this->databaseConnection()) {

            $sql = $this->db_connection->prepare('UPDATE `addresses`
            SET `address_first_name` = :address_first_name,
                `address_last_name` = :address_last_name,
                `address_company_name` = :address_company_name,
                `address_street` = :address_street,
                `address_apartment` = :address_apartment,
                `address_city` = :address_city,
                `address_county` = :address_county,
                `address_postcode` = :address_postcode,
                `address_type` = :address_type,
                `address_country` = :address_country
            WHERE `address_id` = :address_id');

            $sql->bindValue(':address_id', $data['address_id'], PDO::PARAM_STR);
            $sql->bindValue(':address_first_name', $data['address_first_name'], PDO::PARAM_STR);
            $sql->bindValue(':address_last_name', $data['address_last_name'], PDO::PARAM_STR);
            $sql->bindValue(':address_company_name', $data['address_company_name'], PDO::PARAM_STR);
            $sql->bindValue(':address_street', $data['address_street'], PDO::PARAM_STR);
            $sql->bindValue(':address_apartment', $data['address_apartment'], PDO::PARAM_STR);
            $sql->bindValue(':address_city', $data['address_city'], PDO::PARAM_STR);
            $sql->bindValue(':address_county', $data['address_county'], PDO::PARAM_STR);
            $sql->bindValue(':address_postcode', $data['address_postcode'], PDO::PARAM_STR);
            $sql->bindValue(':address_type', $data['address_type'], PDO::PARAM_STR);
            $sql->bindValue(':address_country', $data['address_country'], PDO::PARAM_STR);
            $sql->execute();

            if ($sql) {
                $this->callbackMessage("Address has been updated", "success");
            } else {
                $this->callbackMessage("Address has not been updated, maybe no changes have been made?", "info");
            }
        }
    }

}
?>
