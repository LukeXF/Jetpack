<div class="row tile">
    <div class="col-md-8">
        <h3>Users Page</h3>
        <p>This page will allow you to manage your users options.</p>
        <?php $adminUsers->displayUsersTable(); ?>
    </div>
    <div class="col-md-4 tile-dark">
        <div class="form-btn-center">
            <button type="button" class="btn btn-default">-</button>
        </div>
    </div>
</div>
<script>
    $(function(){
        $("table").stupidtable();
    });
</script>