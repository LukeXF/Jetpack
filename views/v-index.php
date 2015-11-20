<div class="container">

    <div class="col-md-6">
        <form class="paypal" action="payments.php" method="post" id="paypal_form" target="_blank">
            <input type="hidden" name="cmd" value="_xclick" />
            <input type="hidden" name="no_note" value="1" />
            <input type="hidden" name="lc" value="UK" />
            <input type="hidden" name="currency_code" value="GBP" />
            <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
            <input type="hidden" name="first_name" value="Customer's First Name"  />
            <input type="hidden" name="last_name" value="Customer's Last Name"  />
            <input type="hidden" name="payer_email" value="customer@example.com"  />
            <input type="hidden" name="item_number" value="123456" / >
            <input type="submit" name="submit" value="Submit Payment"/>
        </form>
    </div>


    <div class="col-md-6">
    <?php

    // $weather->getWeather();

    ?>
    </div>

</div>
