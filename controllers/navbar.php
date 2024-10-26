<?php

global $molms_utility, $molms_dirName;


$profile_url = add_query_arg( array( 'page' => 'molms_account' ), esc_url_raw( $_SERVER['REQUEST_URI'] ) );
$license_url = add_query_arg( array( 'page' => 'molms_upgrade' ), esc_url_raw( $_SERVER['REQUEST_URI'] ) );
$help_url    = add_query_arg( array( 'page' => 'molms_troubleshooting' ), esc_url_raw( $_SERVER['REQUEST_URI'] ) );
$addons_url  = add_query_arg( array( 'page' => 'molms_addons' ), esc_url_raw( $_SERVER['REQUEST_URI'] ) );
$two_fa      = add_query_arg( array( 'page' => 'molms_two_fa' ), esc_url_raw( $_SERVER['REQUEST_URI'] ) );
//Added for new design
$upgrade_url       = add_query_arg( array( 'page' => 'molms_upgrade' ), esc_url_raw( $_SERVER['REQUEST_URI'] ) );
$request_trial_url = add_query_arg( array( 'page' => 'molms_request_trial' ), esc_url_raw( $_SERVER['REQUEST_URI'] ) );
//dynamic
$logo_url                    = plugin_dir_url( dirname( __FILE__ ) ) . 'includes/images/miniorange_logo.png';
$login_with_usename_only_url = plugin_dir_url( dirname( __FILE__ ) ) . 'includes/images/login-with-password-and-2fa.png';
$hide_login_form_url         = plugin_dir_url( dirname( __FILE__ ) ) . 'includes/images/hide_login_form.png';
$active_tab                  = sanitize_text_field( $_GET['page'] );

require $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'navbar.php';
