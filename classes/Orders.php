<?php

// Handles site payments, currently only PayPal
class Orders extends siteFunctions
{

    public function getOrders($userOrOrderID, $displayOpenOrders = false, $orderID = false)
    {
        // if database connection opened
        if ($this->databaseConnection()) {

            if ($displayOpenOrders) {
                $sql = $this->db_connection->prepare("SELECT * FROM `orders` WHERE `order_user` = :order_user");
                $sql->bindValue(':order_user', $userOrOrderID, PDO::PARAM_INT);
            } else {
                $sql = $this->db_connection->prepare("SELECT * FROM `addresses` WHERE `address_id` = :address_id");
                $sql->bindValue(':address_id', $address_type, PDO::PARAM_INT);
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

    public function insertOrder($braintreeResponse, $data) {

        if ($braintreeResponse->success){

            global $_SESSION;

            // $paymentId   = rand(0, 20000);
            // $paymentType = "test";

            $paymentType = $braintreeResponse->transaction->paymentInstrumentType;
            $paymentId   = $braintreeResponse->transaction->id;

            $this->debug($data, "Cart debug");
            $this->addNewOrder($data, $paymentId, $paymentType);

            for($i = 0; $i < count($_SESSION['cart']); $i++) {
                echo $i;
                $this->debug($_SESSION['cart'][$i]);
                $this->addItemsToOrder($_SESSION['cart'][$i], $paymentId);
            }

            unset($_SESSION['cart']);


            $this->callbackMessage("Your order #" . $sale->transaction->id . " has been successfully placed, we will review it and keep you updated.", "success");

        } else {

            $this->callbackMessage("Error Message: " . $sale->message, "danger");
        }

    }

    public function addNewOrder($postData, $braintreeId, $braintreePaymentType) {

        global $_SESSION;

        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('INSERT INTO orders
(order_braintree_id, order_user, order_payment_type, order_sub_total, order_shipping_total, order_grand_total, order_shipping_address, order_billing_address)
VALUES(:order_braintree_id, :order_user, :order_payment_type, :order_sub_total, :order_shipping_total, :order_grand_total, :order_shipping_address, :order_billing_address)');
            // prepared statement for the username field
            $sql->bindValue(':order_braintree_id', $braintreeId, PDO::PARAM_STR);
            $sql->bindValue(':order_payment_type', $braintreePaymentType, PDO::PARAM_STR);
            $sql->bindValue(':order_sub_total', "2.25", PDO::PARAM_STR);
            $sql->bindValue(':order_shipping_total', "3.01", PDO::PARAM_STR);
            $sql->bindValue(':order_grand_total', "5.26", PDO::PARAM_STR);
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

    public function displayOrders()
    {

        global $_SESSION;
        global $currency;

        $orders = $this->getOrders($_SESSION['user_id'], true);

        // $this->debug($orders);
        if ($orders != false) {
            echo '<table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th data-sort="int">Order ID</th>
                    <th data-sort="string">Username</th>
                    <th data-sort="string">Order Date</th>
                    <th data-sort="string">Order Status</th>
                    <th data-sort="string">Order Total</th>
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

                if (empty($orders[$i]['user_last_name'])) {
                    $lastName = "<i style='opacity:0.5;'>- empty -</i>";
                } else {
                    $lastName = $orders[$i]['user_last_name'];
                }

                echo "
                <tr>
                    <td>" . $orders[$i]['order_braintree_id'] . "</td>
                    " . $orderUser . "
                    <td>" . $orders[$i]['order_date'] . "</td>
                    <td>" . $orders[$i]['order_status'] . "</td>
                    <td>" . $currency . $orders[$i]['order_grand_total'] . "</td>
                    <td>" . $orders[$i]['order_payment_type'] . "</td>
                    <!-- Button trigger modal -->
                    <td>

                        <input type='hidden' name='process' value='loginAsUser'>
                        <input type='hidden' name='user_id' value='" . $orders[$i]['order_id'] . "'>
                        <div class='btn-group' role='group' aria-label='...'>
                            <button type='button' class='btn btn-default' data-toggle='modal' data-target='#editUser" . $orders[$i]['order_id'] . "'><i class='btl bt-search'></i> View</button>
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


    }
}