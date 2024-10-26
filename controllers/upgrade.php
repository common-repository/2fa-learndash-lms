<?php
global $molms_db_queries, $molms_dirName;
$user                   = wp_get_current_user();
$is_customer_registered = ( get_site_option( 'mo_2factor_admin_registration_status' ) == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' );
$Back_button            = admin_url() . 'admin.php?page=molms_two_fa';
include $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'upgrade.php';