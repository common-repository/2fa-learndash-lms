<?php

function molms_configure_authy_authenticator( $user ) {
	$mo2f_authy_auth = isset( $_SESSION['mo2f_authy_keys'] ) ? sanitize_text_field( $_SESSION['mo2f_authy_keys'] ) : null;
	$data            = isset( $_SESSION['mo2f_authy_keys'] ) ? sanitize_text_field( $mo2f_authy_auth['authy_qrCode'] ) : null;
	$authy_secret    = isset( $_SESSION['mo2f_authy_keys'] ) ? $mo2f_authy_auth['mo2f_authy_secret'] : null; ?>
    <table>
        <tr>
            <td class="mo2f_authy_step1">
                <h3><?php echo esc_html( molms_lt( 'Step-1: Configure Authy Authenticator App.' ) ); ?>
                </h3>
                <hr/>
                <form name="f" method="post" id="mo2f_configure_google_authy_form1" action="">
                    <input type="submit" name="mo2f_authy_configure" class="mo_lms_button mo_lms_button1"
                           style="width:60%;"
                           value="<?php echo esc_html( molms_lt( 'Configure' ) ); ?> "/>
                    <input type="hidden" name="mo2f_configure_authy_authenticator_nonce"
                           value="<?php echo esc_attr( wp_create_nonce( "mo2f-configure-authy-authenticator-nonce" ) ) ?>"/>
                    <br><br>
                    <input type="hidden" name="option" value="mo2f_configure_authy_authenticator"/>
                </form>
                <form name="f" method="post" action="" id="mo2f_go_back_form">
                    <input type="hidden" name="option" value="mo2f_go_back"/>
                    <input type="hidden" name="mo2f_go_back_nonce"
                           value="<?php echo esc_attr( wp_create_nonce( "mo2f-go-back-nonce" ) ) ?>"/>
                    <input type="submit" name="back" id="go_back" class="mo_lms_button mo_lms_button1"
                           style="width:60%;"
                           value="<?php echo esc_html( molms_lt( 'Back' ) ); ?>"/>
                </form>
            </td>
            <td class="mo2f_vertical_line"></td>
            <td class="mo2f_authy_step2">
                <h3><?php echo esc_html( molms_lt( 'Step-2: Set up Authy 2-Factor Authentication App' ) ); ?></h3>
                <h3></h3>
                <hr>
                <div style="<?php echo isset( $_SESSION['mo2f_authy_keys'] ) ? 'display:block' : 'display:none'; ?>">
                    <h4><?php echo esc_html( molms_lt( 'Install the Authy 2-Factor Authentication App.' ) ); ?></h4>
                    <h4><?php echo esc_html( molms_lt( 'Now open and configure Authy 2-Factor Authentication App.' ) ); ?></h4>
                    <h4> <?php echo esc_html( molms_lt( 'Tap on Add Account and then tap on SCAN QR CODE in your App and scan the qr code.' ) ); ?></h4>
                    <center><br>
                        <div id="displayQrCode"><?php echo '<img src="data:image/jpg;base64,' . esc_html( $data ) . '" />'; ?></div>
                    </center>
                    <br>
                    <div><a data-toggle="collapse" href="#mo2f_scanbarcode_a" aria-expanded="false">
                            <b><?php echo esc_html( molms_lt( 'Can\'t scan the QR Code?' ) ); ?> </b></a>
                    </div>

                    <div class="mo2f_collapse" id="mo2f_scanbarcode_a">
                        <ol class="mo2f_ol">
                            <li><?php echo esc_html( molms_lt( 'In Authy 2-Factor Authentication App, tap on ENTER KEY MANUALLY.' ) ); ?>          </li>
                            <li><?php echo esc_html( molms_lt( 'In the pop up "Adding New Account", type your secret key:' ) ); ?></li>
                            <div class="mo2f_google_authy_secret_outer_div">
                                <div class="mo2f_google_authy_secret_inner_div">
									<?php echo esc_html( $authy_secret ); ?>
                                </div>
                                <div class="mo2f_google_authy_secret_text">
									<?php echo esc_html( molms_lt( 'Spaces don\'t matter.' ) ); ?>
                                </div>
                            </div>
                            <li><?php echo esc_html( molms_lt( 'Tap OK.' ) ); ?></li>
                        </ol>
                    </div>
                </div>
            </td>
            <td class="mo2f_vertical_line"></td>
            <td class="mo2f_google_authy_step3">
                <h3><?php echo esc_html( molms_lt( 'Step-3: Verify and Save' ) ); ?></h3>
                <hr>
                <div style="<?php echo isset( $_SESSION['mo2f_authy_keys'] ) ? 'display:block' : 'display:none'; ?>">
                    <h4><?php echo esc_html( molms_lt( 'After you have scanned the QR code and created an account, enter the verification code from the scanned account here.' ) ); ?></h4>
                    <br>
                    <form name="f" method="post" action="">
                        <span>
                            <b><?php echo esc_html( molms_lt( 'Code:' ) ); ?> </b>&nbsp;
                            <input class="mo2f_table_textbox" style="width:200px;" autofocus="true" required="true"
                                   type="text" name="mo2f_authy_token"
                                   placeholder="<?php echo esc_html( molms_lt( 'Enter OTP' ) ); ?>"
                                   style="width:95%;"/>
                        </span>
                        <br><br>
                        <input type="submit" name="validate" id="validate" class="mo_lms_button mo_lms_button1"
                               style="margin-left:12%;"
                               value="<?php echo esc_html( molms_lt( 'Verify and Save' ) ); ?>"/>
                        <input type="hidden" name="mo2f_authy_secret" value="<?php echo esc_html( $authy_secret ); ?>"/>
                        <input type="hidden" name="option" value="mo2f_configure_authy_authenticator_validate"/>
                        <input type="hidden" name="mo2f_configure_authy_authenticator_validate_nonce"
                               value="<?php echo esc_attr( wp_create_nonce( "mo2f-configure-authy-authenticator-validate-nonce" ) ) ?>"/>
                    </form>
                </div>
            </td>
        </tr>
        <br>
    </table>
    <script>
        jQuery('html,body').animate({scrollTop: jQuery(document).height()}, 600);
    </script>
	<?php
}

?>