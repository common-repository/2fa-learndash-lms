<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class molms_2fConstants {
	public static function langTranslate( $text ) {
		switch ( $text ) {
			case 'Successfully validated.':
				return molms_lt( 'Successfully validated.' );
				break;
			case 'SCAN_QR_CODE':
				return molms_lt( 'Please scan the QR Code now.' );
				break;
			case 'miniOrange QR Code Authentication':
				return molms_lt( 'miniOrange QR Code Authentication' );
				break;
			case 'miniOrange Push Notification':
				return molms_lt( 'miniOrange Push Notification' );
				break;
			case 'miniOrange Soft Token':
				return molms_lt( 'miniOrange Soft Token' );
				break;
			case 'Security Questions':
				return molms_lt( 'Security Questions' );
				break;
			case 'Google Authenticator':
				return molms_lt( 'Google Authenticator' );
				break;
			case 'Authy Authenticator':
				return molms_lt( 'Authy Authenticator' );
				break;
			case 'Email Verification':
				return molms_lt( 'Email Verification' );
				break;
			case 'OTP Over SMS':
				return molms_lt( 'OTP Over SMS' );
				break;
			case 'OTP Over SMS And Email':
				return molms_lt( 'OTP Over SMS And Email' );
				break;
			case 'Your license has expired. Please renew your license to continue using our service.':
				return molms_lt( 'Your license has expired. Please renew your license to continue using our service.' );
				break;
			case 'The total transaction limit has been exceeded. Please upgrade your premium plan.':
				return molms_lt( 'The total transaction limit has been exceeded. Please upgrade your premium plan.' );
				break;
			case 'The transaction limit has exceeded.':
				return molms_lt( 'The transaction limit has exceeded.' );
				break;
			case 'GenerateOtpRequest is null':
				return molms_lt( 'GenerateOtpRequest is null' );
				break;
			case 'The sms transaction limit has been exceeded. Please refer to the Licensing Plans tab for purchasing your SMS transactions.':
				return molms_lt( 'The sms transaction limit has been exceeded. Please refer to the Licensing Plans tab for purchasing your SMS transactions.' );
				break;
			case 'The email transaction limit has been exceeded. Please refer to the Licensing Plans tab for purchasing your SMS transactions.':
				return molms_lt( 'The email transaction limit has been exceeded. Please refer to the Licensing Plans tab for purchasing your SMS transactions.' );
				break;
			case 'Transaction limit exceeded. Please contact your administrator':
				return molms_lt( 'Transaction limit exceeded. Please contact your administrator' );
				break;
			case 'Free Trial has already been taken or expired for this plugin. Please upgrade to a premium plan.':
				return molms_lt( 'Free Trial has already been taken or expired for this plugin. Please upgrade to a premium plan.' );
				break;
			case 'Invalid format.':
				return molms_lt( 'Invalid format.' );
				break;
			case 'Mobile registration failed.':
				return molms_lt( 'Mobile registration failed.' );
				break;
			case 'Invalid mobile authentication request.':
				return molms_lt( 'Invalid mobile authentication request.' );
				break;
			case 'Exception during SMS sending':
				return molms_lt( 'Exception during SMS sending' );
				break;
			case 'There was an error during sending an SMS.':
				return molms_lt( 'There was an error during sending an SMS.' );
				break;
			case 'Exception during logUserTransaction':
				return molms_lt( 'Exception during logUserTransaction' );
				break;
			case 'There was an error processing the challenge user request.':
				return molms_lt( 'There was an error processing the challenge user request.' );
				break;
			case 'What is your first company name?':
				return molms_lt( 'What is your first company name?' );
				break;
			case 'What was your childhood nickname?':
				return molms_lt( 'What was your childhood nickname?' );
				break;
			case 'In what city did you meet your spouse/significant other?':
				return molms_lt( 'In what city did you meet your spouse/significant other?' );
				break;
			case 'What is the name of your favorite childhood friend?':
				return molms_lt( 'What is the name of your favorite childhood friend?' );
				break;
			case "What was your first vehicle's registration number?":
				return molms_lt( "What was your first vehicle's registration number?" );
				break;
			case "What is your grandmother's maiden name?":
				return molms_lt( "What is your grandmother's maiden name?" );
				break;
			case 'Who is your favourite sports player?':
				return molms_lt( 'Who is your favourite sports player?' );
				break;
			case 'What is your favourite sport?':
				return molms_lt( 'What is your favourite sport?' );
				break;
			case 'In what city or town was your first job':
				return molms_lt( 'In what city or town was your first job' );
				break;
			case 'What school did you attend for sixth grade?':
				return molms_lt( 'What school did you attend for sixth grade?' );
				break;
			case 'G_AUTH':
				return molms_lt( 'Google Authenticator' );
				break;
			case 'AUTHY_2FA':
				return molms_lt( 'Authy 2-Factor Authentication' );
				break;
			case 'An unknown error occurred while creating the end user.':
				return molms_lt( 'An unknown error occurred while creating the end user.' );
				break;
			case 'An unknown error occurred while challenging the user':
				return molms_lt( 'An unknown error occurred while challenging the user.' );
				break;
			case 'An unknown error occurred while generating QR Code for registering mobile.':
				return molms_lt( 'An unknown error occurred while generating QR Code for registering mobile.' );
				break;
			case 'An unknown error occurred while validating the user\'s identity.':
				return molms_lt( 'An unknown error occurred while validating the user\'s identity.' );
				break;
			case 'Customer not found.':
				return molms_lt( 'Customer not found.' );
				break;
			case 'The customer is not valid ':
				return molms_lt( 'The customer is not valid' );
				break;
			case 'The user is not valid ':
				return molms_lt( 'The user is not valid ' );
				break;
			case 'Customer already exists.':
				return molms_lt( 'Customer already exists.' );
				break;
			case 'Customer Name is null':
				return molms_lt( 'Customer Name is null' );
				break;
			case 'Customer check request failed.':
				return molms_lt( 'Customer check request failed.' );
				break;
			case 'Invalid username or password. Please try again.':
				return molms_lt( 'Invalid username or password. Please try again.' );
				break;
			case 'You are not authorized to perform this operation.':
				return molms_lt( 'You are not authorized to perform this operation.' );
				break;
			case 'Invalid request. No such challenge request was initiated.':
				return molms_lt( 'Invalid request. No such challenge request was initiated.' );
				break;
			case 'No OTP Token for the given request was found.':
				return molms_lt( 'No OTP Token for the given request was found.' );
				break;
			case 'Query submitted.':
				return molms_lt( 'Query submitted.' );
				break;
			case 'Invalid parameters.':
				return molms_lt( 'Invalid parameters.' );
				break;
			case 'Alternate email cannot be same as primary email.':
				return molms_lt( 'Alternate email cannot be same as primary email.' );
				break;
			case 'CustomerId is null.':
				return molms_lt( 'CustomerId is null.' );
				break;
			case 'You are not authorized to create users. Please upgrade to premium plan. ':
				return molms_lt( 'You are not authorized to create users. Please upgrade to premium plan. ' );
				break;
			case 'Your user creation limit has been completed. Please upgrade your license to add more users.':
				return molms_lt( 'Your user creation limit has been completed. Please upgrade your license to add more users.' );
				break;
			case 'Username cannot be blank.':
				return molms_lt( 'Username cannot be blank.' );
				break;
			case 'End user created successfully.':
				return molms_lt( 'End user created successfully.' );
				break;
			case 'There was an exception processing the update user request.':
				return molms_lt( 'There was an exception processing the update user request.' );
				break;
			case 'End user found.':
				return molms_lt( 'End user found.' );
				break;
			case 'End user found under different customer. ':
				return molms_lt( 'End user found under different customer. ' );
				break;
			case 'End user not found.':
				return molms_lt( 'End user not found.' );
				break;
			case 'Customer successfully registered.':
				return molms_lt( 'Customer successfully registered.' );
				break;
			case 'Customer registration failed.':
				return molms_lt( 'Customer registration failed.' );
				break;
			case 'There was an error processing the register mobile request.':
				return molms_lt( 'There was an error processing the register mobile request.' );
				break;
			case 'There was an exception processing the get user request.':
				return molms_lt( 'There was an exception processing the get user request.' );
				break;
			case 'End User retrieved successfully.':
				return molms_lt( 'End User retrieved successfully.' );
				break;
			case 'COMPLETED_TEST':
				return molms_lt( 'You have successfully completed the test.' );
				break;
			case 'INVALID_EMAIL_VER_REQ':
				return molms_lt( 'Invalid request. test case failed.' );
				break;
			case 'INVALID_ENTRY':
				return molms_lt( 'All the fields are required. Please enter valid entries.' );
				break;
			case 'INVALID_PASSWORD':
				return molms_lt( 'You already have an account with miniOrange. Please enter a valid password.' );
				break;
			case 'INVALID_REQ':
				return molms_lt( 'Invalid request. Please try again' );
				break;
			case 'INVALID_OTP':
				return molms_lt( 'Invalid OTP. Please try again.' );
				break;
			case 'INVALID_EMAIL_OR_PASSWORD':
				return molms_lt( 'Invalid email or password. Please try again.' );
				break;
			case 'PASSWORDS_MISMATCH':
				return molms_lt( 'Password and Confirm password do not match.' );
				break;
			case 'ENTER_YOUR_EMAIL_PASSWORD':
				return molms_lt( 'Please enter your registered email and password.' );
				break;
			case 'OTP_SENT':
				return molms_lt( 'One Time Passcode has been sent for verification to ' );
				break;
			case 'ERROR_IN_SENDING_OTP_OVER_EMAIL':
				return molms_lt( 'There was an error in sending OTP over email. Please click on Resend OTP to try again.' );
				break;
			case 'ERROR_DURING_REGISTRATION':
				return molms_lt( 'Error occured while registration. Please try again.' );
				break;
			case 'ERROR_DURING_PROCESS':
				return molms_lt( 'An error occured while processing your request. Please Try again.' );
				break;
			case 'ERROR_DURING_PROCESS_EMAIL':
				return molms_lt( 'An error occured while processing your request. Please check your SMTP server is configured.' );
				break;
			case 'ERROR_WHILE_SENDING_SMS':
				return molms_lt( 'There was an error in sending sms. Please click on Resend OTP to try again.' );
				break;
			case 'ERROR_DURING_USER_REGISTRATION':
				return molms_lt( 'Error occurred while registering the user. Please try again.' );
				break;
			case 'SET_AS_2ND_FACTOR':
				return molms_lt( 'is set as your 2 factor authentication method.' );
				break;
			case 'ERROR_WHILE_SAVING_KBA':
				return molms_lt( 'Error occured while saving your kba details. Please try again.' );
				break;
			case 'ANSWER_SECURITY_QUESTIONS':
				return molms_lt( 'Please answer the following security questions.' );
				break;
			case 'ERROR_FETCHING_QUESTIONS':
				return molms_lt( 'There was an error fetching security questions. Please try again.' );
				break;
			case 'INVALID_ANSWERS':
				return molms_lt( 'Invalid Answers. Please try again.' );
				break;
			case 'MIN_PASS_LENGTH':
				return molms_lt( 'Choose a password with minimum length 6.' );
				break;
			case 'ACCOUNT_RETRIEVED_SUCCESSFULLY':
				return molms_lt( 'Your account has been retrieved successfully.' );
				break;
			case 'DEFAULT_2ND_FACTOR':
				return molms_lt( 'has been set as your default 2nd factor method' );
				break;
			case 'RESENT_OTP':
				return molms_lt( 'Another One Time Passcode has been sent' );
				break;
			case 'VERIFY':
				return molms_lt( 'for verification to' );
				break;
			case 'ERROR_IN_SENDING_EMAIL':
				return molms_lt( 'There was an error in sending email. Please click on Resend OTP to try again.' );
				break;
			case 'EMAIL_IN_USE':
				return molms_lt( 'The email is already used by other user. Please register with other email.' );
				break;
			case 'EMAIL_MANDATORY':
				return molms_lt( 'Please submit your query with email' );
				break;
			case 'ERROR_WHILE_SUBMITTING_QUERY':
				return molms_lt( 'Your query could not be submitted. Please try again.' );
				break;
			case 'QUERY_SUBMITTED_SUCCESSFULLY':
				return molms_lt( 'Thanks for getting in touch! We shall get back to you shortly.' );
				break;
			case 'SETTINGS_SAVED':
				return molms_lt( 'Your settings are saved successfully.' );
				break;
			case 'AUTHENTICATION_FAILED':
				return molms_lt( 'Authentication failed. Please try again to test the configuration.' );
				break;
			case 'REGISTER_WITH_MO':
				return molms_lt( 'Invalid request. Please register with miniOrange before configuring your mobile.' );
				break;
			case 'ENTER_EMAILID':
				return molms_lt( 'Please enter email-id to register.' );
				break;
			case 'ENTER_VALUE':
				return molms_lt( 'Please enter a value to test your authentication.' );
				break;
			case 'ENTER_OTP':
				return molms_lt( 'Please enter the one time passcode below.' );
				break;
			case 'ERROR_IN_SENDING_OTP':
				return molms_lt( 'There was an error in sending one-time passcode. Your transaction limit might have exceeded. Please contact miniOrange or upgrade to our premium plan.' );
				break;
			case 'ERROR_IN_SENDING_OTP_ONPREM':
				return molms_lt( 'There was an error in sending one-time passcode. Please check your SMTP Setup and remaining transactions.' );
				break;

			case 'PUSH_NOTIFICATION_SENT':
				return molms_lt( 'A Push notification has been sent to your miniOrange Authenticator App.' );
				break;
			case 'ERROR_WHILE_VALIDATING_OTP':
				return molms_lt( 'Error occurred while validating the OTP. Please try again.' );
				break;
			case 'TEST_GAUTH_METHOD':
				return molms_lt( 'to test Google Authenticator method.' );
				break;
			case 'ERROR_IN_SENDING_OTP_CAUSES':
				return molms_lt( 'Error occurred while validating the OTP. Please try again. Possible causes:' );
				break;
			case 'APP_TIME_SYNC':
				return molms_lt( 'Your App Time is not in sync.Go to settings and tap on tap on Sync Time now .' );
				break;
			case 'SERVER_TIME_SYNC':
				return molms_lt( 'Please make sure your System and device have the same time as the displayed Server time.' );
				break;
			case 'ERROR_WHILE_VALIDATING_USER':
				return molms_lt( 'Error occurred while validating the user. Please try again.' );
				break;
			case 'ONLY_DIGITS_ALLOWED':
				return molms_lt( 'Only digits are allowed. Please enter again.' );
				break;
			case 'TEST_AUTHY_2FA':
				return molms_lt( 'to test Authy 2-Factor Authentication method.' );
				break;
			case 'METHOD':
				return molms_lt( 'method.' );
				break;
			case 'TO_TEST':
				return molms_lt( 'to test' );
				break;
			case 'SET_2FA':
				return molms_lt( 'is set as your Two-Factor method.' );
				break;

			case 'SET_2FA_otp':
				return molms_lt( 'is set as your Two-Factor method.' );
				break;


			case 'VERIFICATION_EMAIL_SENT':
				return molms_lt( 'A verification email is sent to' );
				break;
			case 'ACCEPT_LINK_TO_VERIFY_EMAIL':
				return molms_lt( 'Please click on accept link to verify your email.' );
				break;
			case 'ACCOUNT_CREATED':
				return molms_lt( 'Your account has been created successfully.' );
				break;
			case 'ACCOUNT_REMOVED':
				return molms_lt( 'Your account has been removed. Please contact your administrator.' );
				break;
			case 'REGISTRATION_SUCCESS':
				return molms_lt( 'You are registered successfully.' );
				break;
			case 'DENIED_REQUEST':
				return molms_lt( 'You have denied the request.' );
				break;
			case 'DISABLED_2FA':
				return molms_lt( 'Two-Factor plugin has been disabled.' );
				break;
			case 'ERROR_WHILE_SAVING_SETTINGS':
				return molms_lt( 'Error occurred while saving the settings.Please try again.' );
				break;
			case 'INVALID_REQUEST':
				return molms_lt( 'Invalid request. Please register with miniOrange and configure 2-Factor to save your login settings.' );
				break;
			case 'ACCOUNT_ALREADY_EXISTS':
				return molms_lt( 'You already have an account with miniOrange, please sign in.' );
				break;
			case 'CONFIGURE_2FA':
				return molms_lt( 'to configure another 2 Factor authentication method.' );
				break;
			case 'PHONE_NOT_CONFIGURED':
				return molms_lt( 'Your phone number is not configured. Please configure it before selecting OTP Over SMS as your 2-factor method.' );
				break;
			case 'CLICK_HERE':
				return molms_lt( 'Click Here' );
				break;
			case 'ERROR_CREATE_ACC_OTP':
				return molms_lt( 'An error occured while creating your account. Please try again by sending OTP again.' );
				break;
			case 'LOGIN_WITH_2ND_FACTOR':
				return molms_lt( 'Please disable 2FA prompt on WP login page to enable Login with 2nd facor only.' );
				break;
			case 'USER_LIMIT_EXCEEDED':
				return molms_lt( 'Your limit of 3 users has exceeded. Please upgrade to premium plans for more users.' );
				break;
			default:
				return $text;
		}
	}
}

new molms_2fConstants;
