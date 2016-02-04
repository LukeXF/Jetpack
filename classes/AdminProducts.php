<?php

/**
 * Created by PhpStorm.
 * User: luke.brown
 * Date: 29/10/2015
 * Time: 11:02
 */
class adminProducts extends siteFunctions
{


    /*
     * Loads all user data from table
     */
    private function getAllProducts() {
        // if database connection opened
        if ($this->databaseConnection()) {

            // load pages for the user
            $sql = $this->db_connection->prepare("SELECT * FROM `products`");
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
    public function displayProductsTable(){

        global $currency;
        $products = $this->getAllProducts();
        // $this->debug($products);
        if ($products != false) {
            echo '<table class="table table-striped table-hover">
            <thead>
              <tr>
                <th data-sort="int">Product ID</th>
                <th data-sort="string">Product Name</th>
                <th data-sort="string">Sale Price</th>
                <th data-sort="string">Date Added</th>
                <th data-sort="string">Orders Placed</th>
                <th data-sort="string">Visibility</th>
                <th>Options</th>
              </tr>
            </thead>
            <tbody>';

            for ($i = 0; $i < count($products); $i++) {

                if ($products[$i]["product_visibility"] == 2) {
                        $visibility = "In Stock";
                } elseif ($products[$i]["product_visibility"] == 1) {
                    $visibility = "Out of Stock";
                } else {
                    $visibility = "Hidden";
                }

                echo "
                <tr>
                    <td>" . $products[$i]['product_id'] . "</td>
                    <td data-sort-value='" . $products[$i]['product_name'] . "'> <img src='" . $products[$i]['product_image_one'] . "'>" . $products[$i]['product_name'] . "</td>
                    <td>" . $currency . $products[$i]['product_price'] . "</td>
                    <td>" . date('d M H:i', strtotime($products[$i]['product_date'])) . "</td>
                    <td></td>
                    <td>" . $visibility . "</td>
                    <!-- Button trigger modal -->
                    <td>

                        <input type='hidden' name='process' value='loginAsUser'>
                        <input type='hidden' name='product_id' value='" . $products[$i]['product_id'] . "'>
                        <div class='btn-group' role='group' aria-label='...'>
                            <button type='button' class='btn btn-default' data-toggle='modal' data-target='#editProduct" . $products[$i]['product_id'] . "'><i class='btl bt-edit'></i> Edit</button>
                            <button type='submit' class='btn btn-default' data-toggle='modal' data-target='#deleteProduct" . $products[$i]['product_id'] . "'><i class='btl bt-times'></i> Delete</button>
                        </div>
                    </td>
                </tr>";
            }

            echo "

               </tbody>
            </table>";


            for ($i = 0; $i < count($products); $i++) {
                $this->editProductModal($products[$i]);
                $this->deleteProductModal($products[$i]);
            }

        } else {
            $this->callbackMessage("<b>Error loading users</b> There was an issue relating to the users database, please check your database settings are correct and working", "danger");
        }


    }

    private function editProductModal($product){

        global $currency;

        if ($product["product_visibility"] == 2) {
            $visibility = "
                <option selected value='2'>In Stock</option>
                <option value='1'>Out of Stock</option>
                <option value='0'>Hidden on site</option>
            ";
        } elseif ($product["product_visibility"] == 1) {
            $visibility = "
                <option value='2'>In Stock</option>
                <option selected value='1'>Out of Stock</option>
                <option value='0'>Hidden on site</option>
            ";
        } else {
            $visibility = "
                <option value='2'>In Stock</option>
                <option value='1'>Out of Stock</option>
                <option selected value='0'>Hidden on site</option>
            ";
        }

        echo "
        <!-- Product data modal -->
        <div class='modal fade' id='editProduct" . $product['product_id'] . "' tabindex='-1' role='dialog' aria-labelledby='#editProduct" . $product['product_id'] . "'>
            <div class='modal-dialog' role='document'>
                <form method='post' action='" . $this->url("processing") . "' name='process'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                            <h3 class='modal-title' id='myModalLabel'>Edit Product</h3>
                            <p>
                                Editing <b>Product ID #" . $product['product_id'] . "</b>, or <b>" . $product['product_name'] . "</b> as
                                it currently appears to your customers.
                            </p>
                            <p>
                                Please note by changing the information here, you will update all orders which contain product.
                            </p>
                        </div>
                        <div class='modal-body'>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Product Name</p>
                                </div>
                                <div class='col-md-8'>
                                    <input name='data[product_name]' value='" . $product['product_name'] . "'/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Sale Price in " . $currency . "</p>
                                </div>
                                <div class='col-md-8'>
                                    <input name='data[product_price]' value='" . $product['product_price'] . "'/>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Description</p>
                                </div>
                                <div class='col-md-8'>
                                    <textarea name='data[product_description]'>" . $product['product_description'] . "</textarea>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Keywords</p>
                                </div>
                                <div class='col-md-8'>
                                    <input name='data[product_keywords]' value='" . $product['product_keywords'] . "' placeholder='separate, keywords, like, this' />
                                </div>
                            </div>
                            <div cla
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Dropdown Options</p>
                                </div>
                                <div class='col-md-8'>
                                    <input name='data[product_options]' value='" . $product['product_options'] . "' placeholder='separate, options, like, this' />
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Image One URL</p>
                                </div>
                                <div class='col-md-8'>
                                    <input name='data[product_image_one]' value='" . $product['product_image_one'] . "' />
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Image Two URL</p>
                                </div>
                                <div class='col-md-8'>
                                    <input name='data[product_image_two]' value='" . $product['product_image_two'] . "' />
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Image Three URL</p>
                                </div>
                                <div class='col-md-8'>
                                    <input name='data[product_image_three]' value='" . $product['product_image_three'] . "' />
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Visibility</p>
                                </div>
                                <div class='col-md-8'>
                                    <select name='data[product_visibility]' class='form-control'>
                                        " . $visibility . "
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class='modal-footer'>
                            <input type='hidden' name='data[product_id]'  value='" . $product['product_id'] . "' >
                            <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                            <button type='submit' class='btn btn-black' name='process' value='updateProductData' class='btn btn-default'>Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        ";
    }

    private function deleteProductModal($product){

        echo "
        <!-- User data modal -->
        <div class='modal fade' id='deleteProduct" . $product['product_id'] . "' tabindex='-1' role='dialog' aria-labelledby='#deleteProduct" . $product['product_id'] . "'>
            <div class='modal-dialog' role='document'>
                <form method='post' action='" . $this->url("processing") . "' name='process'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                            <h3 class='modal-title' id='myModalLabel'>Deleted Product</h3>
                            <p>
                                You are about to delete product id " . $product['product_id'] . ", or " . $product['product_name'] . " as
                                it appears to your customers.
                            </p>
                            <p>
                                <b>This cannot be undone.</b>
                            </p>
                        </div>
                        <div class='modal-body'>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Deleting Product</p>
                                </div>
                                <div class='col-md-8'>
                                    <input name='data[product_name]' readonly value='" . $product['product_name'] . "'/>
                                </div>
                            </div>
                        </div>
                        <div class='modal-footer'>
                            <input type='hidden' name='data[product_id]'  value='" . $product['product_id'] . "' >
                            <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                            <button type='submit' class='btn btn-black' name='process' value='updateProductData' class='btn btn-default'>Delete Product</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        ";
    }

    /*
     *
     */
    public function createNewProduct($data){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('INSERT INTO products
                                                          (product_name, product_price, product_description, product_visibility, product_keywords, product_options product_image_one, product_image_two, product_image_three)
                                                           VALUES(:product_name, :product_price, :product_description, :product_visibility, :product_keywords, :product_options :product_image_one, :product_image_two, :product_image_three)');
            // prepared statement for the username field
            $sql->bindValue(':product_name', $data['product_name'], PDO::PARAM_STR);
            $sql->bindValue(':product_price', $data['product_price'], PDO::PARAM_STR);
            $sql->bindValue(':product_description', $data['product_description'], PDO::PARAM_STR);
            $sql->bindValue(':product_visibility', $data['product_visibility'], PDO::PARAM_INT);
            $sql->bindValue(':product_keywords', $data['product_keywords'], PDO::PARAM_STR);
            $sql->bindValue(':product_options', $data['product_options'], PDO::PARAM_STR);
            $sql->bindValue(':product_image_one', $data['product_image_one'], PDO::PARAM_INT);
            $sql->bindValue(':product_image_two', $data['product_image_two'], PDO::PARAM_INT);
            $sql->bindValue(':product_image_three', $data['product_image_three'], PDO::PARAM_INT);
            // execute it all!
            $sql->execute();


            // get the last entered ID and set it to variable user_id for use in the verification link sent via email
            $user_id = $this->db_connection->lastInsertId();
        }
    }

    /*
     *
     */
    public function updateProductData($data){

        if ($this->databaseConnection()) {

            $sql = $this->db_connection->prepare('UPDATE `products`
            SET `product_name` = :product_name,
                `product_price` = :product_price,
                `product_description` = :product_description,
                `product_visibility` = :product_visibility,
                `product_keywords` = :product_keywords,
                `product_options` = :product_options,
                `product_image_one` = :product_image_one,
                `product_image_two` = :product_image_two,
                `product_image_three` = :product_image_three
            WHERE `product_id` = :product_id');

            $sql->bindValue(':product_name', $data['product_name'], PDO::PARAM_STR);
            $sql->bindValue(':product_price', $data['product_price'], PDO::PARAM_STR);
            $sql->bindValue(':product_description', $data['product_description'], PDO::PARAM_STR);
            $sql->bindValue(':product_visibility', $data['product_visibility'], PDO::PARAM_INT);
            $sql->bindValue(':product_keywords', $data['product_keywords'], PDO::PARAM_STR);
            $sql->bindValue(':product_options', $data['product_options'], PDO::PARAM_STR);
            $sql->bindValue(':product_id', $data['product_id'], PDO::PARAM_INT);
            $sql->bindValue(':product_image_one', $data['product_image_one'], PDO::PARAM_INT);
            $sql->bindValue(':product_image_two', $data['product_image_two'], PDO::PARAM_INT);
            $sql->bindValue(':product_image_three', $data['product_image_three'], PDO::PARAM_INT);
            $sql->execute();

            if ($sql->rowCount()) {
                $this->callbackMessage("Product <b>" . $data['product_name'] . "</b> has been successfully updated.", "success");
            } else {
                $this->callbackMessage("Failed to update product data belonging to product_id(" . $data['product_id'] . "). It most likely has already been updated.", "info");
            }
        }
    }

}
