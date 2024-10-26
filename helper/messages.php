<?php

class molms_Messages {

	const TWO_FA_ON_LOGIN_PROMPT_ENABLED = "2FA prompt on the WP Login Page Enabled.";
	const TWO_FA_ON_LOGIN_PROMPT_DISABLED = "2FA prompt on the WP Login Page Disabled.";
	const TWO_FA_PROMPT_LOGIN_PAGE = 'Please disable Login with 2nd facor only to enable 2FA prompt on login page.';
	const ERROR_OCCURED = 'Error has been occured.';

	//support form
	const SUPPORT_FORM_VALUES = "Please submit your query along with email.";
	const SUPPORT_FORM_SENT = "Thanks for getting in touch! We shall get back to you shortly.";
	const SUPPORT_FORM_ERROR = "Your query could not be submitted. Please try again.";
	// request trial form
	const TRIAL_FORM_ERROR = "Please fill out all the fields.";
	const REQUIRED_FIELDS = "Please enter all the required fields";
	const RESET_PASS = "You password has been reset successfully and sent to your registered email. Please check your mailbox.";
	const FEEDBACK = "<div class='custom-notice notice notice-warning feedback-notice'><p><p class='notice-message'>Looking for a feature? Help us make the plugin better. Send us your feedback using the Support Form below.</p><button class='feedback notice-button'><i>Dismiss</i></button></p></div>";
	const TRIAL_REQUEST_ALREADY_SENT = "You have already sent a trial request for premium plugin. We will get back to you on your email soon.";
	//registration messages
	const PASS_LENGTH = "Choose a password with minimum length 6.";
	const OTP_SENT = 'A passcode is sent to {{method}}. Please enter the otp below.';
	const REG_SUCCESS = 'Your account has been retrieved successfully.';
	const ACCOUNT_EXISTS = 'You already have an account with miniOrange. Please enter a valid password.';
	const INVALID_OTP = 'Invalid one time passcode. Please enter a valid passcode.';
	const PASS_MISMATCH = 'Password and Confirm Password do not match.';
	const SELECT_A_PLAN = "Please select a plan";
	const FREE_TRIAL_MESSAGE_TRIAL_PAGE = "
        <div class='notice notice-warning molms-notice-warning molms-trial-notice MOWrn' id='molms_is-dismissible'>
        <form id='FREE_TRIAL_MESSAGE_TRIAL_PAGE' method='post' action=''>
        <p>
        <img style='width:15px;' src='" . MOLMS_PLUGIN_URL . 'includes/images/miniorange_icon.png' . "'>&nbsp&nbspInterested in the Trial of<b> 2 Factor Authentication Premium Plugins?</b> Click on the button below to get trial for <strong>7 days</strong>.
        (<em>No credit card required</em>)
        </p>
        <p style='height:25px; padding: 10px;'>
        <a class='button button-primary notice-button' href='admin.php?page=molms_request_trial' id='molms_trial_redirect'>Get Trial</a>
        <input type='hidden' name='molms_dismiss_trial' value='molms_dismiss_trial'/>
        <button type='submit' class='molms-trial-dismiss notice-button'><i>DISMISS</i></button>
        </p>
        </form>
        </div>
        ";

	const FREE_TRIAL_MESSAGE_ACCOUNT_PAGE = "
        <div class='notice notice-warning molms-notice-warning molms-trial-notice MOWrn' id='molms_is-dismissible'>
        <form id='FREE_TRIAL_MESSAGE_TRIAL_PAGE' method='post' action=''>
        <p>
        <img style='width:15px;' src='" . MOLMS_PLUGIN_URL . 'includes/images/miniorange_icon.png' . "'>&nbsp&nbspInterested in the Trial of<b> 2 Factor Authentication Premium Plugins?</b> Click on the button below to get trial for <strong>7 days</strong>.
        (<em>No credit card required</em>)
        </p>
        <p style='height:25px; padding: 10px;'>
        <a class='button button-primary notice-button' href='admin.php?page=molms_account' id='molms_trial_redirect'>Get Trial</a>
        <input type='hidden' name='molms_dismiss_trial' value='molms_dismiss_trial'/>
        <button type='submit' class='molms-trial-dismiss notice-button'><i>DISMISS</i></button>
        </p>
        </form>
        </div>
        ";

	public static function showMessage( $message, $data = array() ) {
		$message = constant( "self::" . $message );
		foreach ( $data as $key => $value ) {
			$message = str_replace( "{{" . $key . "}}", $value, $message );
		}

		return $message;
	}
}
