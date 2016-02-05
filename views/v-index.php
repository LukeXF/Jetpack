<div class="container">
    <div class="row">
        <?php
            // $_SESSION['user_id'], $_SESSION['user_email'], "hi", "Luke"
        ?>

        <div class="col-md-4">
            <?php
            $siteFunctions->debug(); ?>
        </div>
        <div class="col-md-8">
            <?php
                $siteFunctions->displayCallbackMessage();
            ?>

            <form action="<?php echo $siteFunctions->url("processing"); ?>" method="post">
                Name: <select type="text" name="emailType">
                    <option value="dispatched">Order Dispatched</option>
                    <option value="orderDeclined">Order Declined</option>
                    </select>
                <input name="process" value="email" hidden>
                <input name="emailData[user_id]" value="<?= $_SESSION['user_id']; ?>" hidden>
                <input name="emailData[user_email]" value="<?= $_SESSION['user_email']; ?>" hidden>
                <input name="emailData[message]" value="message here" >
                <input name="emailData[user_full_name]" value="Luke Brown" >
                <input type="submit">
            </form>
        </div>

        <hr>
    </div>

</div>
