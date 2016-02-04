<div class="tile" xmlns="http://www.w3.org/1999/html">
    <div class="tile-padding">
        <h3>Orders Page
        </h3>
        <?php $siteFunctions->displayCallbackMessage(); ?>
        <p>
            View all existing orders placed on your system and when you update any order, you'll automatically email the user with the changes.
            Please make sure to keep all orders updated to avoid charge charge backs and confusions.
        </p>
    </div>
    <?php $adminOrders->displayOrdersTable(); ?>
</div>
<script>
    $(function(){
        $("table").stupidtable();
    });
</script>

<div class='modal fade' id='addNewProduct' tabindex='-1' role='dialog' aria-labelledby='#addNewProduct'>
    <div class='modal-dialog' role='document'>
        <form method='post' action='<?php echo $siteFunctions->url("processing"); ?>' name='process'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                    <h3 class='modal-title' id='myModalLabel'>Create a new product</h3>
                    <p>
                        Creating a new product to sell to your customers is quick and easily, simple fill out the form and the product will be setup ready for you to begin selling straight away.
                    </p>
                    <p>
                        <b>Note:</b> You can upload images of your product once the product has been added to the database.
                    </p>
                </div>
                <div class='modal-body'>
                    <div class='row'>
                        <div class='col-md-4'>
                            <p>Product Name</p>
                        </div>
                        <div class='col-md-8'>
                            <input name='data[product_name]' placeholder="Appears on page title and header"/>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-md-4'>
                            <p>Sale Price</p>
                        </div>
                        <div class='col-md-8'>
                            <input name='data[product_price]' placeholder="Example: 23.95"/>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-md-4'>
                            <p>Description</p>
                        </div>
                        <div class='col-md-8'>
                            <textarea name='data[product_description]' placeholder="explain what this product is in detail."></textarea>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-md-4'>
                            <p>Keywords</p>
                        </div>
                        <div class='col-md-8'>
                            <input name='data[product_keywords]' placeholder="up to 5, separated by commas"/>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-md-4'>
                            <p>Options</p>
                        </div>
                        <div class='col-md-8'>
                            <input name='data[product_options]' placeholder="up to 5, separated by commas"/>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-md-4'>
                            <p>Visibility</p>
                        </div>
                        <div class='col-md-8'>
                            <select name='data[product_visibility]' class='form-control'>
                                <option value="1">Visible on site</option>
                                <option value="0">Hidden on site</option>
                            </select>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-md-4'>
                            <p>Image One URL</p>
                        </div>
                        <div class='col-md-8'>
                            <input name='data[product_image_one]' placeholder="The main image" />
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-md-4'>
                            <p>Image Two URL</p>
                        </div>
                        <div class='col-md-8'>
                            <input name='data[product_image_two]' placeholder="Secondary image" />
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-md-4'>
                            <p>Image Three URL</p>
                        </div>
                        <div class='col-md-8'>
                            <input name='data[product_image_three]' placeholder="Third image" />
                        </div>
                    </div>

                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                    <button type='submit' class='btn btn-black' name='process' value='createNewProduct' class='btn btn-default'>Save Product</button>
                </div>
            </div>
        </form>
    </div>
</div>