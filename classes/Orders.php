<?php

// Handles site payments, currently only PayPal
class Orders extends siteFunctions
{

    private function getOrders($userOrOrderID, $displayOpenOrders = false)
    {
        // if database connection opened
        if ($this->databaseConnection()) {

            if ($displayOpenOrders) {
                $sql = $this->db_connection->prepare("SELECT * FROM `orders` WHERE `order_user` = :order_user");
                $sql->bindValue(':order_user', $userOrOrderID, PDO::PARAM_INT);
            } else {
                $sql = $this->db_connection->prepare("SELECT * FROM `orders` WHERE `order_braintree_id` = :order_braintree_id");
                $sql->bindValue(':order_braintree_id', $userOrOrderID, PDO::PARAM_STR);
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

    private function getOrderItems($braintree_id)
    {
        // if database connection opened
        if ($this->databaseConnection()) {

            $sql = $this->db_connection->prepare("SELECT * FROM `order_items` WHERE `items_order_id` = :braintree_id");
            $sql->bindValue(':braintree_id', $braintree_id, PDO::PARAM_STR);

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

    public function insertOrder($braintreeResponse, $data) {

        if ($braintreeResponse->success){

            global $_SESSION;

            // $paymentId   = rand(0, 20000);
            // $paymentType = "test";

            $paymentType = $braintreeResponse->transaction->paymentInstrumentType;
            $paymentId   = $braintreeResponse->transaction->id;

            $this->debug($data, "Cart debug");

            $shippingPrice = 5.45;
            $shipping = 0;
            $itemsTotal = 0;

            for ($i = 0; $i < count($_SESSION['cart']); $i++) {

                $x = 0;
                while ($x < $_SESSION['cart'][$i]['product_quantity']) {
                    $shipping = $shipping + $shippingPrice;
                    $itemsTotal = $itemsTotal + $_SESSION['cart'][$i]['product_price'];
                    $x++;
                }
            }

            $this->addNewOrder($data, $paymentId, $paymentType, $shipping, $itemsTotal);

            for ($i = 0; $i < count($_SESSION['cart']); $i++) {
                $this->debug($_SESSION['cart'][$i]);
                $this->addItemsToOrder($_SESSION['cart'][$i], $paymentId);
            }

            unset($_SESSION['cart']);


            $this->callbackMessage("Your order #" . $paymentId . " has been successfully placed, we will review it and keep you updated.", "success");

        } else {

            $this->callbackMessage("Error Message: " . $braintreeResponse->message, "danger");
        }

    }

    public function addNewOrder($postData, $braintreeId, $braintreePaymentType, $shipping, $subtotal) {

        global $_SESSION;
        $grandtotal = $shipping + $subtotal;

        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('INSERT INTO orders
(order_braintree_id, order_user, order_payment_type, order_sub_total, order_shipping_total, order_grand_total, order_shipping_address, order_billing_address)
VALUES(:order_braintree_id, :order_user, :order_payment_type, :order_sub_total, :order_shipping_total, :order_grand_total, :order_shipping_address, :order_billing_address)');
            // prepared statement for the username field
            $sql->bindValue(':order_braintree_id', $braintreeId, PDO::PARAM_STR);
            $sql->bindValue(':order_payment_type', $braintreePaymentType, PDO::PARAM_STR);
            $sql->bindValue(':order_sub_total', $subtotal, PDO::PARAM_STR);
            $sql->bindValue(':order_shipping_total', $shipping, PDO::PARAM_STR);
            $sql->bindValue(':order_grand_total', $grandtotal, PDO::PARAM_STR);
            $sql->bindValue(':order_user', $_SESSION['user_id'], PDO::PARAM_INT);
            $sql->bindValue(':order_shipping_address', $postData['shippingAddress'], PDO::PARAM_INT);
            $sql->bindValue(':order_billing_address', $postData['billingAddress'], PDO::PARAM_INT);
            // execute it all!
            $sql->execute();


            // get the last entered ID and set it to variable user_id for use in the verification link sent via email
            $order_id = $this->db_connection->lastInsertId();

        }
    }

    public function addItemsToOrder($data, $braintreeID) {

        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('INSERT INTO order_items
(items_order_id, items_product_id, items_name, items_price, items_quantity)
VALUES(:items_order_id, :items_product_id, :items_name, :items_price, :items_quantity)');
            // prepared statement for the username field
            $sql->bindValue(':items_order_id', $braintreeID, PDO::PARAM_STR);
            $sql->bindValue(':items_product_id', $data['product_id'], PDO::PARAM_INT);
            $sql->bindValue(':items_name', $data['product_name'], PDO::PARAM_STR);
            $sql->bindValue(':items_price', $data['product_price'], PDO::PARAM_STR);
            $sql->bindValue(':items_quantity', $data['product_quantity'], PDO::PARAM_STR);
            // execute it all!
            $sql->execute();


            // get the last entered ID and set it to variable user_id for use in the verification link sent via email
            $user_id = $this->db_connection->lastInsertId();

        }
    }

    public function displayOrders($specificOrder = false)
    {

        global $_SESSION;
        global $currency;

        if (!$specificOrder) {
            $orders = $this->getOrders($_SESSION['user_id'], true);

            // $this->debug($orders);
            if ($orders != false) {
                echo '<table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th data-sort="string">Order ID</th>
                        <th data-sort="string">Username</th>
                        <th data-sort="string">Order Date</th>
                        <th data-sort="string">Order Status</th>
                        <th data-sort="int">Order Total</th>
                        <th data-sort="string">Payment Method</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>';

                for ($i = 0; $i < count($orders); $i++) {

                    if ($orders[$i]['order_user'] != null) {
                        $orderUser = $this->getUserDataFromID($orders[$i]['order_user']);
                        $orderUser = "<td data-sort-value='" . $orders[$i]['order_user'] . "'> <img src='" . $this->getAvatar($orderUser->user_email) . "'>" . $orderUser->user_name . "</td>
                        ";
                    } else {
                        $orderUser = "<td>null</td>";
                    }

                    echo "
                    <tr>
                        <td>" . $orders[$i]['order_braintree_id'] . "</td>
                        " . $orderUser . "
                        <td>" . $orders[$i]['order_date'] . "</td>
                        <td>" . $orders[$i]['order_status'] . "</td>
                        <td data-sort-value='" . $orders[$i]['order_grand_total'] . "'>" . $currency . number_format($orders[$i]['order_grand_total'], 2) . "</td>
                        <td>" . $orders[$i]['order_payment_type'] . "</td>
                        <!-- Button trigger modal -->
                        <td>

                            <input type='hidden' name='process' value='loginAsUser'>
                            <input type='hidden' name='user_id' value='" . $orders[$i]['order_id'] . "'>
                            <div class='btn-group' role='group' aria-label='...'>
                                <a type='button' class='btn btn-default' href='" . $this->url("orders", array("product" => $orders[$i]['order_braintree_id'])) . "'><i class='btl bt-search'></i> View Order</a>
                            </div>
                        </td>
                    </tr>";
                }

                echo "

                    </tbody>
                </table>";


                for ($i = 0; $i < count($orders); $i++) {
                    // $this->editUserModal($orders[$i]);
                    // $this->godLoginModal($orders[$i]);
                    // $this->loginHistoryModal($orders[$i]);
                }

            } else {
                $this->callbackMessage("<b>Error loading users</b> There was an issue relating to the orders database, please check your database settings are correct and working", "danger");
            }

        } else {
            $orders = $this->getOrders($specificOrder)[0];
            $orderItems = $this->getOrderItems($specificOrder);
            $shippingAddress = $this->getAddress($orders['order_shipping_address'], true)[0];
            $billingAddress = $this->getAddress($orders['order_billing_address'], true)[0];


            // if the order does not belong to the user, redirect back to orders overview
            if ($_SESSION['user_id'] != $orders['order_user']) {

                // if admin
                if ($_SESSION['user_account_type'] == "admin") {
                    $userDetails = $this->getUserDataFromID($orders['order_user']);
                    echo '
                        <div class="row">
                            <div class="col-md-8 col-md-offset-2">
                                <div class="box">
                                ' .  '
                                    <img src="' . $this->getAvatar($userDetails->user_email) . '">
                                    Please note that you are logged in as an admin viewing an order belonging to ' . $userDetails->user_name . ' (' . $userDetails->user_email . ')
                                </div>
                            </div>
                        </div>
                    ';
                } else {
                    die($this->callback("orders"));
                }
            }


            echo '
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <h2 align="center"><b>Order ' . $specificOrder . '</b> - placed ' . $this->timeAgo($orders['order_date'], true) . '</h2>
                        <hr>
                        <div class="col-md-4">
                            <b>Order Details:</b><br>
                            Reference ID: ' . $orders['order_id'] .  $currency . '-' . $orders['order_braintree_id'] . '<br>
                            Sub Total: ' . $currency . number_format($orders['order_sub_total'], 2) . '<br>
                            Shipping Total: ' . $currency . number_format($orders['order_shipping_total'], 2) . '<br>
                            Grand Total: ' . $currency . number_format($orders['order_grand_total'], 2) . '<br>
                            Payment Type: ' . $orders['order_payment_type'] . '
                        </div>
                        <div class="col-md-4">
                            <b>Shipping Address:</b><br>
                            ' . $shippingAddress['address_first_name'] . '
                            ' . $shippingAddress['address_last_name'] . ',
                            ' . $shippingAddress['address_company_name'] . '<br>
                            ' . $shippingAddress['address_apartment'] . '
                            ' . $shippingAddress['address_street'] . '<br>
                            ' . $shippingAddress['address_city'] . ',
                            ' . $shippingAddress['address_county'] . '<br>
                            ' . $shippingAddress['address_postcode'] . '<br>
                            ' . $shippingAddress['address_country'] . '<br>
                        </div>
                        <div class="col-md-4">
                            <b>Order Address:</b><br>
                            ' . $billingAddress['address_first_name'] . '
                            ' . $billingAddress['address_last_name'] . ',
                            ' . $billingAddress['address_company_name'] . '<br>
                            ' . $billingAddress['address_apartment'] . '
                            ' . $billingAddress['address_street'] . '<br>
                            ' . $billingAddress['address_city'] . ',
                            ' . $billingAddress['address_county'] . '<br>
                            ' . $billingAddress['address_postcode'] . '<br>
                            ' . $billingAddress['address_country'] . '<br>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                    <hr>
                        '; $this->displayOrderItems($orderItems, $orders['order_shipping_total'], $orders['order_sub_total']); echo '
                    </div>
                </div>
            ';
        }
    }

    private function displayOrderItems($orderItems, $shippingTotal, $subTotal)
    {
        global $currency;
        global $_SESSION;
        echo '
        <div class="row tile-cart tile-cart-order">
            <div class="col-md-12">
                <table class="table table-bordered">';

        // $this->debug($orderItems);
        for ($i = 0; $i < count($orderItems); $i++) {

            $items = $orderItems[$i];

            echo '
                    <tr>
                        <td>' . $items['items_name'] . ' <b>x' . $items['items_quantity'] . '</b>
                        <br>' . $currency . $items['items_price'] . ' each
                        </td>
                        <td>' . $currency . number_format($items['items_price'] * $items['items_quantity'], 2) . '</td>
                    </tr>
             ';
        }

        echo '
                    <tr style="border-top: 2px solid #DDD;">
                        <td><b>Sub Total:</b>

                        </td>
                        <td class="table-grey">' . $currency . number_format($shippingTotal, 2) . '</td>
                    </tr>
                    <tr>
                        <td><b>Shipping:</b></td>
                        <td class="table-grey">' . $currency . number_format($subTotal, 2) . '</td>
                    </tr>
                    <tr>
                        <td><b>Grand Total:</b></td>
                        <td class="table-grey">' . $currency . number_format($shippingTotal + $subTotal, 2) . '</td>
                    </tr>
                </table>
            </div>
        </div>';
    }

    public function email($emailType, $postData)
    {
        global $brand;
        global $currency;

        $vars['user_name'] = $postData['emailData']['user_full_name'];
        $vars['user_id'] = $postData['emailData']['user_id'];
        $vars['user_email'] = $postData['emailData']['user_email'];

        if ($emailType == "dispatched") {
            $order_id = 'jm3dgj';

            $order_info = $this->getOrders($order_id);

            $vars['subject']    = $brand . ' - Order '  . strtoupper($order_id) . ' dispatched';
            $vars['to']         = array('email' => $vars['user_email'], 'name' => $vars['user_name']);
            $vars['intro']      = "Order Update";
            $vars['header']     = "Your order has been dispatched";
            $vars['text']       = "Hi again James, this is just a quick email to let you know that your order has now been dispatched. Click the button below to view it on our website.";
            $vars['link']       = $this->url("orders", array('product'=>$order_id));
            $vars['button']     = "View Order";
            $vars['shipping']   = $currency . $order_info[0]['order_shipping_total'];
            $vars['total']      = $currency . $order_info[0]['order_grand_total'];

            $order = $this->getOrderItems($order_id);
            for ($i = 0; $i < count($order); $i++) {
                $vars['orders'][$i]['item_image']       = $this->getProductInfo($order[$i]['items_product_id'])[0]['product_image_one'];
                $vars['orders'][$i]['item_quantity']    = $order[$i]['items_quantity'];
                $vars['orders'][$i]['item_name']        = $order[$i]['items_name'];
                $vars['orders'][$i]['item_price']       = $currency . $order[$i]['items_price'];
            }

        }

        $this->sendEmail($vars);
    }

    private function sendEmail($merge_vars) {
        require 'lib/mandrill/src/Mandrill.php';
        $mandrill = new Mandrill($GLOBALS['mandrillAPIKey']);
        $message = array(
            'subject' => $merge_vars['subject'],
            'from_email' => $GLOBALS['email'],
            'from_name' => 'The ' . $GLOBALS['brand'] . ' Team',
            'to' => array($merge_vars['to']),
            'merge_language' => 'handlebars',
            'global_merge_vars' => array(
                array('name' => 'intro', 'content' => $merge_vars['intro']),
                array('name' => 'header', 'content' => $merge_vars['header']),
                array('name' => 'text', 'content' => $merge_vars['text']),
                array('name' => 'link', 'content' => $merge_vars['link']),
                array('name' => 'button', 'content' => $merge_vars['button']),
                array("name" => "products", "content" => $merge_vars['orders']),
                array('name' => 'shipping', 'content' => $merge_vars['shipping']),
                array('name' => 'total', 'content' => $merge_vars['total'])
            )
        );

        $template_name = $GLOBALS['mandrillTemplateName'];
        $template_content = array();
        $returned_message = $mandrill->messages->sendTemplate($template_name, $template_content, $message);

        if($returned_message[0]['status'] == "sent") {
            return true;
        } else {
            $this->callbackMessage("Email failed to send - " . $returned_message['message'], "danger");
            return false;
        }

    }

    /*
* Loads all user data from table
*/
    private function getProductInfo($product_id)
    {
        // if database connection opened
        if ($this->databaseConnection()) {

            $sql = $this->db_connection->prepare("SELECT * FROM `products` WHERE `product_id` = :product_id");
            $sql->bindValue(':product_id', $product_id, PDO::PARAM_INT);

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

}