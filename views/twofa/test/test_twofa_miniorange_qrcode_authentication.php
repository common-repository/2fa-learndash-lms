<?php

function molms_test_miniorange_qr_code_authentication( $user ) {
	?>
    <h3><?php echo esc_html( molms_lt( 'Test QR Code Authentication' ) ); ?></h3>
    <hr>
    <p><?php echo esc_html( molms_lt( 'Open your miniOrange' ) ); ?>
        <b><?php echo esc_html( molms_lt( 'Authenticator App' ) ); ?></b> <?php echo esc_html( molms_lt( 'and click on' ) ); ?>
        <b><?php echo esc_html( molms_lt( 'SCAN QR Code' ) ); ?></b> <?php echo esc_html( molms_lt( 'to scan the QR code. Your phone should have internet connectivity to scan QR code.' ) ); ?>
    </p>

    <div style="color:indianred;">
        <b><?php echo esc_html( molms_lt( 'I am not able to scan the QR code,' ) ); ?> <a
                    data-toggle="collapse" href="#mo2f_testscanqrcode"
                    aria-expanded="false"><?php echo esc_html( molms_lt( 'click here ' ) ); ?></a></b>
    </div>
    <div class="mo2f_collapse" id="mo2f_testscanqrcode">
        <br><?php echo esc_html( molms_lt( 'Follow these instructions below and try again.' ) ); ?>
        <ol>
            <li><?php echo esc_html( molms_lt( 'Make sure your desktop screen has enough brightness.' ) ); ?></li>
            <li><?php echo esc_html( molms_lt( 'Open your app and click on Green button (your registered email is displayed on the button) to scan QR Code.' ) ); ?></li>
            <li><?php echo esc_html( molms_lt( 'If you get cross mark on QR Code then click on \'Back\' button and again click on \'Test\' link.' ) ); ?></li>
        </ol>
    </div>
    <br>
    <table class="mo2f_settings_table">
        <div id="qr-success"></div>
        <div id="displayQrCode">
            <br><?php echo '<img style="width:165px;" src="data:image/jpg;base64,' . esc_html( $_SESSION['mo2f_qrCode'] ) . '" />'; ?>
        </div>

    </table>

    <div id="mobile_registered">
        <form name="f" method="post" id="mo2f_mobile_authenticate_success_form" action="">
            <input type="hidden" name="option" value="mo2f_mobile_authenticate_success"/>
            <input type="hidden" name="mo2f_mobile_authenticate_success_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( "mo2f-mobile-authenticate-success-nonce" ) ) ?>"/>
        </form>
        <form name="f" method="post" id="mo2f_mobile_authenticate_error_form" action="">
            <input type="hidden" name="option" value="mo2f_mobile_authenticate_error"/>
            <input type="hidden" name="mo2f_mobile_authenticate_error_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( "mo2f-mobile-authenticate-error-nonce" ) ) ?>"/>
        </form>
        <form name="f" method="post" action="" id="mo2f_go_back_form">
            <input type="hidden" name="option" value="mo2f_go_back"/>
            <input type="hidden" name="mo2f_go_back_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( "mo2f-go-back-nonce" ) ) ?>"/>
            <input type="submit" name="validate" id="validate" class="mo_lms_button mo_lms_button1"
                   value="<?php echo esc_html( molms_lt( 'Back' ) ); ?>"/>
        </form>
    </div>


    <script>
        var timeout;
        pollMobileValidation();

        function pollMobileValidation() {
            var transId = "<?php echo esc_html( $_SESSION['mo2f_transactionId'] ); ?>";
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
                        var content = "<br><div id='success'><img style='width:165px;margin-top:-1%;margin-left:2%;' src='" + "<?php echo esc_url_raw( plugins_url( 'includes/images/right.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ); ?>" + "' /></div>";
                        jQuery("#displayQrCode").empty();
                        jQuery("#displayQrCode").append(content);
                        setTimeout(function () {
                            jQuery('#mo2f_mobile_authenticate_success_form').submit();
                        }, 1000);

                    } else if (status == 'ERROR' || status == 'FAILED') {
                        var content = "<br><div id='error'><img style='width:165px;margin-top:-1%;margin-left:2%;' src='" + "<?php echo esc_url_raw( plugins_url( 'includes/images/wrong.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ); ?>" + "' /></div>";
                        jQuery("#displayQrCode").empty();
                        jQuery("#displayQrCode").append(content);
                        setTimeout(function () {
                            jQuery('#mo2f_mobile_authenticate_error_form').submit();
                        }, 1000);
                    } else {
                        timeout = setTimeout(pollMobileValidation, 3000);
                    }
                }
            });
        }

        jQuery('html,body').animate({scrollTop: jQuery(document).height()}, 600);
    </script>
	<?php
} ?>
