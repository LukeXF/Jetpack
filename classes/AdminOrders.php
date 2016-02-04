<?php

/**
 * Created by PhpStorm.
 * User: luke.brown
 * Date: 17/12/2015
 * Time: 13:50
 */
class adminOrders extends siteFunctions
{

    /*
     * Loads all user data from table
     */
    private function getAllOrders() {
        // if database connection opened
        if ($this->databaseConnection()) {

            // load pages for the user
            $sql = $this->db_connection->prepare("SELECT * FROM `orders`");
            $sql->execute();

            // fetch all from the widget
            $sql = $sql->fetchAll();

            // $this->debug($sql);

            if ( isset($sql) ) {
                return $sql;
            } else {
                return false;
            }

        } else {

            return false;

        }
    }

    /*
     * Display all of the users data.
     *
     * Checks to see if the data is present and then displays each user
     * with all their data, avatars and the options to edit the data,
     * login as the user and view their login history.
     */
    public function displayOrdersTable(){

        global $currency;
        $orders = $this->getAllOrders();
        $this->debug($orders);
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
            /*
                if ($orders[$i]["product_visibility"] == 2) {
                    $visibility = "In Stock";
                } elseif ($orders[$i]["product_visibility"] == 1) {
                    $visibility = "Out of Stock";
                } else {
                    $visibility = "Hidden";
                }
            */


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

        } else {
            $this->callbackMessage("<b>Error loading orders</b> There was an issue relating to the orders database, please check your database settings are correct and working", "danger");
        }


    }


}