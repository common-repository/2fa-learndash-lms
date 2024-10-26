<?php
function molms_collect_device_attributes_handler( $session_id_encrypt, $redirect_to = null ) {
	?>
    <html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_enqueue_script( 'jQuery' ); ?>
    </head>
    <body>
    <div>
        <form id="morba_loginform" method="post">
            <h1><?php echo esc_html( molms_lt( 'Please wait' ) ); ?>...</h1>
            <img src="<?php echo esc_url_raw( plugins_url( 'includes/images/ajax-loader-login.gif', dirname( dirname( dirname( __FILE__ ) ) ) ) ); ?>"/>
			<?php
			if ( get_site_option( 'mo2f_remember_device' ) ) {
				?>
                <p><input type="hidden" id="miniorange_rba_attribures" name="miniorange_rba_attribures" value=""/></p>
				<?php
				wp_enqueue_script( 'jquery_script', plugins_url( 'includes/js/rba/js/jquery-1.9.1.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
				wp_enqueue_script( 'flash_script', plugins_url( 'includes/js/rba/js/jquery.flash.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
				wp_enqueue_script( 'uaparser_script', plugins_url( 'includes/js/rba/js/ua-parser.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
				wp_enqueue_script( 'client_script', plugins_url( 'includes/js/rba/js/client.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
				wp_enqueue_script( 'device_script', plugins_url( 'includes/js/rba/js/device_attributes.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
				wp_enqueue_script( 'swf_script', plugins_url( 'includes/js/rba/js/swfobject.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
				wp_enqueue_script( 'font_script', plugins_url( 'includes/js/rba/js/fontdetect.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
				wp_enqueue_script( 'murmur_script', plugins_url( 'includes/js/rba/js/murmurhash3.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
				wp_enqueue_script( 'miniorange_script', plugins_url( 'includes/js/rba/js/miniorange-fp.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
			} ?>
            <input type="hidden" name="miniorange_attribute_collection_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-login-attribute-collection-nonce' ) ); ?>"/>
            <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
            <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
        </form>
    </div>
    </body>
    </html>
	<?php
}

function molms_get_user_role( $user ) {
	return $user->roles;
}

function molms_redirect_user_to( $user, $redirect_to ) {
	$roles        = $user->roles;
	$current_role = array_shift( $roles );
	$redirectUrl  = isset( $redirect_to ) && ! empty( $redirect_to ) ? $redirect_to : null;
	if ( $current_role == 'administrator' ) {
		$redirectUrl = empty( $redirectUrl ) ? admin_url() : $redirectUrl;
	} else {
		$redirectUrl = empty( $redirectUrl ) ? home_url() : $redirectUrl;
	}
	if ( molms_2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
		$redirect = array(
			'redirect' => $redirectUrl,
		);

		wp_send_json_success( $redirect );
	} else {
		wp_safe_redirect( $redirectUrl );
	}
}


// used in shortcode addon

function molms_check_if_2fa_enabled_for_roles( $current_roles ) {
	if ( empty( $current_roles ) ) {
		return 0;
	}

	foreach ( $current_roles as $value ) {
		if ( get_site_option( 'mo2fa_' . $value ) ) {
			return 1;
		}
	}

	return 0;
}

function molms_register_profile( $email, $deviceKey, $mo2f_rba_status ) {
	if ( isset( $deviceKey ) && $deviceKey == 'true' ) {
		if ( $mo2f_rba_status['status'] == 'WAIT_FOR_INPUT' && $mo2f_rba_status['decision_flag'] ) {
			$rba_profile = new Molms_Miniorange_Rba_Attributes();
			//register profile
			json_decode( $rba_profile->mo2f_register_rba_profile( $email, $mo2f_rba_status['sessionUuid'] ), true );

			return true;
		} else {
			return false;
		}
	}

	return false;
}

function molms_collect_attributes( $email, $attributes ) {
	$mo2f_rba_status                  = array();
	$mo2f_rba_status['decision_flag'] = false;
	$mo2f_rba_status['sessionUuid']   = '';

	if ( get_site_option( 'mo2f_remember_device' ) ) {
		$rba_attributes = new Molms_Miniorange_Rba_Attributes();
		//collect rba attributes
		$rba_response = json_decode( $rba_attributes->mo2f_collect_attributes( $email, $attributes ), true );
		if ( json_last_error() == JSON_ERROR_NONE ) {
			//attributes are collected successfully
			if ( $rba_response['status'] == 'SUCCESS' ) {
				$sessionUuid = $rba_response['sessionUuid'];
				// evaluate the rba risk
				$rba_risk_response = json_decode( $rba_attributes->mo2f_evaluate_risk( $email, $sessionUuid ), true );

				if ( json_last_error() == JSON_ERROR_NONE ) {
					if ( $rba_risk_response['status'] == 'SUCCESS' || $rba_risk_response['status'] == 'WAIT_FOR_INPUT' ) {
						$mo2f_rba_status['status']        = $rba_risk_response['status'];
						$mo2f_rba_status['sessionUuid']   = $sessionUuid;
						$mo2f_rba_status['decision_flag'] = true;
					} else {
						$mo2f_rba_status['status']      = $rba_risk_response['status'];
						$mo2f_rba_status['sessionUuid'] = $sessionUuid;
					}
				} else {
					$mo2f_rba_status['status']      = 'JSON_EVALUATE_ERROR';
					$mo2f_rba_status['sessionUuid'] = $sessionUuid;
				}
			} else {
				$mo2f_rba_status['status'] = 'ATTR_NOT_COLLECTED';
			}
		} else {
			$mo2f_rba_status['status'] = 'JSON_ATTR_NOT_COLLECTED';
		}
	} else {
		$mo2f_rba_status['status'] = 'RBA_NOT_ENABLED';
	}

	return $mo2f_rba_status;
}

function molms_get_user_2ndfactor( $user ) {
	global $molms_db_queries;
	$mo2f_user_email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
	$enduser         = new Molms_Two_Factor_Setup();
	$userinfo        = json_decode( $enduser->mo2f_get_userinfo( $mo2f_user_email ), true );
	if ( json_last_error() == JSON_ERROR_NONE ) {
		if ( $userinfo['status'] == 'ERROR' ) {
			$mo2f_second_factor = 'NONE';
		} elseif ( $userinfo['status'] == 'SUCCESS' ) {
			$mo2f_second_factor = $userinfo['authType'];
		} elseif ( $userinfo['status'] == 'FAILED' ) {
			$mo2f_second_factor = 'USER_NOT_FOUND';
		} else {
			$mo2f_second_factor = 'NONE';
		}
	} else {
		$mo2f_second_factor = 'NONE';
	}

	return $mo2f_second_factor;
}

function molms_get_forgotphone_form( $login_status, $login_message, $redirect_to, $session_id_encrypt ) {
	$mo2f_forgotphone_enabled     = molms_Utility::get_mo2f_db_option( 'mo2f_enable_forgotphone', 'get_site_option' );
	$mo2f_email_as_backup_enabled = get_site_option( 'mo2f_enable_forgotphone_email' );
	$mo2f_kba_as_backup_enabled   = get_site_option( 'mo2f_enable_forgotphone_kba' ); ?>
    <html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		molms_echo_js_css_files(); ?>
    </head>
    <body>
    <div class="mo2f_modal" tabindex="-1" role="dialog">
        <div class="mo2f-modal-backdrop"></div>
        <div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
            <div class="login mo_customer_validation-modal-content">
                <div class="mo2f_modal-header">
                    <h4 class="mo2f_modal-title">
                        <button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
                                title="<?php echo esc_html( molms_lt( 'Back to login' ) ); ?>"
                                onclick="molms_loginback();"><span aria-hidden="true">&times;</span></button>
						<?php echo esc_html( molms_lt( 'How would you like to authenticate yourself?' ) ); ?>
                    </h4>
                </div>
                <div class="mo2f_modal-body">
					<?php if ( $mo2f_forgotphone_enabled ) {
						if ( isset( $login_message ) && ! empty( $login_message ) ) { ?>
                            <div id="otpMessage" class="mo2fa_display_message_frontend">
                                <p class="mo2fa_display_message_frontend"><?php echo esc_html( $login_message ); ?></p>
                            </div>
						<?php } ?>
                        <p class="mo2f_backup_options"><?php echo esc_html( molms_lt( 'Please choose the options from below:' ) ); ?></p>
                        <div class="mo2f_backup_options_div">
							<?php if ( $mo2f_email_as_backup_enabled ) { ?>
                                <input type="radio" name="mo2f_selected_forgotphone_option"
                                       value="One Time Passcode over Email"
                                       checked="checked"/><?php echo esc_html( molms_lt( 'Send a one time passcode to my registered email' ) ); ?>
                                <br><br>
							<?php }
							if ( $mo2f_kba_as_backup_enabled ) { ?>
                                <input type="radio" name="mo2f_selected_forgotphone_option"
                                       value="KBA"/><?php echo esc_html( molms_lt( 'Answer your Security Questions (KBA)' ) ); ?>
							<?php } ?>
                            <br><br>
                            <input type="button" name="miniorange_validate_otp"
                                   value="<?php echo esc_html( molms_lt( 'Continue' ) ); ?>"
                                   class="miniorange_validate_otp"
                                   onclick="mo2fselectforgotphoneoption();"/>
                        </div>
						<?php molms_customize_logo();
					} ?>
                </div>
            </div>
        </div>
    </div>
    <form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
          class="molms_display_none_forms">
        <input type="hidden" name="miniorange_mobile_validation_failed_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
    </form>
    <form name="f" id="mo2f_challenge_forgotphone_form" method="post" class="molms_display_none_forms">
        <input type="hidden" name="mo2f_configured_2FA_method"/>
        <input type="hidden" name="miniorange_challenge_forgotphone_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-challenge-forgotphone-nonce' ) ); ?>"/>
        <input type="hidden" name="option" value="miniorange_challenge_forgotphone">
        <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
    </form>

    <script>
        function molms_loginback() {
            jQuery('#mo2f_backto_mo_loginform').submit();
        }

        function molms_selectforgotphoneoption() {
            var option = jQuery('input[name=mo2f_selected_forgotphone_option]:checked').val();
            document.getElementById("mo2f_challenge_forgotphone_form").elements[0].value = option;
            jQuery('#mo2f_challenge_forgotphone_form').submit();
        }
    </script>
    </body>
    </html>
	<?php
}

function molms_get_kba_authentication_prompt( $login_message, $redirect_to, $session_id_encrypt, $cookievalue ) {
	$mo2f_login_option            = molms_Utility::get_mo2f_db_option( 'mo2f_login_option', 'get_site_option' );
	$mo2f_remember_device_enabled = get_site_option( 'mo2f_remember_device' );
	$mo_lms_config                = new molms_Utility(); ?>
    <html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		molms_echo_js_css_files();
		?>
    </head>
    <body>
    <div class="mo2f_modal" tabindex="-1" role="dialog">
        <div class="mo2f-modal-backdrop"></div>
        <div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
            <div class="login mo_customer_validation-modal-content">
                <div class="mo2f_modal-header">
                    <h4 class="mo2f_modal-title">
                        <button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
                                title="<?php echo esc_html( molms_lt( 'Back to login' ) ); ?>"
                                onclick="molms_loginback();"><span aria-hidden="true">&times;</span></button>
						<?php
						echo esc_html( molms_lt( 'Validate Security Questions' ) ); ?>
                    </h4>
                </div>
                <div class="mo2f_modal-body">
                    <div id="kbaSection" class="kbaSectiondiv">
                        <div id="otpMessage">
                            <p style="font-size:13px;"
                               class="mo2fa_display_message_frontend"><?php echo ( isset( $login_message ) && ! empty( $login_message ) ) ? esc_html( $login_message ) : __( 'Please answer the following questions:' ); ?></p>
                        </div>
                        <form name="f" id="mo2f_submitkba_loginform" method="post">
                            <div id="mo2f_kba_content">
                                <p style="font-size:15px;">
									<?php $kba_questions = $cookievalue;
									echo esc_html( $kba_questions[0]['question'] ); ?><br>
                                    <input class="mo2f-textbox" type="password" name="mo2f_answer_1" id="mo2f_answer_1"
                                           required="true" autofocus="true"
                                           pattern="(?=\S)[A-Za-z0-9_@.$#&amp;+\-\s]{1,100}"
                                           title="Only alphanumeric letters with special characters(_@.$#&amp;+-) are allowed."
                                           autocomplete="off"><br>
									<?php echo esc_html( $kba_questions[1]['question'] ); ?><br>
                                    <input class="mo2f-textbox" type="password" name="mo2f_answer_2" id="mo2f_answer_2"
                                           required="true" pattern="(?=\S)[A-Za-z0-9_@.$#&amp;+\-\s]{1,100}"
                                           title="Only alphanumeric letters with special characters(_@.$#&amp;+-) are allowed."
                                           autocomplete="off">

                                </p>
                            </div>
							<?php if ( $mo2f_login_option && $mo2f_remember_device_enabled ) {
								?>
                                <span class="mo2f_rememberdevice">
                                    <input type="checkbox" name="mo2f_trust_device" class="mo2f_trust_device"
                                           id="mo2f_trust_device"/><?php echo esc_html( molms_lt( 'Remember this device.' ) ); ?>
                                </span>
                                <br>
                                <br>
								<?php
							} ?>
                            <input type="submit" name="miniorange_kba_validate" id="miniorange_kba_validate"
                                   class="miniorange_kba_validate" style="float:left;"
                                   value="<?php echo esc_html( molms_lt( 'Validate' ) ); ?>"/>
                            <input type="hidden" name="miniorange_kba_nonce"
                                   value="<?php echo esc_attr( wp_create_nonce( 'mo2f-validate-kba-details-nonce' ) ); ?>"/>
                            <input type="hidden" name="option"
                                   value="miniorange_kba_validate"/>
                            <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
                            <input type="hidden" name="session_id"
                                   value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
                        </form>
                        <br>
                    </div>
                    <div style="padding:10px;">
                        <p><a href="<?php echo esc_url_raw( $mo_lms_config->lockedOutlink() ); ?>" target="_blank"
                              style="color:#ca2963;font-weight:bold;">I'm locked out & unable to login.</a></p>
                    </div>

					<?php molms_customize_logo(); ?>

                </div>
            </div>
        </div>
    </div>
    <form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
          class="molms_display_none_forms">
        <input type="hidden" name="miniorange_mobile_validation_failed_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
    </form>

    <script>
        var is_ajax = "<?php echo esc_html( molms_2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ); ?>";
        if (is_ajax) {
            jQuery('#mo2f_answer_1').keypress(function (e) {
                if (e.which == 13) {//Enter key pressed
                    e.preventDefault();
                    mo2f_kba_ajax();
                }
            });
            jQuery('#mo2f_answer_2').keypress(function (e) {
                if (e.which == 13) {//Enter key pressed
                    e.preventDefault();
                    mo2f_kba_ajax();
                }
            });
            jQuery("#miniorange_kba_validate").click(function (e) {
                e.preventDefault();
                mo2f_kba_ajax();
            });

            function molms_kba_ajax() {
                jQuery('#mo2f_answer_1').prop('disabled', 'true');
                jQuery('#mo2f_answer_2').prop('disabled', 'true');
                jQuery('#miniorange_kba_validate').prop('disabled', 'true');
                var data = {
                    "action": "mo2f_ajax",
                    "mo2f_ajax_option": "mo2f_ajax_kba",
                    "mo2f_answer_1": jQuery("input[name=\'mo2f_answer_1\']").val(),
                    "mo2f_answer_2": jQuery("input[name=\'mo2f_answer_2\']").val(),
                    "miniorange_kba_nonce": jQuery("input[name=\'miniorange_kba_nonce\']").val(),
                    "session_id": jQuery("input[name=\'session_id\']").val(),
                    "redirect_to": jQuery("input[name=\'redirect_to\']").val(),
                    "mo2f_trust_device": jQuery("input[name=\'mo2f_trust_device\']").val(),
                };
                jQuery.post(my_ajax_object.ajax_url, data, function (response) {
                    if (typeof response.data === "undefined") {
                        jQuery("html").html(response);
                    } else
                        location.href = response.data.redirect;
                });
            }
        }

        function molms_loginback() {
            jQuery('#mo2f_backto_mo_loginform').submit();
        }
    </script>
    </body>

    </html>
	<?php
}

function molms_get_push_notification_oobemail_prompt( $id, $login_status, $login_message, $redirect_to, $session_id_encrypt, $cookievalue ) {
	$mo_lms_config = new molms_Utility();

	global $molms_db_queries, $txid;
	$mo2f_enable_forgotphone = molms_Utility::get_mo2f_db_option( 'mo2f_enable_forgotphone', 'get_site_option' );
	$mo2f_KBA_config_status  = $molms_db_queries->get_user_detail( 'mo2f_SecurityQuestions_config_status', $id );
	$mo2f_is_new_customer    = molms_Utility::get_mo2f_db_option( 'mo2f_is_NC', 'get_site_option' );
	$mo2f_EV_txid            = get_user_meta( $id, 'mo2f_EV_txid', true );
	if ( ! MOLMS_IS_ONPREM ) {
		$mo2f_EV_txid = sanitize_text_field( $_SESSION['mo2f_transactionId'] );
	} ?>
    <html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		molms_echo_js_css_files(); ?>
    </head>
    <body>
    <div class="mo2f_modal" tabindex="-1" role="dialog">
        <div class="mo2f-modal-backdrop"></div>
        <div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
            <div class="login mo_customer_validation-modal-content">
                <div class="mo2f_modal-header">
                    <h4 class="mo2f_modal-title">
                        <button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
                                title="<?php echo esc_html( molms_lt( 'Back to login' ) ); ?>"
                                onclick="molms_loginback();"><span aria-hidden="true">&times;</span></button>
						<?php echo esc_html( molms_lt( 'Accept Your Transaction' ) ); ?></h4>
                </div>
                <div class="mo2f_modal-body">
					<?php if ( isset( $login_message ) && ! empty( $login_message ) ) { ?>
                        <div id="otpMessage">
                            <p class="mo2fa_display_message_frontend"><?php echo esc_html( $login_message ); ?></p>
                        </div>
					<?php } ?>
                    <div id="pushSection">

                        <div>
                            <center>
                                <p class="mo2f_push_oob_message"><?php echo esc_html( molms_lt( 'Waiting for your approval...' ) ); ?></p>
                            </center>
                        </div>
                        <div id="showPushImage">
                            <center>
                                <img src="<?php echo esc_url_raw( plugins_url( 'includes/images/ajax-loader-login.gif', dirname( dirname( __FILE__ ) ) ) ); ?>"/>
                            </center>
                        </div>


                        <span style="padding-right:2%;">
                           <?php if ( isset( $login_status ) && $login_status == 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS' ) { ?>
                               <center>
                                   <?php if ( $mo2f_enable_forgotphone && ! $mo2f_is_new_customer ) { ?>
                                       <input type="button" name="miniorange_login_forgotphone"
                                              onclick="molms_loginforgotphone();" id="miniorange_login_forgotphone"
                                              class="miniorange_login_forgotphone"
                                              value="<?php echo esc_html( molms_lt( 'Forgot Phone?' ) ); ?>"/>
                                   <?php } ?>
                                   &emsp;&emsp;
                              <input type="button" name="miniorange_login_offline" onclick="molms_loginoffline();"
                                     id="miniorange_login_offline" class="miniorange_login_offline"
                                     value="<?php echo esc_html( molms_lt( 'Phone is Offline?' ) ); ?>"/>
                           </center>
                           <?php } elseif ( isset( $login_status ) && $login_status == 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL' && $mo2f_enable_forgotphone && $mo2f_KBA_config_status ) { ?>
                               <center>
                              <a href="#mo2f_alternate_login_kba">
                                 <p class="mo2f_push_oob_backup"><?php echo esc_html( molms_lt( 'Didn\'t receive mail?' ) ); ?></p>
                              </a>
                           </center>
                           <?php } ?>
                        </span>
                        <center>
                            <div style="padding:10px;">
                                <p><a href="<?php echo esc_url_raw( $mo_lms_config->lockedOutlink() ); ?>"
                                      target="_blank" style="color:#ca2963;font-weight:bold;">I'm locked out & unable to
                                        login.</a></p>
                            </div>
                        </center>
                    </div>

					<?php molms_customize_logo(); ?>
                </div>
            </div>
        </div>
    </div>
    <form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
          class="molms_display_none_forms">
        <input type="hidden" name="miniorange_mobile_validation_failed_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
        <input type="hidden" name="option" value="miniorange_mobile_validation_failed">
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
        <input type="hidden" name="currentMethod" value="emailVer"/>

    </form>
    <form name="f" id="mo2f_mobile_validation_form" method="post" class="molms_display_none_forms">
        <input type="hidden" name="miniorange_mobile_validation_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-nonce' ) ); ?>"/>
        <input type="hidden" name="option" value="miniorange_mobile_validation">
        <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
        <input type="hidden" name="tx_type"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
        <input type="hidden" name="TxidEmail" value="<?php echo esc_html( $mo2f_EV_txid ); ?>"/>

    </form>
    <form name="f" id="mo2f_show_softtoken_loginform" method="post" class="molms_display_none_forms">
        <input type="hidden" name="miniorange_softtoken"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-softtoken' ) ); ?>"/>
        <input type="hidden" name="option" value="miniorange_softtoken">
        <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
    </form>
    <form name="f" id="mo2f_show_forgotphone_loginform" method="post" class="molms_display_none_forms">
        <input type="hidden" name="request_origin_method" value="<?php echo esc_html( $login_status ); ?>"/>
        <input type="hidden" name="miniorange_forgotphone"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-forgotphone' ) ); ?>"/>
        <input type="hidden" name="option" value="miniorange_forgotphone">
        <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
    </form>
    <form name="f" id="mo2f_alternate_login_kbaform" method="post" class="molms_display_none_forms">
        <input type="hidden" name="miniorange_alternate_login_kba_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-alternate-login-kba-nonce' ) ); ?>"/>
        <input type="hidden" name="option" value="miniorange_alternate_login_kba">
        <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
    </form>
    <script>
        var timeout;
        var login_status = '<?php echo esc_html( $login_status ); ?>';
        var calls = 0;
        var onprem = '<?php echo esc_html( MOLMS_IS_ONPREM ); ?>';

        if (login_status != "MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS" && onprem == 1) {
            molms_pollPushValidation();

            function molms_pollPushValidation() {
                calls = calls + 1;
                var data = {'txid': '<?php echo esc_html( $mo2f_EV_txid ); ?>'};
                jQuery.ajax({
                    url: '<?php echo esc_url_raw( get_site_option( "siteurl" ) ); ?>' + "/wp-login.php",
                    type: "POST",
                    data: data,
                    success: function (result) {

                        var status = result;
                        if (status == 1) {
                            jQuery('input[name="tx_type"]').val("EV");
                            jQuery('#mo2f_mobile_validation_form').submit();
                        } else if (status == 'ERROR' || status == 'FAILED' || status == 'DENIED' || status == 0) {
                            jQuery('#mo2f_backto_mo_loginform').submit();
                        } else {
                            if (calls < 300) {
                                timeout = setTimeout(molms_pollPushValidation, 1000);
                            } else {
                                jQuery('#mo2f_backto_mo_loginform').submit();
                            }
                        }
                    }
                });
            }


        } else {
            molms_pollPushValidation();

            function molms_pollPushValidation() {
                var transId = "<?php echo esc_html( $cookievalue ); ?>";
                var jsonString = "{\"txId\":\"" + transId + "\"}";
                var postUrl = "<?php echo esc_url_raw( MOLMS_HOST_NAME ); ?>" + "/moas/api/auth/auth-status";

                jQuery.ajax({
                    url: postUrl,
                    type: "POST",
                    dataType: "json",
                    data: jsonString,
                    contentType: "application/json; charset=utf-8",
                    success: function (result) {
                        var status = JSON.parse(JSON.stringify(result)).status;
                        if (status == 'SUCCESS') {
                            jQuery('input[name="tx_type"]').val("PN");
                            jQuery('#mo2f_mobile_validation_form').submit();
                        } else if (status == 'ERROR' || status == 'FAILED' || status == 'DENIED') {
                            jQuery('#mo2f_backto_mo_loginform').submit();
                        } else {
                            timeout = setTimeout(molms_pollPushValidation, 3000);
                        }
                    }
                });
            }
        }

        function molms_loginoffline() {
            jQuery('#mo2f_show_softtoken_loginform').submit();
        }

        function molms_loginforgotphone() {
            jQuery('#mo2f_show_forgotphone_loginform').submit();
        }

        function molms_loginback() {
            jQuery('#mo2f_backto_mo_loginform').submit();
        }

        jQuery('a[href="#mo2f_alternate_login_kba"]').click(function () {
            jQuery('#mo2f_alternate_login_kbaform').submit();
        });

    </script>
    </body>
    </html>
	<?php
}

function molms_get_qrcode_authentication_prompt( $login_status, $login_message, $redirect_to, $qrCode, $session_id_encrypt, $cookievalue ) {
	$mo2f_enable_forgotphone = molms_Utility::get_mo2f_db_option( 'mo2f_enable_forgotphone', 'get_site_option' );
	$mo_lms_config           = new molms_Utility();
	$mo2f_is_new_customer    = molms_Utility::get_mo2f_db_option( 'mo2f_is_NC', 'get_site_option' ); ?>
    <html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		molms_echo_js_css_files(); ?>
    </head>
    <body>
    <div class="mo2f_modal" tabindex="-1" role="dialog">
        <div class="mo2f-modal-backdrop"></div>
        <div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
            <div class="login mo_customer_validation-modal-content">
                <div class="mo2f_modal-header">
                    <h4 class="mo2f_modal-title">
                        <button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
                                title="<?php echo esc_html( molms_lt( 'Back to login' ) ); ?>"
                                onclick="molms_loginback();"><span aria-hidden="true">&times;</span></button>
						<?php echo esc_html( molms_lt( 'Scan QR Code' ) ); ?></h4>
                </div>
                <div class="mo2f_modal-body center">
					<?php if ( isset( $login_message ) && ! empty( $login_message ) ) { ?>
                        <div id="otpMessage">
                            <p class="mo2fa_display_message_frontend"><?php echo esc_html( $login_message ); ?></p>
                        </div>
                        <br>
					<?php } ?>
                    <div id="scanQRSection">
                        <div style="margin-bottom:10%;">
                            <center>
                                <p class="mo2f_login_prompt_messages"><?php echo esc_html( molms_lt( 'Identify yourself by scanning the QR code with miniOrange Authenticator app.' ) ); ?></p>
                            </center>
                        </div>
                        <div id="showQrCode" style="margin-bottom:10%;">
                            <center><?php echo '<img src="data:image/jpg;base64,' . esc_html( $qrCode ) . '" />'; ?></center>
                        </div>
                        <span style="padding-right:2%;">
                           <center>
               <?php if ( ! $mo2f_is_new_customer ) { ?>
	               <?php if ( $mo2f_enable_forgotphone ) { ?>
                       <input type="button" name="miniorange_login_forgotphone" onclick="molms_loginforgotphone();"
                              id="miniorange_login_forgotphone" class="miniorange_login_forgotphone"
                              style="margin-right:5%;"
                              value="<?php echo esc_html( molms_lt( 'Forgot Phone?' ) ); ?>"/>
	               <?php } ?>
                   &emsp;&emsp;
               <?php } ?>
                               <input type="button" name="miniorange_login_offline" onclick="molms_loginoffline();"
                                      id="miniorange_login_offline" class="miniorange_login_offline"
                                      value="<?php echo esc_html( molms_lt( 'Phone is Offline?' ) ); ?>"/>
                        </center>
                     </span>
                        <div style="padding:10px;">
                            <p><a href="<?php echo esc_url_raw( $mo_lms_config->lockedOutlink() ); ?>" target="_blank"
                                  style="color:#ca2963;font-weight:bold;">I'm locked out & unable to login.</a></p>
                        </div>
                    </div>
					<?php molms_customize_logo() ?>
                </div>
            </div>
        </div>
    </div>
    <form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
          class="molms_display_none_forms">
        <input type="hidden" name="miniorange_mobile_validation_failed_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
    </form>
    <form name="f" id="mo2f_mobile_validation_form" method="post" class="molms_display_none_forms">
        <input type="hidden" name="miniorange_mobile_validation_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-nonce' ) ); ?>"/>
        <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
        <input type="hidden" name="option" value="miniorange_mobile_validation">
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
    </form>
    <form name="f" id="mo2f_show_softtoken_loginform" method="post" class="molms_display_none_forms">
        <input type="hidden" name="miniorange_softtoken"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-softtoken' ) ); ?>"/>
        <input type="hidden" name="option" value="miniorange_softtoken">
        <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
    </form>
    <form name="f" id="mo2f_show_forgotphone_loginform" method="post" class="molms_display_none_forms">
        <input type="hidden" name="request_origin_method" value="<?php echo esc_html( $login_status ); ?>"/>
        <input type="hidden" name="miniorange_forgotphone"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-forgotphone' ) ); ?>"/>
        <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
        <input type="hidden" name="option" value="miniorange_forgotphone">
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
    </form>
    <script>
        var timeout;
        molms_pollMobileValidation();

        function molms_pollMobileValidation() {
            var transId = "<?php echo esc_html( $cookievalue ); ?>";
            var jsonString = "{\"txId\":\"" + transId + "\"}";
            var postUrl = "<?php echo esc_url_raw( MOLMS_HOST_NAME ); ?>" + "/moas/api/auth/auth-status";
            jQuery.ajax({
                url: postUrl,
                type: "POST",
                dataType: "json",
                data: jsonString,
                contentType: "application/json; charset=utf-8",
                success: function (result) {
                    var status = JSON.parse(JSON.stringify(result)).status;
                    if (status == 'SUCCESS') {
                        var content = "<div id='success'><center><img src='" + "<?php echo esc_url_raw( plugins_url( 'includes/images/right.png', dirname( dirname( __FILE__ ) ) ) ); ?>" + "' /></center></div>";
                        jQuery("#showQrCode").empty();
                        jQuery("#showQrCode").append(content);
                        setTimeout(function () {
                            jQuery("#mo2f_mobile_validation_form").submit();
                        }, 100);
                    } else if (status == 'ERROR' || status == 'FAILED') {
                        var content = "<div id='error'><center><img src='" + "<?php echo esc_url_raw( plugins_url( 'includes/images/wrong.png', dirname( dirname( __FILE__ ) ) ) ); ?>" + "' /></center></div>";
                        jQuery("#showQrCode").empty();
                        jQuery("#showQrCode").append(content);
                        setTimeout(function () {
                            jQuery('#mo2f_backto_mo_loginform').submit();
                        }, 1000);
                    } else {
                        timeout = setTimeout(molms_pollMobileValidation, 3000);
                    }
                }
            });
        }

        function molms_loginoffline() {
            jQuery('#mo2f_show_softtoken_loginform').submit();
        }

        function molms_loginforgotphone() {
            jQuery('#mo2f_show_forgotphone_loginform').submit();
        }

        function molms_loginback() {
            jQuery('#mo2f_backto_mo_loginform').submit();
        }

    </script>
    </body>
    </html>
	<?php
}

function molms_get_otp_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id, $show_back_button = null ) {
	$mo2f_enable_forgotphone = molms_Utility::get_mo2f_db_option( 'mo2f_enable_forgotphone', 'get_site_option' );
	$mo_lms_config           = new molms_Utility();
	$mo2f_is_new_customer    = molms_Utility::get_mo2f_db_option( 'mo2f_is_NC', 'get_site_option' );
	$attempts                = get_site_option( 'mo2f_attempts_before_redirect', 3 ); ?>
    <html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		molms_echo_js_css_files(); ?>
    </head>
    <body>
    <div class="mo2f_modal" tabindex="-1" role="dialog">
        <div class="mo2f-modal-backdrop"></div>
        <div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
            <div class="login mo_customer_validation-modal-content">
                <div class="mo2f_modal-header">
                    <h4 class="mo2f_modal-title">
                        <button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
                                title="<?php echo esc_html( molms_lt( 'Back to login' ) ); ?>"
                                onclick="molms_loginback();"><span aria-hidden="true">&times;</span></button>
						<?php echo esc_html( molms_lt( 'Validate OTP' ) ); ?>
                    </h4>
                </div>
                <div class="mo2f_modal-body center">
					<?php if ( isset( $login_message ) && ! empty( $login_message ) ) { ?>
                        <div id="otpMessage">
                            <p class="mo2fa_display_message_frontend"><?php echo esc_html( $login_message ); ?></p>
                        </div>
					<?php } ?><br><?php
					?>
                    <span><b>Attempts left</b>:</span> <?php echo esc_html( $attempts ); ?><br>
					<?php if ( $attempts == 1 ) { ?>
                        <span style='color:red;'><b>If you fail to verify your identity, you will be redirected back to login page to verify your credentials.</b></span>
                        <br>
					<?php } ?>
                    <br>
                    <div id="showOTP">
                        <div class="mo2f-login-container">
                            <form name="f" id="mo2f_submitotp_loginform" method="post">
                                <center>
                                    <input type="text" name="mo2fa_softtoken" style="height:28px !important;"
                                           placeholder="<?php echo esc_html( molms_lt( 'Enter code' ) ); ?>"
                                           id="mo2fa_softtoken" required="true" class="mo_otp_token" autofocus="true"
                                           pattern="[0-9]{4,8}"
                                           title="<?php echo esc_html( molms_lt( 'Only digits within range 4-8 are allowed.' ) ); ?>"/>
                                </center>
                                <br>
                                <input type="submit" name="miniorange_otp_token_submit" id="miniorange_otp_token_submit"
                                       class="miniorange_otp_token_submit"
                                       value="<?php echo esc_html( molms_lt( 'Validate' ) ); ?>"/>
								<?php

								if ( $show_back_button == 1 ) {
									?>
                                    <input type="button" name="miniorange_otp_token_back" id="miniorange_otp_token_back"
                                           class="miniorange_otp_token_submit"
                                           value="<?php echo esc_html( molms_lt( 'Back' ) ); ?>"/>
									<?php
								} ?>
                                <input type="hidden" name="request_origin_method"
                                       value="<?php echo esc_html( $login_status ); ?>"/>
                                <input type="hidden" name="miniorange_soft_token_nonce"
                                       value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-soft-token-nonce' ) ); ?>"/>
                                <input type="hidden" name="option" value="miniorange_soft_token">
                                <input type="hidden" name="redirect_to"
                                       value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
                                <input type="hidden" name="session_id"
                                       value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
                            </form>
                            <div style="padding:10px;">
                                <p><a href="<?php echo esc_url_raw( $mo_lms_config->lockedOutlink() ); ?>"
                                      target="_blank" style="color:#ca2963;font-weight:bold;">I'm locked out & unable to
                                        login.</a></p>
                            </div>
                        </div>
                    </div>
                    </center>
					<?php molms_customize_logo() ?>
                </div>
            </div>
        </div>
    </div>

    <form name="f" id="mo2f_backto_inline_registration" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
          class="molms_display_none_forms">
        <input type="hidden" name="miniorange_back_inline_reg_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-back-inline-reg-nonce' ) ); ?>"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
        <input type="hidden" name="option" value="miniorange2f_back_to_inline_registration">
        <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>

    </form>

    <form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
          class="molms_display_none_forms">
        <input type="hidden" name="miniorange_mobile_validation_failed_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
    </form>
	<?php if ( molms_Utility::get_mo2f_db_option( 'mo2f_enable_forgotphone', 'get_site_option' ) && isset( $login_status ) && $login_status != 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' ) { ?>
        <form name="f" id="mo2f_show_forgotphone_loginform" method="post" action="" class="molms_display_none_forms">
            <input type="hidden" name="request_origin_method" value="<?php echo esc_html( $login_status ); ?>"/>
            <input type="hidden" name="miniorange_forgotphone"
                   value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-forgotphone' ) ); ?>"/>
            <input type="hidden" name="option" value="miniorange_forgotphone">
            <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
            <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
        </form>

	<?php } ?>

    <script>
        jQuery('#miniorange_otp_token_back').click(function () {
            jQuery('#mo2f_backto_inline_registration').submit();
        });

        function molms_loginback() {
            jQuery('#mo2f_backto_mo_loginform').submit();
        }

        function molms_loginforgotphone() {
            jQuery('#mo2f_show_forgotphone_loginform').submit();
        }

        var is_ajax = '<?php echo esc_html( molms_2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ); ?>';
        if (is_ajax) {
            jQuery('#mo2fa_softtoken').keypress(function (e) {
                if (e.which == 13) {//Enter key pressed
                    e.preventDefault();
                    mo2f_otp_ajax();
                }
            });
            jQuery("#miniorange_otp_token_submit").click(function (e) {
                e.preventDefault();
                mo2f_otp_ajax();
            });

            function molms_otp_ajax() {
                jQuery('#mo2fa_softtoken').prop('disabled', 'true');
                jQuery('#miniorange_otp_token_submit').prop('disabled', 'true');
                var data = {
                    "action": "mo2f_ajax",
                    "mo2f_ajax_option": "mo2f_ajax_otp",
                    "mo2fa_softtoken": jQuery("input[name=\'mo2fa_softtoken\']").val(),
                    "miniorange_soft_token_nonce": jQuery("input[name=\'miniorange_soft_token_nonce\']").val(),
                    "session_id": jQuery("input[name=\'session_id\']").val(),
                    "redirect_to": jQuery("input[name=\'redirect_to\']").val(),
                    "request_origin_method": jQuery("input[name=\'request_origin_method\']").val(),
                };
                jQuery.post(my_ajax_object.ajax_url, data, function (response) {
                    if (typeof response.data === "undefined")
                        jQuery("html").html(response);
                    else if (response.data.reload)
                        location.reload(true);
                    else
                        location.href = response.data.redirect;
                });
            }
        }
    </script>
    </body>
    </html>
	<?php
}


function molms_get_device_form( $redirect_to, $session_id_encrypt ) {
	?>
    <html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		molms_echo_js_css_files(); ?>
    </head>
    <body>
    <div class="mo2f_modal" tabindex="-1" role="dialog">
        <div class="mo2f-modal-backdrop"></div>
        <div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
            <div class="login mo_customer_validation-modal-content">
                <div class="mo2f_modal-header">
                    <h4 class="mo2f_modal-title">
                        <button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
                                title="<?php echo esc_html( molms_lt( 'Back to login' ) ); ?>"
                                onclick="molms_loginback();"><span aria-hidden="true">&times;</span></button>

						<?php echo esc_html( molms_lt( 'Remember Device' ) ); ?>
                    </h4>
                </div>
                <div class="mo2f_modal-body center">
                    <div id="mo2f_device_content">
                        <p class="mo2f_login_prompt_messages"><?php echo esc_html( molms_lt( 'Do you want to remember this device?' ) ); ?></p>
                        <input type="button" name="miniorange_trust_device_yes" onclick="mo_check_device_confirm();"
                               id="miniorange_trust_device_yes" class="mo_green" style="margin-right:5%;"
                               value="<?php echo esc_html( molms_lt( 'Yes' ) ); ?>"/>
                        <input type="button" name="miniorange_trust_device_no" onclick="mo_check_device_cancel();"
                               id="miniorange_trust_device_no" class="mo_red"
                               value="<?php echo esc_html( molms_lt( 'No' ) ); ?>"/>
                    </div>
                    <div id="showLoadingBar" hidden>
                        <p class="mo2f_login_prompt_messages"><?php echo esc_html( molms_lt( 'Please wait...We are taking you into your account.' ) ); ?></p>
                        <img src="<?php echo esc_url_raw( plugins_url( 'includes/images/ajax-loader-login.gif', dirname( dirname( __FILE__ ) ) ) ); ?>"/>
                    </div>
                    <br><br>
                    <span>
                  <?php echo esc_html( molms_lt( 'Click on ' ) ); ?>
                        <i><b><?php echo esc_html( molms_lt( 'Yes' ) ); ?></b></i><?php echo esc_html( molms_lt( 'if this is your personal device.' ) ); ?>
                        <br>
                        <?php echo esc_html( molms_lt( 'Click on ' ) ); ?>
                        <i><b><?php echo esc_html( molms_lt( 'No ' ) ); ?></b></i> <?php echo esc_html( molms_lt( 'if this is a public device.' ) ); ?>
                  </span><br><br>
					<?php molms_customize_logo() ?>
                </div>
            </div>
        </div>
    </div>
    <form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
          class="molms_display_none_forms">
        <input type="hidden" name="miniorange_mobile_validation_failed_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
    </form>
    <form name="f" id="mo2f_trust_device_confirm_form" method="post" action="" class="molms_display_none_forms">
        <input type="hidden" name="mo2f_trust_device_confirm_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-trust-device-confirm-nonce' ) ); ?>"/>
        <input type="hidden" name="option" value="miniorange_rba_validate">
        <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
    </form>
    <form name="f" id="mo2f_trust_device_cancel_form" method="post" action="" class="molms_display_none_forms">
        <input type="hidden" name="mo2f_trust_device_cancel_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-trust-device-cancel-nonce' ) ); ?>"/>
        <input type="hidden" name="option" value="miniorange_rba_cancle">
        <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>"/>
        <input type="hidden" name="session_id" value="<?php echo esc_html( $session_id_encrypt ); ?>"/>
    </form>
    <script>
        function molms_loginback() {
            jQuery('#mo2f_backto_mo_loginform').submit();
        }

        function molms_check_device_confirm() {
            jQuery('#mo2f_device_content').css("display", "none");
            jQuery('#showLoadingBar').css("display", "block");
            jQuery('#mo2f_trust_device_confirm_form').submit();
        }

        function molms_check_device_cancel() {
            jQuery('#mo2f_device_content').css("display", "none");
            jQuery('#showLoadingBar').css("display", "block");
            jQuery('#mo2f_trust_device_cancel_form').submit();
        }
    </script>
    </body>
    </html>
	<?php
}

function molms_customize_logo() {
	?>
    <div style="float:right;"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><img
                    alt="logo"
                    src="<?php echo esc_url_raw( plugins_url( 'includes/images/miniOrange2.png', dirname( dirname( __FILE__ ) ) ) ); ?>"/></a>
    </div>

	<?php
}

function molms_echo_js_css_files() {
	wp_register_script( 'molms_bootstrap', plugins_url( 'includes/js/bootstrap.min.js', dirname( dirname( __FILE__ ) ) ) );
	wp_register_style( 'molms_twofa_style', plugins_url( 'includes/css/twofa_style_settings.css?version=5.1.21', dirname( dirname( __FILE__ ) ) ) );
	wp_print_scripts( 'jquery-core' );
	wp_print_scripts( 'molms_bootstrap' );
	wp_print_styles( 'molms_twofa_style' );
}

function mo2f_device_exceeded_error() {
	?>
    <html>
    <head>
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		molms_echo_js_css_files(); ?>
    </head>
    <body>
    <div class="mo2f_modal" tabindex="-1" role="dialog" id="myModal5">
        <div class="mo2f-modal-backdrop"></div>
        <div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
            <div class="login mo_customer_validation-modal-content">
                <div class="mo2f_modal-header">
                    <h2 class="mo2f_modal-title">
                        <button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
                                title="<?php echo esc_html__( 'Cancel', '2fa-learndash-lms' ); ?>"
                                onclick="mologinback();"><span aria-hidden="true">&times;</span></button>
                        <center>
							<?php echo esc_html__( 'Access Denied!', '2fa-learndash-lms' ); ?>
                        </center>
                    </h2>
                </div>
                <div class="mo2f_modal-body center">
                    <center>
                        <h4 style="margin-bottom:0px !important;"> <?php echo esc_html__( ' You are already logged in from ' . get_site_option( 'mo2f_simultaneous_session_allowed' ) . ' location(s) and not allowed to login from more. Please logout from there or wait for the session to expire', '2fa-learndash-lms' ); ?></h4>
                        <br>
                    </center>
					<?php molms_customize_logo() ?>
                </div>
            </div>
        </div>
        <form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
              style="display:none;">
            <input type="hidden" name="miniorange_mobile_validation_failed_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
        </form>
        <script>
            function mologinback() {
                jQuery('#mo2f_backto_mo_loginform').submit();
            }
        </script>
    </body>
    </html>
	<?php
}

?>
