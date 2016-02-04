<?php

// Handles site payments, currently only PayPal
class Orders extends siteFunctions
{
    public function displayOrders() {
        echo "hi";
    }

    public function getOrders($address_type, $searchForID = false)
    {
        // if database connection opened
        if ($this->databaseConnection()) {

            if (!$searchForID) {
                $sql = $this->db_connection->prepare("SELECT * FROM `addresses` WHERE `address_type` = :address_type OR `address_type` = 'Billing & Shipping'");
                $sql->bindValue(':address_type', $address_type, PDO::PARAM_STR);
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

}