<div class="tile">
    <div class="tile-padding">
        <h3>New and Open Orders</h3>
        <?php $siteFunctions->displayCallbackMessage(); ?>
        <p>
            Below is your active orders, it will display any orders that haven't been delivered and completed yet. If you have any issues with deliveries or your order in general than please get in contact with our support directly using the order ID as reference.
        </p>
    </div>
    <?php
        $orders->displayOrders();
    ?>
</div>
<script>
    $(function(){
        $("table").stupidtable();
    });
</script>