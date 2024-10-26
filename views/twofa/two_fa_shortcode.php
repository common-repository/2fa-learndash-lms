<?php
global $molms_dirName;
$setup_dirName = $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'link-tracer.php';
global $current_user;
$current_user = wp_get_current_user();
?>

<div class="mo_lms_setting_layout">


    <div id="mo2f_hide_shortcode_content">
        <h1>3. Shortcode
            <span style="text-align: right;font-size: large;"></span>
        </h1>
        <hr>
        <h3><?php echo esc_html__( 'List of Shortcodes', '2fa-learndash-lms' ); ?><a
                    href='<?php echo esc_url_raw( $two_factor_premium_doc['Shortcode'] ); ?>' target="_blank"><span
                        class="dashicons dashicons-text-page" style="font-size:19px;color:#269eb3;float: right;"></span></a>
        </h3>
        <ol style="margin-left:2%">
            <li>
                <b><?php echo esc_html__( 'Enable Two Factor: ', '2fa-learndash-lms' ); ?></b> <?php echo esc_html__( 'This shortcode provides an option to turn on/off 2-factor by user.', '2fa-learndash-lms' ); ?>
            </li>
            <li>
                <b><?php echo esc_html__( 'Enable Reconfiguration: ', '2fa-learndash-lms' ); ?></b> <?php echo esc_html__( 'This shortcode provides an option to configure the Google Authenticator and Security Questions by user.', '2fa-learndash-lms' ); ?>
            </li>
            <li>
                <b><?php echo esc_html__( 'Enable Remember Device: ', '2fa-learndash-lms' ); ?></b> <?php echo esc_html__( ' This shortcode provides\'Enable Remember Device\' from your custom login form.', '2fa-learndash-lms' ); ?>
            </li>
        </ol>
    </div>
    <h3><?php echo esc_html( molms_lt( 'Shortcodes' ) ); ?></h3>
    <hr>
    <div style="margin-left:2%">
        <p>1. <b style="font-size:16px;color: #0085ba;">[miniorange_enable2fa]</b> :<?php echo esc_html( molms_lt(
				' Add this shortcode to provide
                    the option to turn on/off 2-factor by user.'
			) ); ?><br><br>
            2. <b style="font-size:16px;color: #0085ba;">[mo2f_enable_reconfigure]</b> : <?php echo esc_html( molms_lt(
				'Add this shortcode to
                    provide the option to configure the Google Authenticator and Security Questions by user.'
			) ); ?><br>
            <br>
            3. <b style="font-size:16px;color: #0085ba;">[mo2f_enable_rba_shortcode]</b> :<?php echo esc_html( molms_lt(
				' Add this shortcode to
                    \'Enable Remember Device\' from your custom login form.'
			) ); ?>
        </p>

        <form name="f" id="custom_login_form" method="post" action="">
			<?php echo esc_html( molms_lt( 'Enter the id of your custom login form to use \'Enable Remember Device\' on the login page:' ) ); ?>
            <input type="text" class="mo2f_table_textbox" id="mo2f_rba_loginform_id"
                   name="mo2f_rba_loginform_id" <?php
			echo 'disabled';
			?> value="<?php echo esc_html( get_site_option( 'mo2f_rba_loginform_id' ) ) ?>"/>
            <br><br>
            <input type="hidden" name="option" value="custom_login_form_save"/>
            <input type="submit" name="submit" value="Save Settings" style="background-color: #20b2aa; color: white;"
                   class="mo_lms_button mo_lms_button1" <?php

			echo 'disabled';
			?> />
        </form>
    </div>
</div>