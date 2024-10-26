<?php
global $molms_dirName;
$setup_dirName = $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'link-tracer.php';
require $setup_dirName;
function molms_2_factor_user_roles( $current_user ) {
	global $molms_dirName;
	include $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'link-tracer.php';
	global $wp_roles;
	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}
	$upgrade_url = add_query_arg( array( 'page' => 'molms_upgrade' ), esc_url_raw( $_SERVER['REQUEST_URI'] ) ); ?>

    <div><span style="font-size:16px;">Roles<div style="float:right;">Custom Redirect Login Url<a
                        href="<?php echo esc_url( $upgrade_url ); ?>" style="color: red">[ PREMIUM ]</a>&nbsp;&nbsp;&nbsp;
   </span></a>
        <a href='<?php echo esc_url_raw( $two_factor_premium_doc['Custom url'] ); ?>' target="_blank">
            <span class="dashicons dashicons-text-page" style="font-size:19px;color:#269eb3;float: right;"></span></a>

    </div></span><br/><br/>
	<?php
	foreach ( $wp_roles->role_names as $id => $name ) {
		$setting = get_site_option( 'mo2fa_' . esc_html( $id ) ); ?>
        <div>
            <input type="checkbox" name="role" value="<?php echo 'mo2fa_' . esc_html( $id ); ?>"
				<?php
				if ( $id == 'administrator' ) {
					if ( get_site_option( 'mo2fa_administrator' ) ) {
						echo 'checked';
					} else {
						echo 'unchecked';
					}
				} else {
					echo 'disabled';
				} ?>/>
			<?php echo esc_html( $id ); ?>
            <input type="text" class="mo2f_table_textbox" style="width:50% !important;float:right;"
                   id="<?php echo 'mo2fa_' . esc_html( $id ); ?>_login_url"
                   value="<?php echo esc_url_raw( get_site_option( 'mo2fa_' . esc_html( $id ) . '_login_url' ) ); ?>"
				<?php
				echo 'disabled'; ?>
            />
			<?php
			if ( $name == 'Administrator' ) {
				?>
                <h3><br>
                    <hr>
                    For User Roles
                </h3>
				<?php
			} ?>
        </div>
        <br/>
		<?php
	}
	print '</div>';
}

$user                  = wp_get_current_user();
$configured_2FA_method = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $user->ID );
$configured_meth       = array();
$configured_meth       = array(
	'Email Verification',
	'Google Authenticator',
	'Security Questions',
	'Authy Authenticator',
	'OTP Over Email',
	'OTP Over SMS'
);
$method_exisits        = in_array( $configured_2FA_method, $configured_meth );
?>
<?php
if ( current_user_can( 'administrator' ) ) {
	?>
    <div class="mo_lms_setting_layout" id="disable_two_factor_tour">
        <h2>Enable/disable 2-factor Authentication</h2>
        <hr>
        <div style="padding-top: 1%;">
            <form name="f" method="post" action="">
                <input type="hidden" id="mo2f_nonce_enable_2FA" name="mo2f_nonce_enable_2FA"
                       value="<?php echo esc_attr( wp_create_nonce( "mo2f-nonce-enable-2FA" ) ) ?>"/>
                <h3>
					<?php
					echo esc_html( molms_lt( 'Enable Two-Factor plugin:' ) ); ?>
                </h3>
                <p><i>If you disable this checkbox, Two-Factor will not be invoked for any user during login.</i>
                    <label class="mo_lms_switch" style="float: right;">
                        <input type="checkbox" onChange="mo_toggle_twofa()" style="padding-top: 50px;"
                               id="mo2f_enable_2faa"
                               name="mo2f_enable_2fa"
                               value="<?php molms_Utility::get_mo2f_db_option( 'molms_activate_plugin', 'get_site_option' ) ?>"<?php checked( molms_Utility::get_mo2f_db_option( 'molms_activate_plugin', 'get_site_option' ) == 1 ); ?>/>
                        <span class="mo_lms_slider mo_lms_round"></span>
                    </label>
                </p>
            </form>
        </div>
    </div>
    <div class="mo_lms_setting_layout" id="mo2f_inline_registration_tour">
        <h2>Enable/disable User Enrollment / Provisioning for 2FA</h2>
        <hr>
        <div style="padding-top: 1%;">
            <form name="f" method="post" action="">
                <input type="hidden" id="mo2f_nonce_enable_inline" name="mo2f_nonce_enable_inline"
                       value="<?php echo esc_attr( wp_create_nonce( "mo2f-nonce-enable-inline" ) ) ?>"/>
                <h3>
					<?php
					echo esc_html( molms_lt( 'Enable User Enrollment / Provisioning:' ) ); ?>
                </h3>
                <p><i> If you disable this checkbox, user enrollment for 2FA will not be invoked for any user during
                        login.</i>
                    <label class="mo_lms_switch" style="float: right;">
                        <input type="checkbox" onChange="mo_toggle_inline()" style="padding-top: 50px;float: right;"
                               id="mo2f_inline_registration"
                               name="mo2f_inline_registration"
                               value="<?php molms_Utility::get_mo2f_db_option( 'mo2f_inline_registration', 'site_option' ) ?>" <?php checked( molms_Utility::get_mo2f_db_option( 'mo2f_inline_registration', 'site_option' ) == 1 ); ?>/>
                        <span class="mo_lms_slider mo_lms_round"></span>
                    </label>
                </p>
            </form>
        </div>
    </div>
    <script type="text/javascript">
        function mo_toggle_twofa() {
            var data = {
                'action': 'molms_two_factor_ajax',
                'mo_2f_two_factor_ajax': 'mo2f_enable_disable_twofactor',
                'mo2f_nonce_enable_2FA': jQuery('#mo2f_nonce_enable_2FA').val(),
                'mo2f_enable_2fa': jQuery('#mo2f_enable_2faa').is(":checked"),
            };
            jQuery.post(ajaxurl, data, function (response) {
                var response = response.replace(/\s+/g, ' ').trim();
                if (response == "true") {
                    jQuery('#mo_scan_message').empty();
                    jQuery('#mo_scan_message').append("<div id='notice_div' class='overlay_success'><div class='popup_text'>&nbsp&nbsp Two factor is now enabled.</div></div>");
                    window.onload = nav_popup();
                } else {
                    jQuery('#mo_scan_message').empty();
                    jQuery('#mo_scan_message').append("<div id='notice_div' class='overlay_success'><div class='popup_text'>&nbsp&nbsp Two factor is now disabled.</div></div>");
                    window.onload = nav_popup();
                }
            });

        }

        function mo_toggle_inline() {
            var data = {
                'action': 'molms_two_factor_ajax',
                'mo_2f_two_factor_ajax': 'mo2f_enable_disable_inline',
                'mo2f_nonce_enable_inline': jQuery('#mo2f_nonce_enable_inline').val(),
                'mo2f_inline_registration': jQuery('#mo2f_inline_registration').is(":checked"),
            };
            jQuery.post(ajaxurl, data, function (response) {
                var response = response.replace(/\s+/g, ' ').trim();
                if (response == "true") {
                    success_msg('User enrollment is now enabled.');
                } else if (response == "error") {
                    error_msg('Unknown error occured. Please try again!');
                } else {
                    success_msg('User Enrollment is now disabled.');
                }
            });

        }
    </script>
	<?php
}

if ( MOLMS_IS_ONPREM && current_user_can( 'administrator' ) ) {
	?>
    <div class="mo_lms_setting_layout" id="2fa_method">
        <input type="hidden" name="option" value=""/>
        <span>
                        <h2>Select Roles to enable 2-Factor for Users <b style="font-size: 70%;color: red;"></b>
                        <a href='<?php echo esc_url_raw( $two_factor_premium_doc['Enable 2FA Role Based'] ); ?>'
                           target="_blank">
                        <span class="dashicons dashicons-text-page"
                              style="font-size:19px;color:#269eb3;float: right;"></span>
                        
                        </a></h2>
                    <span>
                            <hr><br>

                        <?php
                        echo molms_2_factor_user_roles( $current_user ); ?>
                        <br>
                        </span>
                        <input type="submit" id="save_role_2FA" name="submit" value="Save Settings"
                               class="mo_lms_button mo_lms_button1"/>
                    </span>
        <br><br>
        <div id="mo2f_note">
            <b>Note:</b> Selecting the above roles will enable 2-Factor for all users associated with that role.
        </div>
    </div>


    <script>


        jQuery("#save_role_2FA").click(function () {
            var enabledrole = [];
            $.each($("input[name='role']:checked"), function () {
                enabledrole.push($(this).val());
            });
            var mo2fa_administrator_login_url = $('#mo2fa_administrator_login_url').val();
            var nonce = '<?php echo esc_attr( wp_create_nonce( "unlimittedUserNonce" ) ); ?>';
            var data = {
                'action': 'molms_two_factor_ajax',
                'mo_2f_two_factor_ajax': 'mo2f_role_based_2_factor',
                'nonce': nonce,
                'enabledrole': enabledrole,
                'mo2fa_administrator_login_url': mo2fa_administrator_login_url
            };
            jQuery.post(ajaxurl, data, function (response) {
                var response = response.replace(/\s+/g, ' ').trim();
                if (response == "true") {
                    jQuery('#mo_scan_message').empty();
                    jQuery('#mo_scan_message').append("<div id='notice_div' class='overlay_success'><div class='popup_text'>&nbsp&nbsp Settings are saved.</div></div>");
                    window.onload = nav_popup();
                } else {
                    jQuery('#mo2f_confirmcloud').css('display', 'none');
                    jQuery("#singleUser").prop("checked", false);
                    jQuery('#single_user').css('display', 'none');

                    jQuery('#mo_scan_message').empty();
                    jQuery('#mo_scan_message').append("<div id='notice_div' class='overlay_error'><div class='popup_text'>&nbsp&nbsp <b>You are not authorized to perform this action</b>. Only <b>" + response + "</b> is allowed. For more details contact miniOrange.</div></div>");
                    window.onload = nav_popup();
                }
            });
        });
    </script>

	<?php
}
?>
    <div class="mo_lms_setting_layout">
        <h2>What happens if my phone is lost, discharged or not with me<a
                    href='<?php echo esc_url_raw( $two_factor_premium_doc['What happens if my phone is lost, discharged or not with me'] ); ?>'
                    target="_blank">
                <span class="dashicons dashicons-text-page" style="font-size:19px;color:#269eb3;float: right;"></span>

            </a></h2>
        <hr>
        <h3>Enable Forgot Phone:</h3>
        <p>
        <p><i>Select the alternate login method in case your phone is lost, discharged or not with you.</i>
            <label class="mo_lms_switch" style="float: right;">
                <input type="checkbox" class="option_for_auth" name="mo2f_all_users_method" value="1" checked="checked"
                       style="float: right;" disabled>
                <span class="mo_lms_slider mo_lms_round"></span>
            </label>
        </p>
        <input type="checkbox" class="option_for_auth" name="mo2f_all_users_method" value="1" checked="checked"
               disabled>KBA&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="checkbox" class="option_for_auth" name="mo2f_all_users_method" value="1" checked="checked"
               disabled>OTP over EMAIL
        </p>

    </div>
<?php
if ( ! MOLMS_IS_ONPREM && current_user_can( 'administrator' ) ) {
	?>
    <div id="lms_message">
    </div>

    <script type="text/javascript">

        function reconfigKBA() {
            var data = {
                'action': 'molms_two_factor_ajax',
                'mo_2f_two_factor_ajax': 'mo2f_shift_to_onprem',
            };
            jQuery.post(ajaxurl, data, function (response) {

                if (response == 'true') {
                    jQuery('#mo2f_go_back_form').submit();
                    jQuery('#mo2f_configured_2FA_method_free_plan').val('SecurityQuestions');
                    jQuery('#mo2f_selected_action_free_plan').val('configure2factor');
                    jQuery('#mo2f_save_free_plan_auth_methods_form').submit();
                    openTab2fa(setup_2fa);
                } else {
                    jQuery('#afterMigrate').css('display', 'none');
                    jQuery("#unlimittedUser").prop("checked", false);
                    jQuery('#ConfirmOnPrem').css('display', 'none');
                    jQuery('#onpremisediv').css('display', 'inline');

                    jQuery('#mo_scan_message').empty();
                    jQuery('#mo_scan_message').append("<div id='notice_div' class='overlay_error'><div class='popup_text'>&nbsp&nbsp <b>You are not authorized to perform this action</b>. Only <b>" + response + "</b> is allowed. For more details contact miniOrange.</div></div>");
                    window.onload = nav_popup();
                }
            });
        }

        function reconfigGA() {

            var data = {
                'action': 'molms_two_factor_ajax',
                'mo_2f_two_factor_ajax': 'mo2f_shift_to_onprem',
            };
            jQuery.post(ajaxurl, data, function (response) {

                if (response == 'true') {
                    jQuery('#mo2f_go_back_form').submit();
                    jQuery('#mo2f_configured_2FA_method_free_plan').val('GoogleAuthenticator');
                    jQuery('#mo2f_selected_action_free_plan').val('configure2factor');
                    jQuery('#mo2f_save_free_plan_auth_methods_form').submit();
                    openTab2fa(setup_2fa);
                } else {
                    jQuery('#afterMigrate').css('display', 'none');
                    jQuery("#unlimittedUser").prop("checked", false);
                    jQuery('#ConfirmOnPrem').css('display', 'none');
                    jQuery('#onpremisediv').css('display', 'inline');

                    jQuery('#mo_scan_message').empty();
                    jQuery('#mo_scan_message').append("<div id='notice_div' class='overlay_error'><div class='popup_text'>&nbsp&nbsp <b>You are not authorized to perform this action</b>. Only <b>" + response + "</b> is allowed. For more details contact miniOrange.</div></div>");
                    window.onload = nav_popup();
                }
            });
        }

        function emailVerification() {
            jQuery('#reconfigTable').hide();
            jQuery('#Emailreconfig').show();
            jQuery('#reconfig').hide();
            jQuery('#msg').hide();
        }
    </script>

    <script type="text/javascript">

        jQuery('#closeConfirmOnPrem').click(function () {
            document.getElementById('unlimittedUser').checked = false;
            close_modal();
        });
        jQuery('#ConfirmOnPremButton').click(function () {
            jQuery('#ConfirmOnPrem').hide();
            var enableOnPremise = jQuery("input[name='unlimittedUser']:checked").val();
            var nonce = '<?php echo esc_attr( wp_create_nonce( "unlimittedUserNonce" ) ); ?>';
            var data = {
                'action': 'molms_two_factor_ajax',
                'mo_2f_two_factor_ajax': 'mo2f_unlimitted_user',
                'nonce': nonce,
                'enableOnPremise': enableOnPremise
            };
            jQuery.post(ajaxurl, data, function (response) {
                var response = response.replace(/\s+/g, ' ').trim();
                if (response == 'OnPremiseActive') {
                    jQuery('#lms_message').empty();
                    jQuery('#lms_message').append("<div class= 'notice notice-success is-dismissible' style='height : 25px;padding-top: 10px;  '> Congratulations! Now you can use 2-factor Authentication for your administrators for  free.  ");

                    jQuery('#onpremisediv').hide();
                    jQuery('#afterMigrate').show();
                } else if (response == 'OnPremiseDeactive') {
                    jQuery('#lms_message').empty();
                    jQuery('#lms_message').append("<div class= 'notice notice-success is-dismissible' style='height : 25px;padding-top: 10px;  '> Cloud Solution deactivated");
                    close_modal();
                } else {
                    jQuery('#lms_message').empty();
                    jQuery('#lms_message').append("<div class= 'notice notice-error is-dismissible' style='height : 25px;padding-top: 10px;  '> An Unknown Error has occured. ");
                    close_modal();
                }
            });

        });

        jQuery('#emailBack').click(function () {
            jQuery('#reconfigTable').show();
            jQuery('#Emailreconfig').hide();
            jQuery('#msg').show();
            jQuery('#reconfig').show();
        });
        jQuery('#save_email').click(function () {
            var email = jQuery('#emalEntered').val();
            var nonce = '<?php echo esc_attr( wp_create_nonce( 'EmailVerificationSaveNonce' ) ); ?>';
            var user_id = '<?php echo esc_html( get_current_user_id() ); ?>';

            if (email != '') {
                var data = {
                    'action': 'molms_two_factor_ajax',
                    'mo_2f_two_factor_ajax': 'mo2f_save_email_verification',
                    'nonce': nonce,
                    'email': email,
                    'user_id': user_id
                };
                jQuery.post(ajaxurl, data, function (response) {

                    var response = response.replace(/\s+/g, ' ').trim();
                    if (response == "settingsSaved") {
                        jQuery('#mo2f_configured_2FA_method_free_plan').val('EmailVerification');
                        jQuery('#mo2f_selected_action_free_plan').val('select2factor');
                        jQuery('#mo2f_save_free_plan_auth_methods_form').submit();
                    } else if (response == "NonceDidNotMatch") {
                        jQuery('#mo_scan_message').empty();
                        jQuery('#mo_scan_message').append("<div id='notice_div' class='overlay_success'><div class='popup_text'> There were some issues. Please try again.</div></div>");
                        window.onload = nav_popup();

                        close_modal();

                    } else {
                        jQuery('#mo_scan_message').empty();
                        jQuery('#mo_scan_message').append("<div id='notice_div' class='overlay_success'><div class='popup_text'>Please enter a valid Email.</div></div>");
                        window.onload = nav_popup();


                    }
                });
            }
        });
        jQuery('#closeConfirmOnPrem').click(function () {
            close_modal();
            window.location.reload();
        });

        jQuery('#unlimittedUser').click(function () {
            jQuery('#ConfirmOnPrem').css('display', 'block');
            jQuery('.modal-content').css('width', '35%');

        });


    </script>
    <script type="text/javascript">

    </script>

	<?php
}
?>