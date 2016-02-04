<div class="tile">
    <div class="tile-padding">
        <?php
            $siteFunctions->displayCallbackMessage();
            $orders->displayOrders($_GET['product']);
        ?>
    </div>
</div>
<script>
    $(function(){
        $("table").stupidtable();
    });
</script>