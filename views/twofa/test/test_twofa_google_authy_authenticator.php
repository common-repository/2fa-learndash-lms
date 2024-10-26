<?php function molms_test_google_authy_authenticator( $user, $method ) {
	?>
    <h3><?php echo esc_html( molms_lt( 'Test ' ) ) . esc_html( molms_lt( $method ) ); ?></h3>
    <hr>
    <p><?php echo esc_html( molms_lt( 'Enter the verification code from the configured account in your ' ) ) . esc_html( molms_lt( $method ) )
	              . esc_html( molms_lt( ' app.' ) ); ?></p>

    <form name="f" method="post" action="">
        <input type="hidden" name="option" value="mo2f_validate_google_authy_test"/>
        <input type="hidden" name="mo2f_validate_google_authy_test_nonce"
               value="<?php echo esc_attr( wp_create_nonce( "mo2f-validate-google-authy-test-nonce" ) ) ?>"/>

        <input class="mo2f_table_textbox" style="width:200px;" autofocus="true" type="text" name="otp_token" required
               placeholder="<?php echo esc_html( molms_lt( 'Enter OTP' ) ); ?>" style="width:95%;"/>
        <br><br>
        <input type="button" name="back" id="go_back" class="mo_lms_button mo_lms_button1"
               value="<?php echo esc_html( molms_lt( 'Back' ) ); ?>"/>
        <input type="submit" name="validate" id="validate" class="mo_lms_button mo_lms_button1"
               value="<?php echo esc_html( molms_lt( 'Submit' ) ); ?>"/>

    </form>
    <form name="f" method="post" action="" id="mo2f_go_back_form">
        <input type="hidden" name="option" value="mo2f_go_back"/>
        <input type="hidden" name="mo2f_go_back_nonce"
               value="<?php echo esc_attr( wp_create_nonce( "mo2f-go-back-nonce" ) ) ?>"/>
    </form>
    <script>
        jQuery('#go_back').click(function () {
            jQuery('#mo2f_go_back_form').submit();
        });
    </script>

	<?php
}

function molms_test_otp_over_email( $user, $method ) {
	?>
    <h3><?php echo esc_html( molms_lt( 'Test ' ) ) . esc_html( molms_lt( $method ) ); ?></h3>
    <h4> Remaining Email
        Transaction: <?php echo esc_html( molms_Utility::get_mo2f_db_option( 'molms_email_transactions', 'site_option' ) ); ?> </h4>

    <hr>
    <p><?php echo esc_html( molms_lt( 'Enter the one time passcode sent to your registered email id.' ) ); ?></p>

    <form name="f" method="post" action="">
        <input type="hidden" name="option" value="mo2f_validate_otp_over_email"/>
        <input type="hidden" name="mo2f_validate_otp_over_email_test_nonce"
               value="<?php echo esc_attr( wp_create_nonce( "mo2f-validate-otp-over-email-test-nonce" ) ) ?>"/>

        <input class="mo2f_table_textbox" style="width:200px;" autofocus="true" type="text" name="otp_token" required
               placeholder="<?php echo esc_html( molms_lt( 'Enter OTP' ) ); ?>" style="width:95%;"/>
        <br><br>
        <input type="button" name="back" id="go_back" class="mo_lms_button mo_lms_button1"
               value="<?php echo esc_html( molms_lt( 'Back' ) ); ?>"/>
        <input type="submit" name="validate" id="validate" class="mo_lms_button mo_lms_button1"
               value="<?php echo esc_html( molms_lt( 'Submit' ) ); ?>"/>

    </form>
    <form name="f" method="post" action="" id="mo2f_go_back_form">
        <input type="hidden" name="option" value="mo2f_go_back"/>
        <input type="hidden" name="mo2f_go_back_nonce"
               value="<?php echo esc_attr( wp_create_nonce( "mo2f-go-back-nonce" ) ) ?>"/>
    </form>
    <script>
        jQuery('#go_back').click(function () {
            jQuery('#mo2f_go_back_form').submit();
        });
    </script>

	<?php
}
