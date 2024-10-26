<?php
global $molms_db_queries;
$current_user    = wp_get_current_user();
$mo2f_user_email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );
require_once $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_addon.php';
