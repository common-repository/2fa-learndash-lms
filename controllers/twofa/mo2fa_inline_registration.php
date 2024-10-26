<?php
function molms_fetch_methods() {
	$methods = array(
		"SMS",
		"SOFT TOKEN",
		"MOBILE AUTHENTICATION",
		"PUSH NOTIFICATIONS",
		"GOOGLE AUTHENTICATOR",
		"KBA",
		"OTP_OVER_EMAIL"
	);

	return $methods;
}

function molms_prompt_user_to_select_2factor_mthod_inline( $current_user_id, $login_status, $login_message, $redirect_to, $session_id, $qrCode ) {
	global $molms_db_queries;
	$current_user            = get_userdata( $current_user_id );
	$current_selected_method = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $current_user_id );

	$redirect_to_save = get_user_meta( $current_user_id, 'redirect_to', true );
	if ( is_null( $redirect_to_save ) or $redirect_to_save == '' ) {
		update_user_meta( $current_user_id, 'redirect_to', $redirect_to );
	} else {
		$redirect_to = $redirect_to_save;
		delete_user_meta( $current_user_id, 'redirect_to' );
	}
	$session_id_save = get_user_meta( $current_user_id, 'session_id', true );
	if ( is_null( $session_id_save ) or $session_id_save == '' ) {
		update_user_meta( $current_user_id, 'session_id', $session_id );
	} else {
		$session_id = $session_id_save;
		delete_user_meta( $current_user_id, 'session_id' );
	}
	if ( $current_selected_method == 'MOBILE AUTHENTICATION' || $current_selected_method == 'SOFT TOKEN' || $current_selected_method == 'PUSH NOTIFICATIONS' ) {
		if ( get_site_option( 'mo_2factor_admin_registration_status' ) == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' ) {
			molms_prompt_user_for_miniorange_app_setup( $current_user_id, $login_status, $login_message, $session_id, $qrCode, $current_selected_method );
		} else {
			molms_prompt_user_for_miniorange_register( $current_user_id, $login_status, $login_message );
		}
	} elseif ( $current_selected_method == 'SMS' || $current_selected_method == 'PHONE VERIFICATION' || $current_selected_method == 'SMS AND EMAIL' ) {
		if ( get_site_option( 'mo_2factor_admin_registration_status' ) == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' ) {
			molms_prompt_user_for_phone_setup( $current_user_id, $login_status, $login_message, $current_selected_method );
		} else {
			molms_prompt_user_for_miniorange_register( $current_user_id, $login_status, $login_message );
		}
	} elseif ( $current_selected_method == 'GOOGLE AUTHENTICATOR' ) {
		molms_prompt_user_for_google_authenticator_setup( $current_user_id, $login_status, $login_message );
	} elseif ( $current_selected_method == 'AUTHY 2-FACTOR AUTHENTICATION' ) {
		molms_prompt_user_for_authy_authenticator_setup( $current_user_id, $login_status, $login_message );
	} elseif ( $current_selected_method == 'KBA' ) {
		molms_prompt_user_for_kba_setup( $current_user_id, $login_status, $login_message );
	} elseif ( $current_selected_method == 'OUT OF BAND EMAIL' ) {
		$status = $molms_db_queries->get_user_detail( 'mo_2factor_user_registration_status', $current_user_id );
		if ( ( $status == 'MO_2_FACTOR_PLUGIN_SETTINGS' && get_site_option( 'mo2f_remember_device' ) != 1 ) || ( get_site_option( 'mo2f_disable_kba' ) && $login_status == 'MO_2_FACTOR_SETUP_SUCCESS' ) ) {
			if ( ! MOLMS_IS_ONPREM ) {
				$current_user = get_userdata( $current_user_id );
				$email        = $current_user->user_email;
				$tempEmail    = get_user_meta( $current_user->ID, 'mo2f_email_miniOrange', true );
				if ( isset( $tempEmail ) and $tempEmail != '' ) {
					$email = $tempEmail;
				}
				molms_create_user_in_miniOrange( $current_user_id, $email, $current_selected_method );
			}
			$molms_db_queries->update_user_details( $current_user_id, array( 'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS' ) );
			$pass2fa = new Molms_Password_2Factor_Login();
			$pass2fa->mo2fa_pass2login( site_url() );
		}
	} else {
		$current_user = get_userdata( $current_user_id );
		if ( isset( $current_user->roles[0] ) ) {
			$current_user_role = $current_user->roles[0];
		}
		$opt = molms_fetch_methods( $current_user ); ?>
        <html>
        <head>
            <meta charset="utf-8"/>
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
			<?php
			molms_mo2f_inline_css_and_js(); ?>
        </head>
        <body>
        <div class="mo2f_modal1" tabindex="-1" role="dialog" id="myModal51">
            <div class="mo2f-modal-backdrop"></div>
            <div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
                <div class="login mo_customer_validation-modal-content">
                    <div class="mo2f_modal-header">
                        <h3 class="mo2f_modal-title">
                            <button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
                                    title="<?php echo esc_html__( 'Back to login', '2fa-learndash-lms' ); ?>"
                                    onclick="molms_mologinback();"><span aria-hidden="true">&times;</span></button>

							<?php echo esc_html__( 'New security system has been enabled', '2fa-learndash-lms' ); ?>
                        </h3>
                    </div>
                    <div class="mo2f_modal-body">
                        <b>
							<?php echo esc_html__( 'Configure a Two-Factor method to protect your account', '2fa-learndash-lms' );
							echo '</b>';
							if ( isset( $login_message ) && ! empty( $login_message ) ) {
								echo '<br><br>'; ?>

                                <div id="otpMessage">
                                    <p class="mo2fa_display_message_frontend"
                                       style="text-align: left !important;"><?php echo esc_html__( $login_message, '2fa-learndash-lms' ); ?></p>
                                </div>
								<?php
							} else {
								echo '<br>';
							} ?>

                            <br>
                            <span class="<?php if ( ! ( in_array( "GOOGLE AUTHENTICATOR", $opt ) ) ) {
								echo "mo2f_td_hide";
							} else {
								echo "mo2f_td_show";
							} ?>">
                                    <label title="<?php echo esc_html__( 'You have to enter 6 digits code generated by Authenticator App to login. Supported in Smartphones only.', '2fa-learndash-lms' ); ?>">
                                    <input type="radio" name="mo2f_selected_2factor_method"
                                           value="GOOGLE AUTHENTICATOR"/>
                                    <?php echo esc_html__( 'Google / Authy / Microsoft Authenticator', '2fa-learndash-lms' );
                                    echo '<br> &nbsp;&nbsp;&nbsp; &nbsp;';
                                    echo esc_html__( '(Any TOTP Based Authenticatior App)', '2fa-learndash-lms' );
                                    ?>
                                </label>
                                <br>
                                </span>
                            <span class="<?php if ( ! ( in_array( "OUT OF BAND EMAIL", $opt ) ) ) {
								echo "mo2f_td_hide";
							} else {
								echo "mo2f_td_show";
							} ?>">
                                    <label title="<?php echo esc_html__( 'You will receive an email with link. You have to click the ACCEPT or DENY link to verify your email. Supported in Desktops, Laptops, Smartphones.', '2fa-learndash-lms' ); ?>">
                                                <input type="radio" name="mo2f_selected_2factor_method"
                                                       value="OUT OF BAND EMAIL"/>
                                                <?php echo esc_html__( 'Email Verification', '2fa-learndash-lms' ); ?>
                                    </label>
                                    <br>
                                </span>
                            <span class="<?php if ( ! ( in_array( "SMS", $opt ) ) ) {
								echo "mo2f_td_hide";
							} else {
								echo "mo2f_td_show";
							} ?>">
                                        <label title="<?php echo esc_html__( 'You will receive a one time passcode via SMS on your phone. You have to enter the otp on your screen to login. Supported in Smartphones, Feature Phones.', '2fa-learndash-lms' ); ?>">
                                            <input type="radio" name="mo2f_selected_2factor_method" value="SMS"/>
                                            <?php echo esc_html__( 'OTP Over SMS', '2fa-learndash-lms' ); ?>
                                        </label>
                                    <br>
                                </span>
                            <span class="<?php if ( ! ( in_array( "PHONE VERIFICATION", $opt ) ) ) {
								echo "mo2f_td_hide";
							} else {
								echo "mo2f_td_show";
							} ?>">
                                        <label title="<?php echo esc_html__( 'You will receive a phone call telling a one time passcode. You have to enter the one time passcode to login. Supported in Landlines, Smartphones, Feature phones.', '2fa-learndash-lms' ); ?>">
                                            <input type="radio" name="mo2f_selected_2factor_method"
                                                   value="PHONE VERIFICATION"/>
                                            <?php echo esc_html__( 'Phone Call Verification', '2fa-learndash-lms' ); ?>
                                        </label>
                                    <br>
                                </span>
                            <span class="<?php if ( ! ( in_array( "SOFT TOKEN", $opt ) ) ) {
								echo "mo2f_td_hide";
							} else {
								echo "mo2f_td_show";
							} ?>">
                                        <label title="<?php echo esc_html__( 'You have to enter 6 digits code generated by miniOrange Authenticator App like Google Authenticator code to login. Supported in Smartphones only.', '2fa-learndash-lms' ); ?>">
                                            <input type="radio" name="mo2f_selected_2factor_method" value="SOFT TOKEN"/>
                                            <?php echo esc_html__( 'Soft Token', '2fa-learndash-lms' ); ?>
                                        </label>
                                    <br>
                                </span>
                            <span class="<?php if ( ! ( in_array( "MOBILE AUTHENTICATION", $opt ) ) ) {
								echo "mo2f_td_hide";
							} else {
								echo "mo2f_td_show";
							} ?>">
                                        <label title="<?php echo esc_html__( 'You have to scan the QR Code from your phone using miniOrange Authenticator App to login. Supported in Smartphones only.', '2fa-learndash-lms' ); ?>">
                                            <input type="radio" name="mo2f_selected_2factor_method"
                                                   value="MOBILE AUTHENTICATION"/>
                                            <?php echo esc_html__( 'QR Code Authentication', '2fa-learndash-lms' ); ?>
                                        </label>
                                    <br>
                                </span>
                            <span class="<?php if ( ! ( in_array( "PUSH NOTIFICATIONS", $opt ) ) ) {
								echo "mo2f_td_hide";
							} else {
								echo "mo2f_td_show";
							} ?>">
                                        <label title="<?php echo esc_html__( 'You will receive a push notification on your phone. You have to ACCEPT or DENY it to login. Supported in Smartphones only.', '2fa-learndash-lms' ); ?>">
                                            <input type="radio" name="mo2f_selected_2factor_method"
                                                   value="PUSH NOTIFICATIONS"/>
                                            <?php echo esc_html__( 'Push Notification', '2fa-learndash-lms' ); ?>
                                        </label>
                                        <br>    
                                </span>
                            <span class="<?php if ( ! ( in_array( "AUTHY 2-FACTOR AUTHENTICATION", $opt ) ) ) {
								echo "mo2f_td_hide";
							} else {
								echo "mo2f_td_show";
							} ?>">
                                            <label title="<?php echo esc_html__( 'You have to enter 6 digits code generated by Authy 2-Factor Authentication App to login. Supported in Smartphones only.', '2fa-learndash-lms' ); ?>">
                                                <input type="radio" name="mo2f_selected_2factor_method"
                                                       value="AUTHY 2-FACTOR AUTHENTICATION"/>
                                                <?php echo esc_html__( 'Authy 2-Factor Authentication', '2fa-learndash-lms' ); ?>
                                            </label>
                                            <br>
                                </span>
                            <span class="<?php if ( ! ( in_array( "KBA", $opt ) ) ) {
								echo "mo2f_td_hide";
							} else {
								echo "mo2f_td_show";
							} ?>">
                                    <label title="<?php echo esc_html__( 'You have to answers some knowledge based security questions which are only known to you to authenticate yourself. Supported in Desktops,Laptops,Smartphones.', '2fa-learndash-lms' ); ?>">
                                    <input type="radio" name="mo2f_selected_2factor_method" value="KBA"/>
                                                <?php echo esc_html__( 'Security Questions ( KBA )', '2fa-learndash-lms' ); ?>
                                            </label>
                                            <br>
                                </span>
                            <span class="<?php if ( ! ( in_array( "SMS AND EMAIL", $opt ) ) ) {
								echo "mo2f_td_hide";
							} else {
								echo "mo2f_td_show";
							} ?>">
                                    <label title="<?php echo esc_html__( 'You will receive a one time passcode via SMS on your phone and your email. You have to enter the otp on your screen to login. Supported in Smartphones, Feature Phones.', '2fa-learndash-lms' ); ?>">
                                    <input type="radio" name="mo2f_selected_2factor_method" value="SMS AND EMAIL"/>
                                                <?php echo esc_html__( 'OTP Over SMS and Email', '2fa-learndash-lms' ); ?>
                                            </label>
                                            <br>
                                </span>
                            <span class="<?php if ( ! ( in_array( "OTP_OVER_EMAIL", $opt ) ) ) {
								echo "mo2f_td_hide";
							} else {
								echo "mo2f_td_show";
							} ?>">
                                    <label title="<?php echo esc_html__( 'You will receive a one time passcode on your email. You have to enter the otp on your screen to login. Supported in Smartphones, Feature Phones.', '2fa-learndash-lms' ); ?>">
                                    <input type="radio" name="mo2f_selected_2factor_method" value="OTP OVER EMAIL"/>
                                                <?php echo esc_html__( 'OTP Over Email', '2fa-learndash-lms' ); ?>
                                            </label>
                                </span>
                            <br><a href="#skiptwofactor"
                                   style="color:#F4D03F ;font-weight:bold;margin-left:35%;"><?php echo esc_html__( 'Skip Two Factor', '2fa-learndash-lms' ); ?></a>>>
                            <br/>
							<?php molms_customize_logo() ?>
                    </div>
                </div>
            </div>
        </div>
        <form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
              style="display:none;">
            <input type="hidden" name="miniorange_mobile_validation_failed_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
        </form>
        <form name="f" method="post" action="" id="mo2f_select_2fa_methods_form" style="display:none;">
            <input type="hidden" name="mo2f_selected_2factor_method"/>
            <input type="hidden" name="miniorange_inline_save_2factor_method_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-save-2factor-method-nonce' ) ); ?>"/>
            <input type="hidden" name="option" value="miniorange_inline_save_2factor_method"/>
            <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
            <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id ); ?>"/>
        </form>

        <form name="f" id="mo2f_skip_loginform" method="post" action="" style="display:none;">
            <input type="hidden" name="option" value="mo2f_skip_2fa_setup"/>
            <input type="hidden" name="miniorange_skip_2fa_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-skip-nonce' ) ); ?>"/>
            <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
            <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id ); ?>"/>

        </form>

        <script>
            function molms_mologinback() {
                jQuery('#mo2f_backto_mo_loginform').submit();
            }

            jQuery('input:radio[name=mo2f_selected_2factor_method]').click(function () {
                var selectedMethod = jQuery(this).val();
                document.getElementById("mo2f_select_2fa_methods_form").elements[0].value = selectedMethod;
                jQuery('#mo2f_select_2fa_methods_form').submit();
            });
            jQuery('a[href="#skiptwofactor"]').click(function (e) {

                jQuery('#mo2f_skip_loginform').submit();
            });
        </script>
        </body>
        </html>
		<?php
	}
}

function molms_create_user_in_miniOrange( $current_user_id, $email, $currentMethod ) {
	global $molms_db_queries;
	$mo2f_user_email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user_id );
	if ( isset( $mo2f_user_email ) and $mo2f_user_email != '' ) {
		$email = $mo2f_user_email;
	}

	$current_user = get_userdata( $current_user_id );
	if ( $current_user_id == get_site_option( 'mo2f_miniorange_admin' ) ) {
		$email = get_site_option( 'mo2f_email' );
	}

	$enduser    = new Molms_Two_Factor_Setup();
	$check_user = json_decode( $enduser->mo_check_user_already_exist( $email ), true );

	if ( json_last_error() == JSON_ERROR_NONE ) {
		if ( $check_user['status'] == 'ERROR' ) {
			return molms_2fConstants:: langTranslate( $check_user['message'] );
		} elseif ( strcasecmp( $check_user['status'], 'USER_FOUND' ) == 0 ) {
			$molms_db_queries->update_user_details( $current_user_id, array(
				'user_registration_with_miniorange'   => 'SUCCESS',
				'mo2f_user_email'                     => $email,
				'mo_2factor_user_registration_status' => 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR'
			) );
			update_site_option( 'totalUsersCloud', get_site_option( 'totalUsersCloud' ) + 1 );

			$mo2fa_login_status = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
		} elseif ( strcasecmp( $check_user['status'], 'USER_NOT_FOUND' ) == 0 ) {
			$content = json_decode( $enduser->mo_create_user( $current_user, $email ), true );
			if ( json_last_error() == JSON_ERROR_NONE ) {
				if ( strcasecmp( $content['status'], 'SUCCESS' ) == 0 ) {
					update_site_option( 'totalUsersCloud', get_site_option( 'totalUsersCloud' ) + 1 );
					$molms_db_queries->update_user_details( $current_user_id, array(
						'user_registration_with_miniorange'   => 'SUCCESS',
						'mo2f_user_email'                     => $email,
						'mo_2factor_user_registration_status' => 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR'
					) );

					$mo2fa_login_message = '';
					$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				}
			}
		} elseif ( strcasecmp( $check_user['status'], 'USER_FOUND_UNDER_DIFFERENT_CUSTOMER' ) == 0 ) {
			$mo2fa_login_message = esc_html__( 'The email associated with your account is already registered. Please contact your admin to change the email.', '2fa-learndash-lms' );
			$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_FOR_RELOGIN';
			molms_inline_email_form( $email, $current_user_id );
			exit;
		}
	}
}

function molms_inline_email_form( $email, $current_user_id ) {
	?>
    <html>
    <head>
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		molms_mo2f_inline_css_and_js(); ?>
    </head>
    <body>
    <div class="mo2f_modal" tabindex="-1" role="dialog" id="myModal5">
        <div class="mo2f-modal-backdrop"></div>
        <div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
            <div class="login mo_customer_validation-modal-content">
                <div class="mo2f_modal-header">
                    <h3 class="mo2f_modal-title">
                        <button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
                                title="<?php echo esc_html__( 'Back to login', '2fa-learndash-lms' ); ?>"
                                onclick="molms_mologinback();"><span aria-hidden="true">&times;</span></button>
						<?php echo esc_html__( 'Email already registered.', '2fa-learndash-lms' ); ?></h3>
                </div>
                <div class="mo2f_modal-body">
                    <form action="" method="post" name="f">
                        <p>The Email assoicated with your account is already registered in miniOrnage. Please use a
                            different email address or contact miniOrange.
                        </p><br>
                        <i><b>Enter your Email:&nbsp;&nbsp;&nbsp; </b> <input type='email' id='emailInlineCloud'
                                                                              name='emailInlineCloud' size='40' required
                                                                              value="<?php echo esc_html( $email ); ?>"/></i>
                        <br>
                        <p id="emailalredyused" style="color: red;" hidden>This email is already associated with
                            miniOrange.</p>
                        <br>
                        <input type="hidden" name="miniorange_emailChange_nonce"
                               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-email-change-nonce' ) ); ?>"/>
                        <input type="text" name="current_user_id" hidden id="current_user_id"
                               value="<?php echo esc_html( $current_user_id ); ?>"/>
                        <button type="submit" class="mo_lms_button mo_lms_button1" style="margin-left: 165px;"
                                id="save_entered_email_inlinecloud">Save
                        </button>
                    </form>
                    <br>
					<?php molms_customize_logo() ?>
                </div>
            </div>
        </div>
    </div>
    <form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
          style="display:none;">
        <input type="hidden" name="miniorange_mobile_validation_failed_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
    </form>
    <form name="f" method="post" action="" id="mo2f_select_2fa_methods_form" style="display:none;">
        <input type="hidden" name="mo2f_selected_2factor_method"/>
        <input type="hidden" name="miniorange_inline_save_2factor_method_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-save-2factor-method-nonce' ) ); ?>"/>
        <input type="hidden" name="option" value="miniorange_inline_save_2factor_method"/>
        <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id ); ?>"/>
    </form>
	<?php if ( get_site_option( 'mo2f_skip_inline_option' ) && ! get_site_option( 'mo2f_enable_emailchange' ) ) { ?>
        <form name="f" id="mo2f_skip_loginform" method="post" action="" style="display:none;">
            <input type="hidden" name="miniorange_skip_2fa"
                   value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-skip-nonce' ) ); ?>"/>
        </form>
	<?php } ?>

    <script type="text/javascript">
        jQuery('#save_entered_email_inlinecloud1').click(function () {
            var email = jQuery('#emailInlineCloud').val();
            var nonce = '<?php echo esc_attr( wp_create_nonce( "checkuserinminiOrangeNonce" ) ); ?>';
            var data = {
                'action': 'molms_two_factor_ajax',
                'mo_2f_two_factor_ajax': 'mo2f_check_user_exist_miniOrange',
                'email': email,
                'nonce': nonce

            };

            var ajaxurl = '<?php echo esc_url( admin_url( '' ) ); ?>';


            jQuery.post(ajaxurl, data, function (response) {

                if (response == 'alreadyExist') {
                    jQuery('#emailalredyused').show();
                } else if (response == 'USERCANBECREATED') {
                    document.getElementById("mo2f_select_2fa_methods_form").elements[0].value = selectedMethod;
                    jQuery('#mo2f_select_2fa_methods_form').submit();
                }
            });

        });


    </script>
    </body>

	<?php
}

function molms_prompt_user_for_miniorange_app_setup( $current_user_id, $login_status, $login_message, $session_id, $qrCode, $currentMethod ) {
	global $molms_db_queries;
	if ( isset( $qrCode ) ) {
		$qrCodedata = $qrCode['mo2f-login-qrCode'];
		$showqrCode = $qrCode['mo2f_show_qr_code'];
	}
	$current_user = get_userdata( $current_user_id );
	$email        = $current_user->user_email;

	$opt = molms_fetch_methods( $current_user );

	$mobile_registration_status = $molms_db_queries->get_user_detail( 'mobile_registration_status', $current_user_id ); ?>
    <html>
    <head>
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		molms_mo2f_inline_css_and_js(); ?>
    </head>
    <body>
    <div class="mo2f_modal" tabindex="-1" role="dialog" id="myModal5">
        <div class="mo2f-modal-backdrop"></div>
        <div class="mo2f_modal-dialog mo2f_modal-lg">
            <div class="login mo_customer_validation-modal-content">
                <div class="mo2f_modal-header">
                    <h4 class="mo2f_modal-title">
                        <button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
                                title="<?php echo esc_html__( 'Back to login', '2fa-learndash-lms' ); ?>"
                                onclick="molms_mologinback();"><span aria-hidden="true">&times;</span></button>
						<?php echo esc_html__( 'Setup miniOrange', '2fa-learndash-lms' ); ?>
                        <b><?php echo esc_html__( 'Authenticator', '2fa-learndash-lms' ); ?></b> <?php echo esc_html__( 'App', '2fa-learndash-lms' ); ?>
                    </h4>
                </div>
                <div class="mo2f_modal-body">
					<?php if ( isset( $login_message ) && ! empty( $login_message ) ) { ?>
                        <div id="otpMessage">
                            <p class="mo2fa_display_message_frontend"
                               style="text-align: left !important;"><?php echo esc_html__( $login_message, '2fa-learndash-lms' ); ?></p>
                        </div>
					<?php } ?>
                    <div style="margin-right:7px;"><?php molms_download_instruction_for_mobile_app( $current_user_id, $mobile_registration_status ); ?></div>
                    <div class="mo_margin_left">
                        <h3><?php echo esc_html__( 'Step-2 : Scan QR code', '2fa-learndash-lms' ); ?></h3>
                        <hr class="mo_hr">
                        <div id="mo2f_configurePhone">
                            <h4><?php echo esc_html__( 'Please click on \'Configure your phone\' button below to see QR Code.', '2fa-learndash-lms' ); ?></h4>
                            <center>
								<?php if ( sizeof( $opt ) > 1 ) { ?>
                                    <input type="button" name="back" id="mo2f_inline_back_btn" class="miniorange_button"
                                           value="<?php echo esc_html__( 'Back', '2fa-learndash-lms' ); ?>"/>
								<?php } ?>
                                <input type="button" name="submit" onclick="molms_moconfigureapp();"
                                       class="miniorange_button"
                                       value="<?php echo esc_html__( 'Configure your phone', '2fa-learndash-lms' ); ?>"/>
                            </center>
                        </div>
						<?php
						if ( isset( $showqrCode ) && $showqrCode == 'MO_2_FACTOR_SHOW_QR_CODE' && isset( $_POST['miniorange_inline_show_qrcode_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['miniorange_inline_show_qrcode_nonce'] ), 'miniorange-2-factor-inline-show-qrcode-nonce' ) ) {
							molms_initialize_inline_mobile_registration( $current_user, $session_id, $qrCodedata ); ?>
							<?php
						} ?>

						<?php molms_customize_logo() ?>
                    </div>
                    <br>
                    <br>
                </div>
            </div>
        </div>
    </div>
    <form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
          style="display:none;">
        <input type="hidden" name="miniorange_mobile_validation_failed_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
    </form>
    <form name="f" method="post" action="" id="mo2f_inline_configureapp_form" style="display:none;">
        <input type="hidden" name="option" value="miniorange_inline_show_mobile_config"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id ); ?>"/>
        <input type="hidden" name="miniorange_inline_show_qrcode_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-show-qrcode-nonce' ) ); ?>"/>
    </form>
    <form name="f" method="post" id="mo2f_inline_mobile_register_form" action="" style="display:none;">
        <input type="hidden" name="option" value="miniorange_inline_complete_mobile"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id ); ?>"/>
        <input type="hidden" name="mo_auth_inline_mobile_registration_complete_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-mobile-registration-complete-nonce' ) ); ?>"/>
    </form>
	<?php if ( sizeof( $opt ) > 1 ) { ?>
        <form name="f" method="post" action="" id="mo2f_goto_two_factor_form">
            <input type="hidden" name="option" value="miniorange_back_inline"/>
            <input type="hidden" name="miniorange_inline_two_factor_setup"
                   value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-setup-nonce' ) ); ?>"/>
        </form>
	<?php } ?>
    <script>
        function molms_mologinback() {
            jQuery('#mo2f_backto_mo_loginform').submit();
        }

        function molms_moconfigureapp() {
            jQuery('#mo2f_inline_configureapp_form').submit();
        }

        jQuery('#mo2f_inline_back_btn').click(function () {
            jQuery('#mo2f_goto_two_factor_form').submit();
        });
		<?php
		if (isset( $showqrCode ) && $showqrCode == 'MO_2_FACTOR_SHOW_QR_CODE' && isset( $_POST['miniorange_inline_show_qrcode_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['miniorange_inline_show_qrcode_nonce'] ), 'miniorange-2-factor-inline-show-qrcode-nonce' )) {
		?>
		<?php
		} ?>
    </script>
    </body>
    </html>
	<?php
}

function molms_prompt_user_for_google_authenticator_setup( $current_user_id, $login_status, $login_message )
{
	$mo2f_google_auth = json_decode( get_user_meta( $current_user_id, 'mo2f_google_auth', true ), true );
	$data             = isset( $mo2f_google_auth ) ? $mo2f_google_auth['ga_qrCode'] : null;
	$ga_secret        = isset( $mo2f_google_auth ) ? $mo2f_google_auth['ga_secret'] : null; ?>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		molms_mo2f_inline_css_and_js(); ?>
    </head>
    <style>
        * {
            box-sizing: border-box;
        }

        [class*="mcol-"] {
            float: left;
            padding: 15px;
        }

        /* For desktop: */
        .mcol-1 {
            width: 50%;
        }

        .mcol-2 {
            width: 50%;
        }

        @media only screen and (max-width: 768px) {
            /* For mobile phones: */
            [class*="mcol-"] {
                width: 100%;
            }
        }
    </style>
    <body>
    <div class="mo2f_modal" tabindex="-1" role="dialog" id="myModal5">
        <div class="mo2f-modal-backdrop"></div>
        <div class="mo2f_modal-dialog mo2f_modal-lg">
            <div class="login mo_customer_validation-modal-content">
                <div class="mo2f_modal-header">
                    <h4 class="mo2f_modal-title" style="color:black;">
                        <button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
                                title="<?php echo esc_html__( 'Back to login', '2fa-learndash-lms' ); ?>"
                                onclick="molms_mologinback();"><span aria-hidden="true">&times;</span></button>
						<?php echo esc_html__( 'Setup Authenticator', '2fa-learndash-lms' ); ?></h4>
                </div>
                <div class="mo2f_modal-body">
					<?php

					$current_user = get_userdata( $current_user_id );
					$opt          = molms_fetch_methods( $current_user ); ?>
					<?php if ( isset( $login_message ) && ! empty( $login_message ) ) { ?>
                        <div id="otpMessage"
						     <?php if ( get_user_meta( $current_user_id, 'mo2f_is_error', true ) ) {
						     ?>style="background-color:#FADBD8; color:#E74C3C;?>"<?php update_user_meta( $current_user_id, 'mo2f_is_error', false );
						} ?>
                        >
                            <p class="mo2fa_display_message_frontend"
                               style="text-align: left !important;"><?php echo esc_html__( $login_message, '2fa-learndash-lms' ); ?></p>
                        </div>
						<?php if ( isset( $login_message ) ) {
							?> <br/> <?php
						} ?>
					<?php } ?>
                    <div class="mcol-1">
                        <div id="mo2f_choose_app_tour">
                            <label for="authenticator_type"><b>Choose an Authenticator app:</b></label>

                            <select id="authenticator_type">
                                <option value="google_authenticator">Google Authenticator</option>
                                <option value="msft_authenticator">Microsoft Authenticator</option>
                                <option value="authy_authenticator">Authy Authenticator</option>
                                <option value="last_pass_auth">LastPass Authenticator</option>
                                <option value="free_otp_auth">FreeOTP Authenticator</option>
                                <option value="duo_auth">Duo Mobile Authenticator</option>
                            </select>
                            <div id="links_to_apps_tour" style="background-color:white;padding:5px;">
                                                <span id="links_to_apps">
                                                 <p style="background-color:#e8e4e4;padding:5px;">Get the App - <a
                                                             href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2"
                                                             target="_blank"><b><?php echo esc_html( molms_lt( "Android Play Store" ) ); ?></b></a>, &nbsp;
                                                        <a href="http://itunes.apple.com/us/app/google-authenticator/id388497605"
                                                           target="_blank"><b><?php echo esc_html( molms_lt( "iOS App Store" ) ); ?>.</b>&nbsp;</p></a>

                                                </span>
                            </div>
                        </div>
                        <div style="font-size: 18px !important;"><?php echo esc_html__( 'Scan the QR code from the Authenticator App.', '2fa-learndash-lms' ); ?></div>
                        <ol>
                            <li><?php echo esc_html__( 'In the app, tap on Menu and select "Set up account"', '2fa-learndash-lms' ); ?></li>
                            <li><?php echo esc_html__( 'Select "Scan a barcode". Use your phone\'s camera to scan this barcode.', '2fa-learndash-lms' ); ?></li>
                            <br>
							<?php if ( MOLMS_IS_ONPREM ) { ?>
                                <div class="mo2f_gauth" data-qrcode="<?php echo esc_html( $data ); ?>"
                                     style="float:left;margin-left:10%;"></div>
								<?php
							} else { ?>
                                <div style="margin-left: 14%;">
                                    <div class="mo2f_gauth_column_cloud mo2f_gauth_left">
                                        <div id="displayQrCode"><?php echo '<img id="displayGAQrCodeTour" style="line-height: 0;background:white;" src="data:image/jpg;base64,' . esc_html( $data ) . '" />'; ?></div>
                                    </div>
                                </div>
							<?php } ?>
                            <div style="margin-top: 55%"><a href="#mo2f_scanbarcode_a" aria-expanded="false"
                                                            style="color:#21618C;"><b><?php echo esc_html__( 'Can\'t scan the barcode?', '2fa-learndash-lms' ); ?></b></a>
                            </div>

                        </ol>
                        <div id="mo2f_scanbarcode_a" hidden>
                            <ol>
                                <li><?php echo esc_html__( 'Tap Menu and select "Set up account."', '2fa-learndash-lms' ); ?></li>
                                <li><?php echo esc_html__( 'Select "Enter provided key"', '2fa-learndash-lms' ); ?></li>
                                <li><?php echo esc_html__( 'In "Enter account name" type your full email address.', '2fa-learndash-lms' ); ?></li>
                                <li class="mo2f_list"><?php echo esc_html__( 'In "Enter your key" type your secret key:', '2fa-learndash-lms' ); ?></li>
                                <div style="padding: 10px; background-color: #f9edbe;width: 20em;text-align: center;">
                                    <div style="font-size: 14px; font-weight: bold;line-height: 1.5;">
										<?php echo esc_html( $ga_secret ); ?>
                                    </div>
                                    <div style="font-size: 80%;color: #666666;">
										<?php echo esc_html__( 'Spaces don\'t matter.', '2fa-learndash-lms' ); ?>
                                    </div>
                                </div>
                                <li class="mo2f_list"><?php echo esc_html__( 'Key type: make sure "Time-based" is selected.', '2fa-learndash-lms' ); ?></li>
                                <li class="mo2f_list"><?php echo esc_html__( 'Tap Add.', '2fa-learndash-lms' ); ?></li>
                            </ol>
                        </div>
                    </div>
                    <div class="mcol-2">
                        <div style="font-size: 18px !important;">
                            <b><?php echo esc_html__( 'Verify and Save', '2fa-learndash-lms' ); ?> </b></div>
                        <br/>
                        <div style="font-size: 15px !important;"><?php echo esc_html__( 'Once you have scanned the barcode, enter the 6-digit verification code generated by the Authenticator app', '2fa-learndash-lms' ); ?></div>
                        <br/>
                        <form name="" method="post" id="mo2f_inline_verify_ga_code_form">
                                                <span><b><?php echo esc_html__( 'Code:', '2fa-learndash-lms' ); ?> </b>
                                                <br/>
                                                <input type="hidden" name="option"
                                                       value="miniorange_inline_ga_validate">
                                                <input class="mo2f_IR_GA_token" style="margin-left:36.5%;"
                                                       autofocus="true" required="true" pattern="[0-9]{4,8}" type="text"
                                                       id="google_auth_code" name="google_auth_code"
                                                       placeholder="<?php echo esc_html__( 'Enter OTP', '2fa-learndash-lms' ); ?>"/></span><br/>
                            <div class="center">
                                <input type="submit" name="validate" id="validate" class="miniorange_button"
                                       value="<?php echo esc_html__( 'Verify and Save', '2fa-learndash-lms' ); ?>"/>
                            </div>
                            <input type="hidden" name="mo2f_inline_validate_ga_nonce"
                                   value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-google-auth-nonce' ) ); ?>"/>
                        </form>
                        <form name="f" method="post" action="" id="mo2f_goto_two_factor_form" class="center">
                            <input type="submit" name="back" id="mo2f_inline_back_btn" class="miniorange_button"
                                   value="<?php echo esc_html( molms_lt( 'Back' ) ); ?>"/>
                            <input type="hidden" name="option" value="miniorange_back_inline"/>
                            <input type="hidden" name="miniorange_inline_two_factor_setup"
                                   value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-setup-nonce' ) ); ?>"/>
                        </form>
                    </div>
                    <br>
                    <br>
					<?php molms_customize_logo() ?>
                </div>
            </div>
        </div>
    </div>
    <form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
          style="display:none;">
        <input type="hidden" name="miniorange_mobile_validation_failed_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
    </form>
    <form name="f" method="post" id="mo2f_inline_app_type_ga_form" action="" style="display:none;">
        <input type="hidden" name="google_phone_type"/>
        <input type="hidden" name="mo2f_inline_ga_phone_type_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-ga-phone-type-nonce' ) ); ?>"/>
    </form>

    <script>
        jQuery('#authenticator_type').change(function () {
            var auth_type = jQuery(this).val();
            if (auth_type == 'google_authenticator') {
                jQuery('#links_to_apps').html('<p style="background-color:#e8e4e4;padding:5px;">' +
                    'Get the App - <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank"><b><?php echo esc_html( molms_lt( "Android Play Store" ) ); ?></b></a>, &nbsp;' +
                    '<a href="http://itunes.apple.com/us/app/google-authenticator/id388497605" target="_blank"><b><?php echo esc_html( molms_lt( "iOS App Store" ) ); ?>.</b>&nbsp;</p>');
                jQuery('#mo2f_change_app_name').show();
                jQuery('#links_to_apps').show();
            } else if (auth_type == 'msft_authenticator') {
                jQuery('#links_to_apps').html('<p style="background-color:#e8e4e4;padding:5px;">' +
                    'Get the App - <a href="https://play.google.com/store/apps/details?id=com.azure.authenticator" target="_blank"><b><?php echo esc_html( molms_lt( "Android Play Store" ) ); ?></b></a>, &nbsp;' +
                    '<a href="https://apps.apple.com/us/app/microsoft-authenticator/id983156458" target="_blank"><b><?php echo esc_html( molms_lt( "iOS App Store" ) ); ?>.</b>&nbsp;</p>');
                jQuery('#links_to_apps').show();
            } else if (auth_type == 'free_otp_auth') {
                jQuery('#links_to_apps').html('<p style="background-color:#e8e4e4;padding:5px;">' +
                    'Get the App - <a href="https://play.google.com/store/apps/details?id=org.fedorahosted.freeotp" target="_blank"><b><?php echo esc_html( molms_lt( "Android Play Store" ) ); ?></b></a>, &nbsp;' +
                    '<a href="https://apps.apple.com/us/app/freeotp-authenticator/id872559395" target="_blank"><b><?php echo esc_html( molms_lt( "iOS App Store" ) ); ?>.</b>&nbsp;</p>');
                jQuery('#links_to_apps').show();
            } else if (auth_type == 'duo_auth') {
                jQuery('#links_to_apps').html('<p style="background-color:#e8e4e4;padding:5px;">' +
                    'Get the App - <a href="https://play.google.com/store/apps/details?id=com.duosecurity.duomobile" target="_blank"><b><?php echo esc_html( molms_lt( "Android Play Store" ) ); ?></b></a>, &nbsp;' +
                    '<a href="https://apps.apple.com/in/app/duo-mobile/id422663827" target="_blank"><b><?php echo esc_html( molms_lt( "iOS App Store" ) ); ?>.</b>&nbsp;</p>');
                jQuery('#links_to_apps').show();
            } else if (auth_type == 'authy_authenticator') {
                jQuery('#links_to_apps').html('<p style="background-color:#e8e4e4;padding:5px;">' +
                    'Get the App - <a href="https://play.google.com/store/apps/details?id=com.authy.authy" target="_blank"><b><?php echo esc_html( molms_lt( "Android Play Store" ) ); ?></b></a>, &nbsp;' +
                    '<a href="https://itunes.apple.com/in/app/authy/id494168017" target="_blank"><b><?php echo esc_html( molms_lt( "iOS App Store" ) ); ?>.</b>&nbsp;</p>');
                jQuery('#links_to_apps').show();
            } else {
                jQuery('#links_to_apps').html('<p style="background-color:#e8e4e4;padding:5px;">' +
                    'Get the App - <a href="https://play.google.com/store/apps/details?id=com.lastpass.authenticator" target="_blank"><b><?php echo esc_html( molms_lt( "Android Play Store" ) ); ?></b></a>, &nbsp;' +
                    '<a href="https://itunes.apple.com/in/app/lastpass-authenticator/id1079110004" target="_blank"><b><?php echo esc_html( molms_lt( "iOS App Store" ) ); ?>.</b>&nbsp;</p>');
                jQuery('#mo2f_change_app_name').show();
                jQuery('#links_to_apps').show();
            }
        });

        function molms_mologinback() {
            jQuery('#mo2f_backto_mo_loginform').submit();
        }

        jQuery('input:radio[name=mo2f_inline_app_type_radio]').click(function () {
            var selectedPhone = jQuery(this).val();
            document.getElementById("mo2f_inline_app_type_ga_form").elements[0].value = selectedPhone;
            jQuery('#mo2f_inline_app_type_ga_form').submit();
        });
        jQuery('a[href="#mo2f_scanbarcode_a"]').click(function () {
            jQuery("#mo2f_scanbarcode_a").toggle();
        });
        jQuery(document).ready(function () {
            jQuery('.mo2f_gauth').qrcode({
                'render': 'image',
                size: 175,
                'text': jQuery('.mo2f_gauth').data('qrcode')
            });
        });
    </script>
    </body>
	<?php
	echo '<head>';
	wp_register_script( 'molms_qrcode', plugins_url( "/includes/jquery-qrcode/jquery-qrcode.js", dirname( dirname( __FILE__ ) ) ) );
	wp_register_script( 'molms_qrcode_min', plugins_url( "/includes/jquery-qrcode/jquery-qrcode.min.js", dirname( dirname( __FILE__ ) ) ) );
	wp_print_scripts( 'molms_qrcode' );
	wp_print_scripts( 'molms_qrcode_min' );
	echo '</head>';
}

function molms_mo2f_inline_css_and_js() {
	wp_register_script( 'molms_bootstrap', plugins_url( 'includes/js/bootstrap.min.js', dirname( dirname( __FILE__ ) ) ) );
	wp_register_style( 'molms_bootstrap_min', plugins_url( 'includes/css/bootstrap.min.css', dirname( dirname( __FILE__ ) ) ) );
	wp_register_style( 'molms_front_end_login', plugins_url( 'includes/css/front_end_login.css', dirname( dirname( __FILE__ ) ) ) );
	wp_register_style( 'molms_style_settings', plugins_url( 'includes/css/style_settings.css', dirname( dirname( __FILE__ ) ) ) );
	wp_register_style( 'molms_hide_login', plugins_url( 'includes/css/hide-login.css', dirname( dirname( __FILE__ ) ) ) );
	wp_print_scripts( 'jquery-core' );
	wp_print_scripts( 'molms_bootstrap' );
	wp_print_styles( 'molms_bootstrap_min' );
	wp_print_styles( 'molms_front_end_login' );
	wp_print_styles( 'molms_style_settings' );
	wp_print_styles( 'molms_hide_login' );
}


function molms_initialize_inline_mobile_registration( $current_user, $session_id, $qrCode ) {
	$data                      = $qrCode;
	$mo2f_login_transaction_id = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_transactionId', $session_id );
	$url                       = MOLMS_HOST_NAME;
	$opt                       = molms_fetch_methods( $current_user ); ?>
    <p><?php echo esc_html__( 'Open your miniOrange', '2fa-learndash-lms' ); ?>
        <b> <?php echo esc_html__( 'Authenticator', '2fa-learndash-lms' ); ?></b> <?php echo esc_html__( 'app and click on', '2fa-learndash-lms' ); ?>
        <b><?php echo esc_html__( 'Configure button', '2fa-learndash-lms' ); ?> </b> <?php echo esc_html__( 'to scan the QR Code. Your phone should have internet connectivity to scan QR code.', '2fa-learndash-lms' ); ?>
    </p>
    <div class="red" style="color:#E74C3C;">
        <p><?php echo esc_html__( 'I am not able to scan the QR code,', '2fa-learndash-lms' ); ?> <a
                    data-toggle="mo2f_collapse" href="#mo2f_scanqrcode" aria-expanded="false"
                    style="color:#3498DB;"><?php echo esc_html__( 'click here ', '2fa-learndash-lms' ); ?></a></p></div>
    <div class="mo2f_collapse" id="mo2f_scanqrcode" style="margin-left:5px;">
		<?php echo esc_html__( 'Follow these instructions below and try again.', '2fa-learndash-lms' ); ?>
        <ol>
            <li><?php echo esc_html__( 'Make sure your desktop screen has enough brightness.', '2fa-learndash-lms' ); ?></li>
            <li><?php echo esc_html__( 'Open your app and click on Configure button to scan QR Code again.', '2fa-learndash-lms' ); ?></li>
            <li><?php echo esc_html__( 'If you get cross mark on QR Code then click on \'Refresh QR Code\' link.', '2fa-learndash-lms' ); ?></li>
        </ol>
    </div>
    <table class="mo2f_settings_table">
        <a href="#mo2f_refreshQRCode"
           style="color:#3498DB;"><?php echo esc_html__( 'Click here to Refresh QR Code.', '2fa-learndash-lms' ); ?></a>
        <div id="displayInlineQrCode"
             style="margin-left:36%;"><?php echo '<img style="width:200px;" src="data:image/jpg;base64,' . esc_html( $data ) . '" />'; ?>
        </div>
    </table>
    <center>
		<?php
		if ( sizeof( $opt ) > 1 ) { ?>
            <input type="button" name="back" id="mo2f_inline_back_btn" class="miniorange_button"
                   value="<?php echo esc_html__( 'Back', '2fa-learndash-lms' ); ?>"/>
		<?php } ?>
    </center>
    <script>
        jQuery('a[href="#mo2f_refreshQRCode"]').click(function (e) {
            jQuery('#mo2f_inline_configureapp_form').submit();
        });
        jQuery("#mo2f_configurePhone").empty();
        jQuery("#mo2f_app_div").hide();
        var timeout;
        molms_pollInlineMobileRegistration();

        function molms_pollInlineMobileRegistration() {
            var transId = "<?php echo esc_html( $mo2f_login_transaction_id ); ?>";
            var jsonString = "{\"txId\":\"" + transId + "\"}";
            var postUrl = "<?php echo esc_url_raw( $url ); ?>" + "/moas/api/auth/registration-status";
            jQuery.ajax({
                url: postUrl,
                type: "POST",
                dataType: "json",
                data: jsonString,
                contentType: "application/json; charset=utf-8",
                success: function (result) {
                    var status = JSON.parse(JSON.stringify(result)).status;
                    if (status == 'SUCCESS') {
                        var content = "<br/><div id='success'><img style='width:165px;margin-top:-1%;margin-left:2%;' src='" + "<?php echo esc_url_raw( plugins_url( 'includes/images/right.png', dirname( dirname( __FILE__ ) ) ) ); ?>" + "' /></div>";
                        jQuery("#displayInlineQrCode").empty();
                        jQuery("#displayInlineQrCode").append(content);
                        setTimeout(function () {
                            jQuery("#mo2f_inline_mobile_register_form").submit();
                        }, 1000);
                    } else if (status == 'ERROR' || status == 'FAILED') {
                        var content = "<br/><div id='error'><img style='width:165px;margin-top:-1%;margin-left:2%;' src='" + "<?php echo esc_url_raw( plugins_url( 'includes/images/wrong.png', __FILE__ ) ); ?>" + "' /></div>";
                        jQuery("#displayInlineQrCode").empty();
                        jQuery("#displayInlineQrCode").append(content);
                        jQuery("#messages").empty();
                        jQuery("#messages").append("<div class='error mo2f_error_container'> <p class='mo2f_msgs'>An Error occured processing your request. Please try again to configure your phone.</p></div>");
                    } else {
                        timeout = setTimeout(molms_pollInlineMobileRegistration, 3000);
                    }
                }
            });
        }
    </script>
	<?php
}

function molms_prompt_user_for_kba_setup( $current_user_id, $login_status, $login_message ) {
	$current_user = get_userdata( $current_user_id );
	$opt          = molms_fetch_methods( $current_user ); ?>
    <html>
    <head>
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		molms_mo2f_inline_css_and_js(); ?>
        <style>
            .mo2f_kba_ques, .mo2f_table_textbox {
                background: whitesmoke none repeat scroll 0% 0%;
            }
        </style>
    </head>
    <body>
    <div class="mo2f_modal" tabindex="-1" role="dialog" id="myModal5">
        <div class="mo2f-modal-backdrop"></div>
        <div class="mo2f_modal-dialog mo2f_modal-lg">
            <div class="login mo_customer_validation-modal-content">
                <div class="mo2f_modal-header">
                    <h4 class="mo2f_modal-title">
                        <button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
                                title="<?php echo esc_html__( 'Back to login', '2fa-learndash-lms' ); ?>"
                                onclick="molms_mologinback();"><span aria-hidden="true">&times;</span></button>
						<?php echo esc_html__( 'Setup Security Question (KBA)', '2fa-learndash-lms' ); ?></h4>
                </div>
                <div class="mo2f_modal-body">
					<?php if ( isset( $login_message ) && ! empty( $login_message ) ) { ?>
                        <div id="otpMessage">
                            <p class="mo2fa_display_message_frontend"
                               style="text-align: left !important;"><?php echo esc_html__( $login_message, '2fa-learndash-lms' ); ?></p>
                        </div>
					<?php } ?>
                    <form name="f" method="post" action="">
						<?php molms_configure_kba_questions(); ?>
                        <br/>
                        <div class="row">
                            <div class="col-md-4" style="margin: 0 auto;width: 100px;">
                                <input type="submit" name="validate" class="miniorange_button"
                                       style="width: 30%;background-color:#ff4168;"
                                       value="<?php echo esc_html__( 'Save', '2fa-learndash-lms' ); ?>"/>
                                <button type="button" class="miniorange_button"
                                        style="width: 30%;background-color:#ff4168;" onclick="molms_mobackinline();">
                                    Back
                                </button>

                            </div>
                        </div>
                        <input type="hidden" name="option" value="mo2f_inline_kba_option"/>
                        <input type="hidden" name="mo2f_inline_save_kba_nonce"
                               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-save-kba-nonce' ) ); ?>"/>
                    </form>
					<?php if ( sizeof( $opt ) > 1 ) { ?>
                        <form name="f" method="post" action="" id="mo2f_goto_two_factor_form"
                              class="molms_display_none_forms">
                            <div class="row">
                                <div class="col-md-4" style="margin: 0 auto;width: 100px;">
                                    <input type="hidden" name="option" value="miniorange_back_inline"/>
                                </div>
                            </div>
                            <input type="hidden" name="miniorange_inline_two_factor_setup"
                                   value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-setup-nonce' ) ); ?>"/>
                        </form>
					<?php } ?>

					<?php molms_customize_logo() ?>
                </div>
            </div>
        </div>
    </div>
    <form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
          style="display:none;">
        <input type="hidden" name="miniorange_mobile_validation_failed_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
    </form>

    <script>


        function molms_mologinback() {
            jQuery('#mo2f_backto_mo_loginform').submit();
        }

        function molms_mobackinline() {
            jQuery('#mo2f_goto_two_factor_form').submit();
        }
    </script>
    </body>
    </html>
	<?php
}

function molms_prompt_user_for_miniorange_register( $current_user_id, $login_status, $login_message ) {
	$current_user = get_userdata( $current_user_id );
	$opt          = molms_fetch_methods( $current_user ); ?>
    <html>
    <head>
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		molms_mo2f_inline_css_and_js(); ?>
        <style>
            .mo2f_kba_ques, .mo2f_table_textbox {
                background: whitesmoke none repeat scroll 0% 0%;
            }
        </style>
    </head>
    <body>
    <div class="mo2f_modal" tabindex="-1" role="dialog" id="myModal5">
        <div class="mo2f-modal-backdrop"></div>
        <div class="mo2f_modal-dialog mo2f_modal-lg">
            <div class="login mo_customer_validation-modal-content">
                <div class="mo2f_modal-header">
                    <h3 class="mo2f_modal-title" style="color:black;">
                        <button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
                                title="<?php echo esc_html__( 'Back to login', '2fa-learndash-lms' ); ?>"
                                onclick="molms_mologinback();"><span aria-hidden="true">&times;</span></button>
                        <b> <?php echo esc_html__( 'Connect with miniOrange', '2fa-learndash-lms' ); ?></b></h3>
                </div>
                <div class="mo2f_modal-body">
					<?php if ( isset( $login_message ) && ! empty( $login_message ) ) { ?>
                        <div id="otpMessage">
                            <p class="mo2fa_display_message_frontend"
                               style="text-align: left !important;"><?php echo esc_html( $login_message ); ?></p>
                        </div>
					<?php } ?>
                    <form name="mo2f_inline_register_form" id="mo2f_inline_register_form" method="post" action="">
						<?php wp_nonce_field( 'miniorange_inline_register' ); ?>
                        <input type="hidden" name="option" value="miniorange_inline_register"/>
                        <p>This method requires you to have an account with miniOrange.</p>
                        <table class="mo_lms_settings_table">
                            <tr>
                                <td><b><font color="#FF0000">*</font>Email:</b></td>
                                <td><input class="mo_lms_table_textbox" type="email" name="email"
                                           required placeholder="person@example.com"/></td>
                            </tr>
                            <tr>
                                <td><b><font color="#FF0000">*</font>Password:</b></td>
                                <td><input class="mo_lms_table_textbox" required type="password"
                                           name="password" placeholder="Choose your password (Min. length 6)"/></td>
                            </tr>
                            <tr>
                                <td><b><font color="#FF0000">*</font>Confirm Password:</b></td>
                                <td><input class="mo_lms_table_textbox" required type="password"
                                           name="confirmPassword" placeholder="Confirm your password"/></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td><br><input type="submit" name="submit" value="Create Account"
                                               class="miniorange_button"/>
                                    <a href="#mo2f_account_exist">Already have an account?</a>
                            </tr>
                        </table>
                    </form>
                    <form name="f" id="mo2f_inline_login_form" method="post" action="" hidden>
                        <p><b>It seems you already have an account with miniOrange. Please enter your miniOrange email
                                and password.<br></b><a target="_blank"
                                                        href="https://login.xecurify.com/moas/idp/resetpassword"> Click
                                here if you forgot your password?</a></p>
                        <input type="hidden" name="option" value="miniorange_inline_login"/>
						<?php wp_nonce_field( 'miniorange_inline_login' ); ?>
                        <table class="mo_lms_settings_table">
                            <tr>
                                <td><b><font color="#FF0000">*</font>Email:</b></td>
                                <td><input class="mo_lms_table_textbox" type="email" name="email"
                                           required placeholder="person@example.com"
                                    /></td>
                            </tr>
                            <tr>
                                <td><b><font color="#FF0000">*</font>Password:</b></td>
                                <td><input class="mo_lms_table_textbox" required type="password"
                                           name="password" placeholder="Enter your miniOrange password"/></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td><input type="submit" class="miniorange_button"/>
                                    <input type="button" id="cancel_link" class="miniorange_button"
                                           value="<?php echo esc_html__( 'Go Back to Registration', '2fa-learndash-lms' ); ?>"/>
                            </tr>
                        </table>
                    </form>
                    <br>
                    <input type="button" name="back" id="mo2f_inline_back_btn" class="miniorange_button"
                           value="<?php echo esc_html__( '<< Back to Menu', '2fa-learndash-lms' ); ?>"/>
					<?php molms_customize_logo() ?>
                </div>
            </div>
        </div>
    </div>
    <form name="f" method="post" action="" id="mo2f_goto_two_factor_form">
        <input type="hidden" name="option" value="miniorange_back_inline"/>
        <input type="hidden" name="miniorange_inline_two_factor_setup"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-setup-nonce' ) ); ?>"/>
    </form>
    <form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
          style="display:none;">
        <input type="hidden" name="miniorange_mobile_validation_failed_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
    </form>

    <script>
        jQuery('#mo2f_inline_back_btn').click(function () {
            jQuery('#mo2f_goto_two_factor_form').submit();
        });
        jQuery('a[href=\"#mo2f_account_exist\"]').click(function (e) {
            jQuery('#mo2f_inline_login_form').show();
            jQuery('#mo2f_inline_register_form').hide();
        });
        jQuery('#cancel_link').click(function () {
            jQuery('#mo2f_inline_register_form').show();
            jQuery('#mo2f_inline_login_form').hide();
        });

        function molms_mologinback() {
            jQuery('#mo2f_backto_mo_loginform').submit();
        }
    </script>
    </body>
    </html>
	<?php
}

function molms_prompt_user_for_phone_setup( $current_user_id, $login_status, $login_message, $currentMethod ) {
	$current_user = get_userdata( $current_user_id );
	$opt          = molms_fetch_methods( $current_user );
	global $molms_db_queries;
	$current_selected_method = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $current_user_id );
	$current_user            = get_userdata( $current_user_id );
	$email                   = $current_user->user_email; ?>
    <html>
    <head>
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		molms_mo2f_inline_css_and_js();
		wp_register_script( 'molms_phone', plugins_url( 'includes/js/phone.js', dirname( dirname( __FILE__ ) ) ) );
		wp_register_style( 'molms_phone', plugins_url( 'includes/css/phone.css', dirname( dirname( __FILE__ ) ) ) );
		wp_print_scripts( 'molms_phone' );
		wp_print_styles( 'molms_phone' ); ?>
    </head>
    <body>
    <div class="mo2f_modal" tabindex="-1" role="dialog" id="myModal5">
        <div class="mo2f-modal-backdrop"></div>
        <div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
            <div class="login mo_customer_validation-modal-content">
                <div class="mo2f_modal-header">
                    <h4 class="mo2f_modal-title">
                        <button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
                                title="<?php echo esc_html__( 'Back to login', '2fa-learndash-lms' ); ?>"
                                onclick="molms_mologinback();"><span aria-hidden="true">&times;</span></button>
						<?php
						if ( $current_selected_method == 'SMS AND EMAIL' ) { ?>
						<?php echo esc_html__( 'Verify Your Phone and Email', '2fa-learndash-lms' ); ?></h4>
					<?php } elseif ( $current_selected_method == 'OTP OVER EMAIL' ) {
						?>
						<?php echo esc_html__( 'Verify Your EMAIL', '2fa-learndash-lms' ); ?></h4>
						<?php
					} else {
						?>
						<?php echo esc_html__( 'Verify Your Phone', '2fa-learndash-lms' ); ?></h3>
						<?php
					} ?>
                </div>
                <div class="mo2f_modal-body">
					<?php if ( isset( $login_message ) && ! empty( $login_message ) ) { ?>
                        <div id="otpMessage"
						     <?php if ( get_user_meta( $current_user_id, 'mo2f_is_error', true ) ) {
						     ?>style="background-color:#FADBD8; color:#E74C3C;?>"<?php update_user_meta( $current_user_id, 'mo2f_is_error', false );
						} ?>
                        >
                            <p class="mo2fa_display_message_frontend"
                               style="text-align: left !important; "> <?php echo esc_html( $login_message ); ?></p>
                        </div>
						<?php if ( isset( $login_message ) ) {
							?> <br/> <?php
						} ?>
					<?php } ?>
                    <div class="mo2f_row">
                        <form name="f" method="post" action="" id="mo2f_inline_verifyphone_form">
                            <p>
								<?php
								if ( $current_selected_method == 'SMS AND EMAIL' ) { ?>
								<?php echo esc_html__( 'Enter your phone number. An One Time Passcode(OTP) wll be sent to this number and your email address.', '2fa-learndash-lms' ); ?></p>
							<?php
							} elseif ( $current_selected_method == 'OTP OVER EMAIL' ) {
								//no message
							} else {
								?>
								<?php echo esc_html__( 'Enter your phone number', '2fa-learndash-lms' ); ?></h4>
								<?php
							}
							if ( ! ( $current_selected_method == 'OTP OVER EMAIL' ) ) {
								?>
                                <input class="mo2f_table_textbox" type="text" name="verify_phone" id="phone"
                                       style="width:15em;"
                                       value="<?php echo esc_html( get_user_meta( $current_user_id, 'mo2f_user_phone', true ) ); ?>"
                                       pattern="[\+]?[0-9]{1,4}\s?[0-9]{7,12}" required="true"
                                       title="<?php echo esc_html__( 'Enter phone number without any space or dashes', '2fa-learndash-lms' ); ?>"/>
                                <br/>
								<?php
							} ?>
							<?php
							$email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user_id );
							if ( $current_selected_method == 'SMS AND EMAIL' || $current_selected_method == 'OTP OVER EMAIL' ) { ?>
                                <input class="mo2f_IR_phone" type="text" name="verify_email" id="email"
                                       value="<?php echo esc_html( $email ); ?>"
                                       title="<?php echo esc_html__( 'Enter your email', '2fa-learndash-lms' ); ?>"
                                       style="width: 250px;" disabled/><br/>
							<?php } ?>
                            <input type="submit" name="verify" class="miniorange_button"
                                   value="<?php echo esc_html__( 'Send OTP', '2fa-learndash-lms' ); ?>"/>
                            <input type="hidden" name="option" value="miniorange_inline_complete_otp_over_sms"/>
                            <input type="hidden" name="miniorange_inline_verify_phone_nonce"
                                   value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-verify-phone-nonce' ) ); ?>"/>
                        </form>
                    </div>
                    <form name="f" method="post" action="" id="mo2f_inline_validateotp_form">
                        <p>
							<?php
							if ( $current_selected_method == 'SMS AND EMAIL' ) { ?>
                        <h4><?php echo esc_html__( 'Enter One Time Passcode', '2fa-learndash-lms' ); ?></h4>
						<?php } else {
							?>
							<?php echo esc_html( molms_lt( 'Please enter the One Time Passcode sent to your phone.' ) ); ?></p>
							<?php
						} ?>
                        <input class="mo2f_IR_phone_OTP" required="true" pattern="[0-9]{4,8}" autofocus="true"
                               type="text" name="otp_token"
                               placeholder="<?php echo esc_html__( 'Enter the code', '2fa-learndash-lms' ); ?>"
                               id="otp_token"/><br>
                        <span style="color:#1F618D;"><?php echo esc_html( molms_lt( 'Didn\'t get code?' ) ); ?></span>
                        &nbsp;
						<?php if ( $current_selected_method == 'PHONE VERIFICATION' ) { ?>
                            <a href="#resendsmslink"
                               style="color:#F4D03F ;font-weight:bold;"><?php echo esc_html__( 'CALL AGAIN', '2fa-learndash-lms' ); ?></a>
						<?php } else { ?>
                            <a href="#resendsmslink"
                               style="color:#F4D03F ;font-weight:bold;"><?php echo esc_html__( 'RESEND IT', '2fa-learndash-lms' ); ?></a>
						<?php } ?>
                        <br/><br/>
                        <input type="submit" name="validate" class="miniorange_button"
                               value="<?php echo esc_html__( 'Verify Code', '2fa-learndash-lms' ); ?>"/>
						<?php if ( sizeof( $opt ) > 1 ) { ?>
                            <input type="hidden" name="option" value="miniorange_back_inline"/>
                            <input type="button" name="back" id="mo2f_inline_back_btn" class="miniorange_button"
                                   value="<?php echo esc_html__( 'Back', '2fa-learndash-lms' ); ?>"/>
						<?php } ?>
                        <input type="hidden" name="option" value="miniorange_inline_complete_otp"/>
                        <input type="hidden" name="miniorange_inline_validate_otp_nonce"
                               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-validate-otp-nonce' ) ); ?>"/>
                    </form>
					<?php molms_customize_logo() ?>
                </div>
            </div>
        </div>
    </div>
    <form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
          style="display:none;">
        <input type="hidden" name="miniorange_mobile_validation_failed_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
    </form>
    <form name="f" method="post" action="" id="mo2fa_inline_resend_otp_form" style="display:none;">
        <input type="hidden" name="option" value="miniorange_inline_complete_otp_over_sms"/>
        <input type="hidden" name="miniorange_inline_verify_phone_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-verify-phone-nonce' ) ); ?>"/>
    </form>
	<?php if ( count( $opt ) > 1 ) { ?>
        <form name="f" method="post" action="" id="mo2f_goto_two_factor_form">
            <input type="hidden" name="option" value="miniorange_back_inline"/>
            <input type="hidden" name="miniorange_inline_two_factor_setup"
                   value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-setup-nonce' ) ); ?>"/>
        </form>
	<?php } ?>
    <script>
        jQuery("#phone").intlTelInput();
        phone = jQuery("#phone").val();
        if (phone == '')
            jQuery("#phone").val('+1');

        function molms_mologinback() {
            jQuery('#mo2f_backto_mo_loginform').submit();
        }

        jQuery('#mo2f_inline_back_btn').click(function () {
            jQuery('#mo2f_goto_two_factor_form').submit();
        });
        jQuery('a[href="#resendsmslink"]').click(function (e) {
            jQuery('#mo2fa_inline_resend_otp_form').submit();
        });
    </script>
    </body>

    </html>
	<?php
}
