<?php
global $molms_dirName;
$setup_dirName = $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'link-tracer.php';
require $setup_dirName;

?>
    <div class="mo_lms_setting_layout" style="min-height:1310px">
        <form id="settings_from_addon" method="post" action="">
            <input type="hidden" name="option" value="mo_auth_addon_settings_save"/>
            <h1><?php echo esc_html( molms_lt( '1. Prevent Account Sharing' ) ); ?>
                <span style="text-align: right;font-size: large;"></span><a
                        href='<?php echo esc_url_raw( $two_factor_premium_doc['Remember Device'] ); ?>'
                        target="_blank">
                    <span class="dashicons dashicons-text-page"
                          style="font-size:19px;color:#269eb3;float: right;"></span>

                </a>
            </h1>
            <p id="rba_description">
                It helps you to remember the device where you will not be asked to authenticate the 2-factor if you
                login from the remembered Device.</p>
            <hr>
            <br>
			<?php mo2f_rba_functionality(); ?>


        </form>

        <br><br>

        <h3><?php echo esc_html( molms_lt( 'Limit Number Of Device' ) ); ?></h3>
        <hr>
        <p><?php echo esc_html( molms_lt( 'In this feature, the admin can restrict the number of devices from which the user can access the website. If the device limit is exceeded the admin can set three actions where it can allow the users to login, deny the access or challenge the user for authentication.' ) ); ?>
            <br><br>


        </p>
        <div class="mo_lms_setting_layout" style="background-color: aliceblue; border:none;">
            <h3 style="display: inline;float: left">Device Configuration</h3>
            <h3 style="color: red;"><b>&nbsp;&nbsp;&nbsp;&nbsp;
                </b></h3>
            <hr>
            <label class="mo_lms_switch">
                <input type="checkbox" id="pluginWAF" name="pluginWAF" <?php echo 'disabled'; ?>>
                <span class="mo_lms_slider mo_lms_round"></span>
            </label>
            <span class="checkbox_text text_fonts" id="Allow_User_to_Register_Device" style="font-weight: 500;">Allow User to Register Device.</span>
            <br><br>
            <span class="input_field_fonts" style="font-weight: 500;">Number of Device Registrations Allowed :</span>
            <input type="text" name="allowedDeviceRegistrations" maxlength="2" value="10"
                   id="allowedDeviceRegistrations" class="form-control" title="Please enter Numbers only"
                   pattern="\d*" <?php echo 'disabled'; ?>></p>
            <br>
            <span class="checkbox_text text_fonts" id="Allow_User_to_Register_Device1" style="font-weight: 500;">Action if number of devices exceeded</span>

            <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="radio" name="rbaConfiguration.deviceExceedAction"
                   id="rbaConfiguration_deviceExceedActionCHALLENGE" value="CHALLENGE" class="radio spacing"><label
                    for="rbaConfiguration_deviceExceedActionCHALLENGE" style="font-weight: 500;" class="radio spacing">Challenge</label>
            <input type="radio" name="rbaConfiguration.deviceExceedAction" id="rbaConfiguration_deviceExceedActionDENY"
                   checked="checked" value="DENY" class="radio spacing"><label
                    for="rbaConfiguration_deviceExceedActionDENY" style="font-weight: 500;"
                    class="radio spacing">Deny</label>
            <script type="text/javascript">
                document.getElementById("rbaConfiguration_deviceExceedActionCHALLENGE").disabled = true;
                document.getElementById("rbaConfiguration_deviceExceedActionDENY").disabled = true;
            </script>
            <div style="margin-top: 20px;">
                <label class="mo_lms_switch">
                    <input type="checkbox" id="pluginWAF" name="pluginWAF" <?php echo 'disabled'; ?>>
                    <span class="mo_lms_slider mo_lms_round"></span>
                </label>
                <span class="checkbox_text text_fonts" style="font-weight: 500;">Send email alerts to Users if number of Device registrations exceeded allowed count.</span>
            </div>

        </div>
        <div style="margin-top: 337px;margin-left: 8px;">
            <button style="box-shadow: none;" class="mo_lms_button mo_lms_button1" id="set_device_limit_button"
                    target="_blank"><?php echo esc_html( molms_lt( 'Set Device Limit' ) ); ?>
            </button>
        </div>
        <script type="text/javascript">
            document.getElementById("set_device_limit_button").disabled = true;
        </script>


        <br><br>
        <div>
            <h3><?php echo esc_html( molms_lt( 'IP Restriction: Limit users to login from specific IPs' ) ); ?></h3>
            <hr>
            <p><?php echo esc_html( molms_lt( 'The Admin can enable IP restrictions for the users. It will provide additional security to the accounts and perform different action to the accounts only from the listed IP Ranges. If user tries to access with a restricted IP, Admin can set three action: Allow, challenge or deny. Depending upon the action it will allow the user to login, challenge(prompt) for authentication or deny the access.' ) ); ?>

                <br><br>

            </p>
            <div class="mo_lms_setting_layout" style="background-color: aliceblue; border:none;">
                <h3 style="display: inline;float: left">IP Blocking Configuration </h3>
                <h3 style="color: red;"></h3>
                <hr>
                <label class="mo_lms_switch">
                    <input type="checkbox" id="pluginWAF" name="pluginWAF" <?php echo 'disabled'; ?>>
                    <span class="mo_lms_slider mo_lms_round"></span>
                </label>&nbsp;&nbsp;&nbsp;&nbsp;
                <span class="checkbox_text text_fonts" id="Allow_User_to_Register_Device" style="font-weight: 500;">Allow All IPs</span>
                <br><br>
                <div class="col-md-7 top-buffer">
                    <span class="input_field_fonts" style="font-weight: 500;">Action if IP Address is not in the given list:</span>

                </div>

                <div class="radio col-md-5 col-xs-offset-1">
                    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="radio" name="rbaConfiguration.deviceExceedAction"
                           id="rbaConfiguration_deviceExceedActionCHALLENGE2" value="CHALLENGE"
                           class="radio spacing"><label for="rbaConfiguration_deviceExceedActionCHALLENGE"
                                                        style="font-weight: 500;" class="radio spacing">Allow</label>
                    <input type="radio" name="rbaConfiguration.deviceExceedAction"
                           id="rbaConfiguration_deviceExceedActionCHALLENGE1" value="CHALLENGE"
                           class="radio spacing"><label for="rbaConfiguration_deviceExceedActionCHALLENGE"
                                                        style="font-weight: 500;"
                                                        class="radio spacing">Challenge</label>
                    <input type="radio" name="rbaConfiguration.deviceExceedAction"
                           id="rbaConfiguration_deviceExceedActionDENY1" checked="checked" value="DENY"
                           class="radio spacing"><label for="rbaConfiguration_deviceExceedActionDENY"
                                                        style="font-weight: 500;" class="radio spacing">Deny</label>
                    <br><br>

                </div>
                <script type="text/javascript">
                    document.getElementById("rbaConfiguration_deviceExceedActionCHALLENGE2").disabled = true;
                    document.getElementById("rbaConfiguration_deviceExceedActionCHALLENGE1").disabled = true;
                    document.getElementById("rbaConfiguration_deviceExceedActionDENY1").disabled = true;

                </script>

                <input type="text" name="allowedDeviceRegistrations" maxlength="2" id="allowedDeviceRegistrations"
                       class="form-control" title="Please enter Numbers only" pattern="\d*" placeholder="Enter Start IP"
                       style="background-color: white;" <?php echo 'disabled'; ?>>&nbsp;&nbsp;
                <input type="text" name="allowedDeviceRegistrations" maxlength="2" id="allowedDeviceRegistrations"
                       class="form-control" title="Please enter Numbers only" pattern="\d*" placeholder="Enter End IP"
                       style="background-color: white;" <?php echo 'disabled'; ?>> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label class="mo_lms_switch">
                    <input type="checkbox" id="pluginWAF" name="pluginWAF" <?php echo 'disabled'; ?>>
                    <span class="mo_lms_slider mo_lms_round"></span>
                </label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" style="    background-color: forestgreen;" id="add_ip"
                        class="btn btn-success addipbutton pull-right">
                    <i class="glyphicon-white glyphicon-plus">+</i>
                </button>
                <br><br>
                <input type="text" name="allowedDeviceRegistrations" maxlength="2" id="allowedDeviceRegistrations"
                       class="form-control" title="Please enter Numbers only" pattern="\d*" placeholder="Enter Start IP"
                       style="background-color: white;" <?php echo 'disabled'; ?>>&nbsp;&nbsp;
                <input type="text" name="allowedDeviceRegistrations" maxlength="2" id="allowedDeviceRegistrations"
                       class="form-control" title="Please enter Numbers only" pattern="\d*" placeholder="Enter End IP"
                       style="background-color: white;" <?php echo 'disabled'; ?>> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label class="mo_lms_switch">
                    <input type="checkbox" id="pluginWAF" name="pluginWAF" <?php echo 'disabled'; ?>>
                    <span class="mo_lms_slider mo_lms_round"></span>
                </label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" style="    background-color: forestgreen;" id="add_ip"
                        class="btn btn-success addipbutton pull-right">
                    <i class="glyphicon-white glyphicon-plus">+</i>
                </button>


            </div>
            <div style="margin-top: 345px;margin-left: 8px;">
                <a style="box-shadow: none;"
                   class="mo_lms_button mo_lms_button1"
                   target="_blank" <?php echo 'disabled'; ?>><?php echo esc_html( molms_lt( 'Restrict IP' ) ); ?></a>
            </div>
        </div>
    </div>

    <script>


        jQuery('#mo2f_hide_rba_content').hide();
        jQuery('#mo2f_activate_rba_addon').hide();

    </script>
<?php


function mo2f_rba_functionality() {
	global $current_user;
	$current_user = wp_get_current_user();
	global $dbQueries, $molms_db_queries;
	$upgrade_url = add_query_arg( array( 'page' => 'molms_upgrade' ), esc_url_raw( $_SERVER['REQUEST_URI'] ) ); ?>

    <div>
        <div class="mo_lms_setting_layout" style="background-color: aliceblue; border:none;">
            <h3>Remember device</h3>
            <input type="checkbox" id="mo2f_remember_device" name="mo2f_remember_device"
                   value="1" <?php checked( get_site_option( 'mo2f_remember_device' ) == 1 );
			echo 'disabled'; ?> /><?php echo esc_html( molms_lt( 'Enable' ) ); ?>
            '<b><?php echo esc_html( molms_lt( 'Remember device' ) ); ?></b>' <?php echo esc_html( molms_lt( 'option ' ) ); ?>
            <br><span
                    style="color:red;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<?php echo esc_html( molms_lt( 'Applicable only for ' ) ); ?>
                <i><?php echo esc_html( molms_lt( 'Login with password + 2nd Factor' ) ); ?></i></span><br>
            <br>
            <div class="mo2f_advanced_options_note"><p style="padding:5px;">
                    <i><?php echo esc_html( molms_lt( ' Checking this option will display an option ' ) ); ?>
                        '<b><?php echo esc_html( molms_lt( 'Remember this device' ) ); ?></b>'<?php echo esc_html( molms_lt( 'on 2nd factor screen. In the next login from the same device, user will bypass 2nd factor, i.e. user will be logged in through username + password only.' ) ); ?>
            </div>
            </i></p>
        </div>
        <div style="margin-left: 8px;">
            <button style="box-shadow: none;" class="mo_lms_button mo_lms_button1" id="set_device_limit_button"
                    target="_blank"><?php echo esc_html( molms_lt( 'Save Settings' ) ); ?>
            </button>
        </div>
    </div>


	<?php
}


?>