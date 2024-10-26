<div class="mo_lms_setting_layout">
    <form name="f" id="session_settings_form" method="post" action="">
        <input type="hidden" name="option" value="mo2f_session_settings_save"/>
        <div>
            <table style="width:100%">
                <tr>
                    <th align="left">
                        <h3>User Session Control: <?php if ( ! $session_addon ) {
								echo '<a href="' . esc_url_raw( $upgrade_url ) . '" style="color: red">'; ?>[ PREMIUM ]</a> <?php
							} ?>
                            <br>
                            <p><i class="mo_lms_not_bold">This will help you limit the number of simultaneous sessions
                                    for your users. You can decide to allow access to the new session after limit is
                                    reached and destroy all other sessions or block access to the new session when the
                                    limit is reached.</i></p>
                        </h3>
                    </th>
                    <th align="right">
                        <a href='<?php echo esc_url_raw( $two_factor_premium_doc['User Session Control'] ); ?>'
                           target="_blank">
                            <span class="dashicons dashicons-text-page molms-dashicons"
                                  style="font-size:19px;color:#269eb3;float: right;"></span>
                        </a>
                        <label class='mo_lms_switch'>
                            <input type="checkbox" id="mo2f_simultaneous_session_enable"
                                   name="mo2f_simultaneous_session_enable"
                                   value="1" <?php checked( get_site_option( 'mo2f_simultaneous_session_enable' ) == 1 ) ?> />
                            <span class='mo_lms_slider mo_lms_round'></span>
                        </label>
                    </th>
                </tr>
            </table>
        </div>
        <br>
        <div>
            <h3>Number of Sessions
                <input type="text" name="mo2f_number_of_sessions" id="mo2f_number_of_sessions" min="1"
                       value="<?php echo esc_html( intval( $sessions_allowed ) ) ?>"
                       placeholder="Enter limit of sessions">
            </h3>
        </div>
        <br>
        <div>
            <input type="radio" name="mo2f_allow_access" id="mo2f_allow_access"
                   value="1" <?php checked( get_site_option( 'mo2f_simultaneous_session_give_entry' ) ); ?>>Allow access
            <input type="radio" name="mo2f_allow_access" id="mo2f_block_access"
                   value="0" <?php checked( ! get_site_option( 'mo2f_simultaneous_session_give_entry' ) ); ?>>Block
            Access
        </div>

        <hr>
        <div>
            <table style="width:100%">
                <tr>
                    <th align="left">
                        <h3>Idle Session: <?php if ( ! $session_addon ) {
								echo '<a href="' . esc_url_raw( $upgrade_url ) . '" style="color: red">'; ?>[ PREMIUM ]</a> <?php
							} ?>
                            <br>
                            <p><i class="mo_lms_not_bold">This will allow you to logout a Wordpress user who was
                                    inactive for a period of time. You can set the amount of hours after which you want
                                    to logout the inactive user.</i></p>
                        </h3>
                    </th>
                    <th align="right">
                        <a href='<?php echo esc_url_raw( $two_factor_premium_doc['Idle Session'] ); ?>' target="_blank">
                            <span class="dashicons dashicons-text-page molms-dashicons"
                                  style="font-size:19px;color:#269eb3;float: right;"></span>
                        </a>
                        <label class='mo_lms_switch'>
                            <input type="checkbox" id="mo2f_idle_session_logout_enable"
                                   name="mo2f_idle_session_logout_enable"
                                   value="1" <?php checked( get_site_option( 'mo2f_idle_session_logout_enable' ) == 1 ) ?> />
                            <span class='mo_lms_slider mo_lms_round'></span>
                        </label>
                    </th>
                </tr>
            </table>
        </div>
        <br>
        <div>
            <h3>Number of Hours
                <input type="text" name="mo2f_number_of_idle_hours" id="mo2f_number_of_idle_hours" min="1"
                       value="<?php echo esc_html( intval( $idle_hours ) ) ?>" placeholder="Enter number of hours">
            </h3>
        </div>

        <hr>
        <div>
            <table style="width:100%">
                <tr>
                    <th align="left">
                        <h3>Set Session Time: <?php if ( ! $session_addon ) {
								echo '<a href="' . esc_url_raw( $upgrade_url ) . '" style="color: red">'; ?>[ PREMIUM ]</a> <?php
							} ?>
                            <br>
                            <p><i class="mo_lms_not_bold">This will allow you to set a time limit on the user's
                                    session. After that time, the user would be logged out and will be required to login
                                    again. You can set the time limit after which users will get expired.</i></p>
                        </h3>
                    </th>
                    <th align="right">
                        <a href='<?php echo esc_url_raw( $two_factor_premium_doc['Set Session Time'] ); ?>'
                           target="_blank">
                            <span class="dashicons dashicons-text-page molms-dashicons"
                                  style="font-size:19px;color:#269eb3;float: right;"></span>
                        </a>
                        <label class='mo_lms_switch'>
                            <input type="checkbox" id="mo2f_session_logout_time_enable"
                                   name="mo2f_session_logout_time_enable"
                                   value="1" <?php checked( get_site_option( 'mo2f_session_logout_time_enable' ) == 1 ) ?> />
                            <span class='mo_lms_slider mo_lms_round'></span>
                        </label>
                    </th>
                </tr>
            </table>
        </div>
        <br>
        <div>
            <h3>Number of Hours
                <input type="text" name="mo2f_number_of_timeout_hours" id="mo2f_number_of_timeout_hours" min="1"
                       value="<?php echo esc_html( intval( $session_timeout_hours ) ) ?>"
                       placeholder="Enter number of hours">
            </h3>
        </div>
        <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'molms-session-nonce' ) ); ?>"/>
        <center>
            <button type="submit"
                    class="mo_lms_button mo_lms_button1" <?php echo esc_html( $session_settings_disabled ) ?>>Save
                Settings
            </button>
        </center>
    </form>
</div>
