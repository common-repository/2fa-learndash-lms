<?php
global $molms_dirName;
$setup_dirName = $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'link-tracer.php';
require $setup_dirName;
global $current_user;
$current_user = wp_get_current_user();
global $molms_db_queries;
?>
    <div class="mo_lms_setting_layout" id="mo2f_customization_tour">
        <form name="f" id="custom_css_form_add" method="post" action="">
            <input type="hidden" name="option" value="mo_auth_custom_options_save"/>

            <div id="mo2f_custom_addon_hide">
                <h1><?php echo esc_html__( '2. Personalization' ); ?>
                    <span style="text-align: right;font-size: large;"></span>
                </h1>
                <p id="custom_description">
					<?php echo esc_html__( 'This helps you to modify and redesign the 2FA prompt to match according to your website and various customizations in the plugin dashboard.', '2fa-learndash-lms' ); ?>

                </p>
                <hr>
            </div>

            <h3><?php echo esc_html( molms_lt( 'Customize Plugin Icon' ) ); ?><a
                        href='<?php echo esc_url_raw( $two_factor_premium_doc['Custom plugin logo'] ); ?>'
                        target="_blank">
                    <span class="dashicons dashicons-text-page"
                          style="font-size:19px;color:#269eb3;float: right;"></span>

                </a></h3><br>
            <div style="margin-left:2%">
                <input type="checkbox" id="mo2f_enable_custom_icon" name="mo2f_enable_custom_icon"
                       value="1" <?php checked( esc_html( get_site_option( 'mo2f_enable_custom_icon' ) ) == 1 );
				echo 'disabled'; ?> /> <?php echo esc_html( molms_lt( 'Change Plugin Icon.' ) ); ?>
                <div class="mo2f_advanced_options_note"><p style="padding:5px;"><i><?php echo esc_url_raw( molms_lt(
								'
						Go to /wp-content/uploads/miniorange folder and upload a .png image with the name "plugin_icon" (Max Size: 20x34px).'
							) ); ?></i></p>
                </div>
            </div>
            <hr>

            <h3><?php echo esc_html( molms_lt( 'Customize Plugin Name' ) ); ?><a
                        href='<?php echo esc_url_raw( $two_factor_premium_doc['Custom plugin name'] ); ?>'
                        target="_blank">
                    <span class="dashicons dashicons-text-page"
                          style="font-size:19px;color:#269eb3;float: right;"></span>

                </a></h3><br>
            <div style="margin-left:2%">
				<?php echo esc_html( molms_lt( 'Change Plugin Name:' ) ); ?> &nbsp;
                <input type="text" class="mo2f_table_textbox" style="width:35%     " id="mo2f_custom_plugin_name"
                       name="mo2f_custom_plugin_name" <?php echo 'disabled'; ?>
                       value="<?php echo esc_html( molms_Utility::get_mo2f_db_option( 'mo2f_custom_plugin_name', 'get_site_option' ) ) ?>"
                       placeholder="<?php echo esc_html( molms_lt( 'Enter a custom Plugin Name.' ) ); ?>"/>

                <div class="mo2f_advanced_options_note"><p style="padding:5px;"><i>
							<?php echo esc_html( molms_lt( 'This will be the Plugin Name You and your Users see in  WordPress Dashboard.' ) ); ?>
                        </i></p></div>
            </div>
            <hr>

        </form>
		<?php show_2_factor_custom_design_options( $current_user ); ?>
        <br>
        <div>
            <h3><?php echo esc_html( molms_lt( 'Custom Email and SMS Templates' ) ); ?>
                <a href="https://developers.miniorange.com/docs/security/wordpress/wp-security/customize-email-template"
                   target="_blank"><span class="dashicons dashicons-text-page"
                                         style="font-size:19px;color:#269eb3;float: right;"></span> </a>
            </h3>
            <hr>

            <div style="margin-left:2%">
                <p><?php echo esc_html( molms_lt( 'You can change the templates for Email and SMS as per your requirement.' ) ); ?></p>
				<?php if ( molms_is_customer_registered() ) {
					if ( get_site_option( 'mo2f_miniorange_admin' ) == $current_user->ID ) { ?>
                        <a style="box-shadow: none;"
                           class="mo_lms_button mo_lms_button1"<?php echo 'disabled'; ?>><?php echo esc_html( molms_lt( 'Customize Email Template' ) ); ?></a>
                        <span style="margin-left:10px;"></span>
                        <a style="box-shadow: none;"
                           class="mo_lms_button mo_lms_button1"<?php echo 'disabled'; ?> ><?php echo esc_html( molms_lt( 'Customize SMS Template' ) ); ?></a>
					<?php }
				} else { ?>
                    <a class="mo_lms_button mo_lms_button1"
					   <?php echo 'disabled'; ?>style="pointer-events: none;cursor: default;box-shadow: none;"><?php echo esc_html( molms_lt( 'Customize Email Template' ) ); ?></a>
                    <span style="margin-left:10px;"></span>
                    <a class="mo_lms_button mo_lms_button1"<?php echo 'disabled'; ?>
                       style="pointer-events: none;cursor: default;box-shadow: none;"><?php echo esc_html( molms_lt( 'Customize SMS Template' ) ); ?></a>
				<?php } ?>
            </div>
        </div>
        <br>

        <div>
            <h3><?php echo esc_html( molms_lt( 'Integrate your websites\'s theme with the 2FA plugin\'s popups' ) ); ?></h3>
            <hr>
            <div style="margin-left:2%">
                <p><?php echo esc_html( molms_lt( 'Contact Us through the support forum in the right for the UI integration.' ) ); ?></p>
                <input type="submit" name="submit"
                       style="margin-left:2%; background-color: #20b2aa; color: white; box-shadow: none;"
                       value="Save Settings" class="mo_lms_button mo_lms_button1" <?php
				echo 'disabled'; ?> />
            </div>

        </div>
    </div>
    <br>
    <form style="display:none;" id="mo2fa_addon_loginform"
          action="<?php echo esc_url_raw( get_site_option( 'mo2f_MOLMS_HOST_NAME' ) ) . '/moas/login'; ?>"
          target="_blank" method="post">
        <input type="email" name="username"
               value="<?php echo esc_html( $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user->ID ) ); ?>"/>
        <input type="text" name="redirectUrl" value=""/>
    </form>
    <script>
        function mo2fLoginMiniOrangeDashboard(redirectUrl) {
            document.getElementById('mo2fa_addon_loginform').elements[1].value = redirectUrl;
            jQuery('#mo2fa_addon_loginform').submit();
        }
    </script>

<?php
function show_2_factor_custom_design_options( $current_user ) {
	global $molms_dirName;
	include $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'link-tracer.php';
	?>

    <div>
        <div id="mo2f_custom_addon_hide">

        </div>
        <form name="f" id="custom_css_reset_form" method="post" action="">
            <input type="hidden" name="option" value="mo_auth_custom_design_options_reset"/>

            <h3><?php echo esc_html( molms_lt( 'Customize UI of Login Pop up\'s' ) ); ?><a
                        href='<?php echo esc_url_raw( $two_factor_premium_doc['custom login popup'] ); ?>'
                        target="_blank">
                    <span class="dashicons dashicons-text-page"
                          style="font-size:19px;color:#269eb3;float: right;"></span>

                </a></h3>
            <input type="submit" name="submit" value="Reset Settings" class="mo_lms_button mo_lms_button1"
                   style="float:right; background-color: #20b2aa; color: white;box-shadow: none;"<?php
			echo 'disabled'; ?> />

        </form>
        <form name="f" id="custom_css_form" method="post" action="">
            <input type="hidden" name="option" value="mo_auth_custom_design_options_save"/>


            <table class="mo2f_settings_table" style="margin-left:2%">
                <tr>
                    <td><?php echo esc_html( molms_lt( 'Background Color:' ) ); ?> </td>
                    <td><input type="text" id="mo2f_custom_background_color"
                               name="mo2f_custom_background_color" <?php echo 'disabled'; ?>
                               value="<?php echo esc_html( get_site_option( 'mo2f_custom_background_color' ) ) ?>"
                               class="my-color-field"/></td>
                </tr>
                <tr>
                    <td><?php echo esc_html( molms_lt( 'Popup Background Color:' ) ); ?> </td>
                    <td><input type="text" id="mo2f_custom_popup_bg_color"
                               name="mo2f_custom_popup_bg_color" <?php echo 'disabled'; ?>
                               value="<?php echo esc_html( get_site_option( 'mo2f_custom_popup_bg_color' ) ) ?>"
                               class="my-color-field"/></td>
                </tr>
                <tr>
                    <td><?php echo esc_html( molms_lt( 'Button Color:' ) ); ?> </td>
                    <td><input type="text" id="mo2f_custom_button_color"
                               name="mo2f_custom_button_color" <?php echo 'disabled'; ?>
                               value="<?php echo esc_html( get_site_option( 'mo2f_custom_button_color' ) ) ?>"
                               class="my-color-field"/></td>
                </tr>
                <tr>
                    <td><?php echo esc_html( molms_lt( 'Links Text Color:' ) ); ?> </td>
                    <td><input type="text" id="mo2f_custom_links_text_color"
                               name="mo2f_custom_links_text_color" <?php echo 'disabled'; ?>
                               value="<?php echo esc_html( get_site_option( 'mo2f_custom_links_text_color' ) ) ?>"
                               class="my-color-field"/></td>
                </tr>
                <tr>
                    <td><?php echo esc_html( molms_lt( 'Popup Message Text Color:' ) ); ?> </td>
                    <td><input type="text" id="mo2f_custom_notif_text_color"
                               name="mo2f_custom_notif_text_color" <?php echo 'disabled'; ?>
                               value="<?php echo esc_html( get_site_option( 'mo2f_custom_notif_text_color' ) ) ?>"
                               class="my-color-field"/></td>
                </tr>
                <tr>
                    <td><?php echo esc_html( molms_lt( 'Popup Message Background Color:' ) ); ?> </td>
                    <td><input type="text" id="mo2f_custom_notif_bg_color"
                               name="mo2f_custom_notif_bg_color" <?php echo 'disabled'; ?>
                               value="<?php echo esc_html( get_site_option( 'mo2f_custom_notif_bg_color' ) ) ?>"
                               class="my-color-field"/></td>
                </tr>
                <tr>
                    <td><?php echo esc_html( molms_lt( 'OTP Token Background Color:' ) ); ?> </td>
                    <td><input type="text" id="mo2f_custom_otp_bg_color"
                               name="mo2f_custom_otp_bg_color" <?php echo 'disabled'; ?>
                               value="<?php echo esc_html( get_site_option( 'mo2f_custom_otp_bg_color' ) ) ?>"
                               class="my-color-field"/></td>
                </tr>
                <tr>
                    <td><?php echo esc_html( molms_lt( 'OTP Token Text Color:' ) ); ?> </td>
                    <td><input type="text" id="mo2f_custom_otp_text_color"
                               name="mo2f_custom_otp_text_color" <?php echo 'disabled'; ?>
                               value="<?php echo esc_html( get_site_option( 'mo2f_custom_otp_text_color' ) ) ?>"
                               class="my-color-field"/></td>
                </tr>
            </table>
            </br>


        </form>
    </div>
	<?php
}