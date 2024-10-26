<?php

global $molms_utility, $molms_dirName;

$controller = $molms_dirName . 'controllers' . DIRECTORY_SEPARATOR;
if ( current_user_can( 'administrator' ) ) {
	if ( isset( $_GET['page'] ) ) {
		$page = sanitize_text_field( $_GET['page'] );
		if ( $page == 'molms_upgrade' ) {
			include $controller . 'upgrade.php';
		} else {
			include $controller . 'navbar.php';
			switch ( $page ) {
				case 'molms_account':
					include $controller . 'account.php';
					break;
				case 'molms_troubleshooting':
					include $controller . 'troubleshooting.php';
					break;
				case 'molms_two_fa':
					include $controller . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa.php';
					break;
				case 'molms_request_trial':
					include $controller . 'request_trial.php';
					break;
			}
			include $controller . 'support.php';
		}
	}
}
