<?php

echo '<!--Register with miniOrange-->
	<form name="f" method="post" action="">
		<input type="hidden" name="option" value="molms_register_customer" />
		<div class="mo_lms_divided_layout">
		<div class="mo_lms_setting_layout" style="margin-bottom:30px;">
			
				<h3>Register with miniOrange
					<div style="float: right;">';
if ( isset( $two_fa ) ) {
	echo '<a class="mo_lms_button mo_lms_button1" href="' . esc_url_raw( $two_fa ) . '">Back</a> ';
}
echo '</div>
				</h3>
				<p>Just complete the short registration below to configure miniOrange 2-Factor plugin. Please enter a valid email id that you have access to. You will be able to move forward after verifying an OTP that we will send to this email.</p>
				<table class="mo_lms_settings_table">
					<tr>
						<td><b><font color="#FF0000">*</font>Email:</b></td>
						<td><input class="mo_lms_table_textbox" type="email" name="email"
							required placeholder="person@example.com"
							value="' . esc_html( $user->user_email ) . '" /></td>
					</tr>

					<tr>
						<td><b><font color="#FF0000">*</font>Password:</b></td>
						<td><input class="mo_lms_table_textbox" required type="password"
							name="password" placeholder="Choose your password (Min. length 6)" /></td>
					</tr>
					<tr>
						<td><b><font color="#FF0000">*</font>Confirm Password:</b></td>
						<td><input class="mo_lms_table_textbox" required type="password"
							name="confirmPassword" placeholder="Confirm your password" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><br><input type="submit" name="submit" value="Create Account" style="width:130px;padding: 6px 10px;"
							class="mo_lms_button mo_lms_button1" />
						
							<a href="#mo2f_account_exist">Already have an account?</a>

					</tr>
				</table>
		</div>	
		</div>
		<input type="hidden" name="nonce"
               value=' . esc_attr( wp_create_nonce( "mo2f-account-nonce" ) ) . ' >
	</form>
	 <form name="f" method="post" action="" class="mo2f_verify_customerform">
        <input type="hidden" name="option" value="molms_verifycustomer">
         <input type="hidden" name="molms_verifycustomer_nonce"
               value=' . esc_attr( wp_create_nonce( "mo2f-goto-verifycustomer-nonce" ) ) . ' >
       </form>';

wp_print_scripts( 'jquery-core' );
?>
<script>

    jQuery('a[href=\"#mo2f_account_exist\"]').click(function (e) {
        jQuery('.mo2f_verify_customerform').submit();
    });


</script>