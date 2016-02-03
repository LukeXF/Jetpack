<?php

// Handles site payments, currently only PayPal
class Store extends siteFunctions
{

    public function testPayment()
    {
        require("lib/braintree-php-3.5.0/lib/Braintree.php");
        Braintree_Configuration::environment('sandbox');
        Braintree_Configuration::merchantId('w7gz99fhtbrhj6y6');
        Braintree_Configuration::publicKey('p5hhp9ctz3znhsq3');
        Braintree_Configuration::privateKey('2baa5dd4adcb171001ce768fc2edf041');
        $this->debug($clientToken = Braintree_ClientToken::generate());
    }

    /*
    * Loads all user data from table
    */
    private function getAllVisibleProducts()
    {
        // if database connection opened
        if ($this->databaseConnection()) {

            // load pages for the user
            $sql = $this->db_connection->prepare("SELECT * FROM `products` WHERE `product_visibility` = 1");
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

        $products = $this->getAllVisibleProducts();
        // $this->debug($products);
        if ($products != false) {

            for ($i = 0; $i < count($products); $i++) {

                echo "
                <div class='col-md-6'>
                    <div class='tile tile-store'>
                        <div class='row'>
                            <div class='col-xs-3 col-md-6'>
                               <img src='" . $products[$i]['product_image_one'] . "'>
                            </div>
                            <div class='col-xs-9 col-md-6'>
                                <h3>" . $products[$i]['product_name'] . "</h3>
                                <p>" . $products[$i]['product_description'] . "</p>
                                <button type='button' class='btn btn-default' data-toggle='modal' data-target='#shoppingCart" . $products[$i]['product_id'] . "'>
                                    <i class='btl bt-shopping-cart'></i> More details
                                </button>

                            </div>
                        </div>
                    </div>
                </div>";
            }
        }
    }
}

?>
