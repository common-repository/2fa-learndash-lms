<?php

echo '	<form name="f" method="post" action="">
			<input type="hidden" name="option" value="molms_verify_customer" />
			<div class="mo_lms_divided_layout">
				<div class="mo_lms_setting_layout">
					<h3>Login with miniOrange
						<div style="float: right;">';
if ( isset( $two_fa ) ) {
	echo '<a class="mo_lms_button mo_lms_button1" href="' . esc_url( $two_fa ) . '">Back</a> ';
}
echo '</div>
					</h3>
					<p><b>It seems you already have an account with miniOrange. Please enter your miniOrange email and password.</td><a target="_blank" href="https://login.xecurify.com/moas/idp/resetpassword"> Click here if you forgot your password?</a></b></p>
					<table class="mo_lms_settings_table">
						<tr>
							<td><b><font color="#FF0000">*</font>Email:</b></td>
							<td><input class="mo_lms_table_textbox" type="email" name="email"
								required placeholder="person@example.com"
								value="' . esc_html( $admin_email ) . '" /></td>
						</tr>
						<tr>
							<td><b><font color="#FF0000">*</font>Password:</b></td>
							<td><input class="mo_lms_table_textbox" required type="password"
								name="password" placeholder="Enter your miniOrange password" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><input type="submit" class="mo_lms_button mo_lms_button1" />
								<a href="#cancel_link" class="mo_lms_button mo_lms_button1">Go Back to Registration</a>
						</tr>
					</table>
				</div>
			</div>			
		<input type="hidden" name="nonce"
               value=' . esc_attr( wp_create_nonce( "mo2f-account-nonce" ) ) . ' >
		</form>
		<form id="cancel_form" method="post" action="">
			<input type="hidden" name="option" value="molms_cancel" />	
			<input type="hidden" name="nonce"
               value=' . esc_attr( wp_create_nonce( "mo2f-account-nonce" ) ) . ' >
		</form>
		<script>
			jQuery(document).ready(function(){
				$(\'a[href="#cancel_link"]\').click(function(){
					$("#cancel_form").submit();
				});		
			});
		</script>';
