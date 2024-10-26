<?php
$user         = wp_get_current_user();
$userID       = wp_get_current_user()->ID;
$onprem_admin = get_site_option( 'mo2f_onprem_admin' );
$roles        = ( array ) $user->roles;
$is_onprem    = MOLMS_IS_ONPREM;
$flag         = 0;
foreach ( $roles as $role ) {
	if ( get_site_option( 'mo2fa_' . $role ) == '1' ) {
		$flag = 1;
	}
}

if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) != 'molms_upgrade' ) {
	echo '<div class="wrap">
				<div><img  style="float:left;margin-top:5px;" src="' . esc_url( $logo_url ) . '"></div>
				<h1>
					miniOrange 2-Factor 
					<a class="add-new-h2" style="font-size:17px;border-radius:4px;"  href="' . esc_url( $profile_url ) . '">Account</a>
					<a class="add-new-h2" style="font-size:17px;border-radius:4px;" href="' . esc_url( $help_url ) . '">FAQs</a>
					<a class="license-button add-new-h2" style="font-size:17px;border-radius:4px;" href="' . esc_url( $request_trial_url ) . '">Trial</a>
                    <a class="add-new-h2" id ="mo_2fa_upgrade_tour" style="font-size:17px;border-radius:4px;background-color:orange; color:black;" href="' . esc_url( $upgrade_url ) . '">See Plans and Pricing</a>';

	echo '<div id = "lms_nav_message"></div>';
	echo '</h1>			
		</div>'; ?>
	<?php
}