<?php
function molms_test_email_verification() {
	$molms_dirName = dirname( __FILE__ );
	$molms_dirName = explode( 'wp-content', $molms_dirName );
	$molms_dirName = explode( 'views', $molms_dirName[1] );

	$checkEV = get_site_option( 'siteurl' ) . DIRECTORY_SEPARATOR . "wp-content" . $molms_dirName[0] . "handler" . DIRECTORY_SEPARATOR . "class-molms-password-2factor-login.php"; ?>

    <h3><?php echo esc_html( molms_lt( 'Test Email Verification' ) ); ?></h3>
    <hr>
    <div>
        <br>
        <br>
        <center>
            <h3><?php echo esc_html( molms_lt( 'A verification email is sent to your registered email.' ) ); ?>
                <br>
				<?php echo esc_html( molms_lt( 'We are waiting for your approval...' ) ); ?></h3>
            <img src="<?php echo esc_url_raw( plugins_url( 'includes/images/ajax-loader-login.gif', dirname( dirname( dirname( __FILE__ ) ) ) ) ); ?>"/>
        </center>

        <input type="button" name="back" id="go_back" class="mo_lms_button mo_lms_button1"
               value="<?php echo esc_html( molms_lt( 'Back' ) ); ?>"
               style="margin-top:100px;margin-left:10px;"/>
    </div>

    <form name="f" method="post" action="" id="mo2f_go_back_form">
        <input type="hidden" name="option" value="mo2f_go_back"/>
        <input type="hidden" name="mo2f_go_back_nonce"
               value="<?php echo esc_attr( wp_create_nonce( "mo2f-go-back-nonce" ) ) ?>"/>
    </form>
    <form name="f" method="post" id="mo2f_out_of_band_success_form" action="">
        <input type="hidden" name="option" value="mo2f_out_of_band_success"/>
        <input type="hidden" name="mo2f_out_of_band_success_nonce"
               value="<?php echo esc_attr( wp_create_nonce( "mo2f-out-of-band-success-nonce" ) ) ?>"/>
        <input type="hidden" name="TxidEmail" value="<?php echo esc_html( $_SESSION['txid'] ); ?>"/>
    </form>
    <form name="f" method="post" id="mo2f_out_of_band_error_form" action="">
        <input type="hidden" name="option" value="mo2f_out_of_band_error"/>

        <input type="hidden" name="mo2f_out_of_band_error_nonce"
               value="<?php echo esc_attr( wp_create_nonce( "mo2f-out-of-band-error-nonce" ) ) ?>"/>
    </form>

    <script type="text/javascript">
        jQuery('#go_back').click(function () {
            jQuery('#mo2f_go_back_form').submit();
        });
    </script>
	<?php

	if ( MOLMS_IS_ONPREM ) {
		$otpToken = isset( $_SESSION['otpToken'] ) ? sanitize_text_field( $_SESSION['otpToken'] ) : '';
		$txid     = isset( $_SESSION["txid"] ) ? sanitize_text_field( $_SESSION["txid"] ) : ''; ?>
        <script type="text/javascript">
            var timeout;
            pollMobileValidation();

            function pollMobileValidation() {
                var otpToken = "<?php echo esc_html( $otpToken ); ?>";
                var jsonString = "{\"otpToken\":\"" + otpToken + "\"}";
                var txid = '<?php echo esc_html( $txid ); ?>';
                var data = {
                    'action': 'molms_two_factor_ajax',
                    'mo_2f_two_factor_ajax': 'molms_CheckEVStatus',
                    'nonce': '<?php echo esc_js( wp_create_nonce( 'molms-CheckEVStatus-nonce' ) );?>',
                    'txid': txid
                };
                jQuery.post(ajaxurl, data, function (response) {
                    var response = response.replace(/\s+/g, ' ').trim();
                    var status = response;
                    if (status == '1') {
                        jQuery('#mo2f_out_of_band_success_form').submit();
                    } else if (status == 'ERROR' || status == 'FAILED' || status == 'DENIED' || status == '0') {
                        jQuery('#mo2f_out_of_band_error_form').submit();
                    } else {
                        timeout = setTimeout(pollMobileValidation, 1000);
                    }
                });

            }

        </script>
		<?php
	} else {
		$mo2f_transactionId = isset( $_SESSION['mo2f_transactionId'] ) ? sanitize_text_field( $_SESSION['mo2f_transactionId'] ) : ''; ?>
        <script type="text/javascript">
            var timeout;
            pollMobileValidation();

            function pollMobileValidation() {
                var transId = "<?php echo esc_html( $mo2f_transactionId ); ?>";
                var jsonString = "{\"txId\":\"" + transId + "\"}";
                var postUrl = "<?php echo esc_html( MOLMS_HOST_NAME ); ?>" + "/moas/api/auth/auth-status";

                jQuery.ajax({
                    url: postUrl,
                    type: "POST",
                    dataType: "json",
                    data: jsonString,
                    contentType: "application/json; charset=utf-8",
                    success: function (result) {
                        var status = JSON.parse(JSON.stringify(result)).status;

                        if (status == 'SUCCESS') {
                            jQuery('#mo2f_out_of_band_success_form').submit();
                        } else if (status == 'ERROR' || status == 'FAILED' || status == 'DENIED') {
                            jQuery('#mo2f_out_of_band_error_form').submit();
                        } else {
                            timeout = setTimeout(pollMobileValidation, 3000);
                        }
                    }
                });
            }
        </script>

		<?php
	}
}

?>
