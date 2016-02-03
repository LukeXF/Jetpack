<div class="tile">
    <div class="tile-padding">
            <h3>Users Page</h3>
            <?php $siteFunctions->displayCallbackMessage(); ?>
            <p>This page will allow you to manage your users and modify their data.
                You can also force login as them to test their experience.
                The final option allows you to see their login details such as their IP location,
                user agent and timing so you can assess any suspicious behavior acting on their account.
            </p>
    </div>
        <?php $adminUsers->displayUsersTable(); ?>
</div>
<script>
    $(function(){
        $("table").stupidtable();
    });
</script>