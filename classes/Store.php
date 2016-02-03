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

        $result = Braintree_Transaction::sale([
            'amount' => '10.00',
            'paymentMethodNonce' => $nonce
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
                $sql = $this->db_connection->prepare("SELECT * FROM `products` WHERE `product_id` :product_id");
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
        $products = $this->getAllVisibleProducts();

        if ($products != false) {
            echo "
                    <div class='col-md-12 tile-store'>
                        <div class='tile'>
                            <div class='row'>
                                <div class='col-md-3'>
                                   <img src='" . $products[0]['product_image_one'] . "'>
                                </div>
                                <div class='col-md-9'>
                                    <h3>" . $products[0]['product_name'] . " <span>" . $currency . $products[0]['product_price'] . "</span></h3>
                                    <p>" . $this->truncate($products[0]['product_description'], 180) . "</p>
                                </div>
                            </div>
                        </div>
                    </div>
            ";
        } else {
            $this->callbackMessage("Product not found", "danger");
        }
    }
}

?>
