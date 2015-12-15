<div class="tile">
        <h3>Users Page</h3>
        <p>This page will allow you to manage your users options.</p>
        <?php $adminUsers->displayUsersTable(); ?>
</div>
<script>
    $(function(){
        $("table").stupidtable();
    });
</script>