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

    public function addNewOrder($data)
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

    public function displayOrders(){

        global $_SESSION;

        $orders = $this->getOrders($_SESSION['user_id'], true);

        $this->debug($orders);
        if ($orders != false) {
            echo '<table class="table table-striped table-hover">
            <thead>
              <tr>
                <th data-sort="int">User ID</th>
                <th data-sort="string">Username</th>
                <th data-sort="string">First Name</th>
                <th data-sort="string">Last Name</th>
                <th data-sort="string">Account Type</th>
                <th data-sort="string">Email Address</th>
                <th data-sort="string">Location</th>
                <th>Options</th>
              </tr>
            </thead>
            <tbody>';

            for ($i = 0; $i < count($orders); $i++) {


                if ($orders[$i]['order_user'] != null) {
                    $orderUser = $this->getUserDataFromID($orders[$i]['order_user']);
                    $this->debug($orderUser);
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
                    <td>" . $orders[$i]['order_id'] . "</td>
                    <td>" . $orderUser . "</td>
                    <!-- Button trigger modal -->
                    <td>

                        <input type='hidden' name='process' value='loginAsUser'>
                        <input type='hidden' name='user_id' value='" . $orders[$i]['order_id'] . "'>
                        <div class='btn-group' role='group' aria-label='...'>
                            <button type='button' class='btn btn-default' data-toggle='modal' data-target='#editUser" . $orders[$i]['order_id'] . "'><i class='btl bt-edit'></i> Edit</button>
                            <button type='submit' class='btn btn-default' data-toggle='modal' data-target='#loginAsUser" . $orders[$i]['order_id'] . "'><i class='btl bt-key'></i> Log in</button>
                            <button type='button' class='btn btn-default' data-toggle='modal' data-target='#loginHistoryModal" . $orders[$i]['order_id'] . "'><i class='btl bt-map-arrow times'></i> History</button>
                        </div>
                    </td>
                </tr>";
            }

            echo "

            </tbody>
          </table>";


            for ($i = 0; $i < count($orders); $i++) {
                $this->editUserModal($orders[$i]);
                $this->godLoginModal($orders[$i]);
                $this->loginHistoryModal($orders[$i]);
            }

        } else {
            $this->callbackMessage("<b>Error loading users</b> There was an issue relating to the users database, please check your database settings are correct and working", "danger");
        }


    }
}