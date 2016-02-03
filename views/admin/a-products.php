<div class="tile">
    <div class="tile-padding">
        <h3>Users Page</h3>
        <?php $siteFunctions->displayCallbackMessage(); ?>
        <p>
        </p>
    </div>
    <?php $adminUsers->displayUsersTable(); ?>
</div>
<script>
    $(function(){
        $("table").stupidtable();
    });
</script>