<div class="container">
    <div class="row">
        <div class="col-md-9">
        </div>
        <?php
            $siteFunctions->displayCallbackMessage();

            if (isset($_GET['product'])) {
                $store->displayProduct($_GET['product']);
            } else {
                $store->displayStore();
                //$store->debug();
            }
        ?>
    </div>
</div>