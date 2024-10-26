=== 2FA LearnDash LMS WordPress ===

Contributors: twofactorauthentication, cyberlord92
Tags: 2FA, concurrent logout, auto logout, prevent account sharing, inactive user, idle logout, idle user, autologout, multiple sessions, concurrent login, LearnDash, wordpress 2fa, LMS 2fa
Donate link: https://miniorange.com/
Requires at least: 3.0.1
Tested up to: 6.1
Requires PHP: 5.3.0
Stable tag: 1.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Prevent account sharing, handle concurrent/ Idle or inactive sessions, and set session time using WordPress 2FA LearnDash LMS plugin. Supports various LMS like Learndash, LifterLMS, Masterstudy LMS, LearnPress LMS, Namaste! LMS, Membership, Good LMS, Sensei LMS, Teachable LMS.

== Description ==

LMS paid e-learning courses can be prevented from account sharing using the 2FA LearnDash LMS plugin. This 2FA learndash LMS plugin is free, easy to set up, and compatible with other LMS like LearnPress LMS, LifterLMS, Membership, Masterstudy LMS, LearnPress LMS, Namaste! LMS, Membership, Good LMS, Sensei LMS, Teachable LMS. 2FA LearnDash LMS also supports session control, limit sessions, and idle or inactive session logout feature.

= [2FA LearnDash LMS WordPress] FREE Plugin Features = 
* **[Prevent account sharing](https://security.miniorange.com/restricting-users-from-sharing-their-login-credentials/):** 2FA LMS Learndash plugin restricts users from sharing WordPress login credentials which help to secure WordPress Websites. The plugin also adds a session control feature that limits user sessions based on WordPress User activities.
* [Limits number of sessions](https://plugins.miniorange.com/lms-2-factor-authentication-for-wordpress#step1)
* [Logout inactive users](https://plugins.miniorange.com/lms-2-factor-authentication-for-wordpress#step2)
* [Set session time](https://plugins.miniorange.com/lms-2-factor-authentication-for-wordpress#step3)
* Simplified & easy user interface to set up methods
* QR Code authentication, Push Notification, Soft Token and Security Questions(KBA) are supported in the plugin for multi-factor authentication(WP 2FA/TFA)

= [2FA LearnDash LMS WordPress] Premium Plugin Features = 
* All free plugin features
* Disable concurrent login
* Message Content after Idle or inactive logout
* Role based timeout
* Custom URL redirect after inactive session timeout
* Auto browser close logout
* Override Multiple Login priority
* Includes Language Translation Support

You can check out other 2fa premium plans along with the features here: [miniOrange paid 2fa plans](https://plugins.miniorange.com/2-factor-authentication-for-wordpress)

= Why do you need to register for 2FA for LMS? =

Our plugin uses miniOrange APIs to communicate between your WP and miniOrange. To keep this communication secure, we ask you to register and assign API keys specific to your account. This way your account and users' calls can be only accessed by API keys assigned to you.
Adding to this, you can also use the same account on multiple applications and your users do not have to maintain multiple accounts  if you are using our cloud solution. Single code generated in Authenticator App or QR Code Authentication will be enough to log in to all sites. With this, you can also achieve sync of **two-factor authentication on multiple sites**.

Customized solutions and Active support are available. Email us at info@xecurify.com or call us at +1 9786589387.

**Note: The plugin is GDPR Compliant and supports a wide variety of Language Translations**

== Installation ==

= From your WordPress dashboard =
1. Navigate to `Plugins > Add New` from your WP Admin dashboard.
2. Search for `2FA for LMS`.
3. Install `2FA for LMS` and Activate the plugin.

= From WordPress.org =
1. Search for `2FA for LMS` and download it.
2. Unzip and upload the `2fa-learndash-lms` directory to your `/wp-content/plugins/` directory.
3. Activate 2FA for LMS from the Plugins tab of your admin dashboard.

= Once Activated =
1. Select miniOrange 2-Factor from the left menu and follow the instructions.
2. Once, you complete your setup. Click on Log Out button.
3. Enter the username and password. After the initial validation, you will be prompted for the 2-factor method you had set up.
4. Validate yourself with the 2-factor authentication method you configured.

<b>Video Guide</b>	:<br>
<iframe width="560" height="315" src="https://www.youtube.com/embed/vVGXjedIaGs" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>


== Frequently Asked Questions ==

= How do I gain access to my website if I get locked out? =

You can obtain access to your website by one of the below options:

1. If you have an additional administrator account whose Two Factor is not enabled yet, you can login with it.
2. If you had setup KBA questions earlier, you can use them as an alternate method to login to your website.
3. Rename the plugin from FTP - this disables the Two-Factor (2FA) plugin and you will be able to login with your WordPress username and password.

For detailed information, Please check on our website. <a href="https://faq.miniorange.com/knowledgebase/gain-access-to-website-if-locked-out/" target="_blank">Locked Out</a>.<br>
You can also check our video Tutorial:
<iframe width="560" height="315" src="https://www.youtube.com/embed/wLFKakQkpk8" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

= I want to enable Two-Factor Authentication (2FA) role-wise? =

You can select the roles under Login Settings tab to enable the plugin role-wise.	[PREMIUM FEATURE]

= I have enabled Two-Factor Authentication (2FA) for all users, what happens if an end-user tries to login but has not yet registered? =

If a user has not setup Two-Factor yet, a user has to register by inline registration that will be invoked during the login.

= I want to enable only one authentication method for my users. What should I do? =

You can select the authentication methods under Login Settings tab. The selected authentication methods will be shown to the user during inline registration. [PREMIUM FEATURE]

= I forgot the password of my miniOrange account. How can I reset it? =

There are two cases according to the page you see -<br>
 1. Login with miniOrange screen: You should click on forgot password link. You will get a new password on your email address with which you have registered with miniOrange. Now you can log in with the new password.
 2. Register with miniOrange screen: Enter your email ID and any random password in password field and confirm password input box. This will redirect you to log in with miniOrange screen. Now follow first step.

= My users have different types of phones. What phones are supported? =

We support all types of phones. Smart Phones, Basic Phones, Landlines, etc. Go to Setup Two-Factor Tab and select Two-Factor method of your choice from a range of 8 different options.

= What if a user does not have a smartphone? =

You can select OTP over SMS, Phone Call Verification, or Email Verification as your Two-Factor method. All these methods are supported on basic phones.

= What if a user does not have any phone? =

You can select Email Verification or Security Questions (KBA) as your Two-Factor method.

= What if I am trying to log in from my phone? =

If your Security Questions (KBA) are configured then you will be asked to answer them when you are logging in from your phone.

= My phone has no internet connectivity and configured 2nd factor with miniOrange App, how can I log in? =

You can log in using our alternate login method. Please follow below steps to login:

* Enter your username and click on login with your phone.
* Click on <b>Phone is Offline?</b> button below QR Code.
* You will see a textbox to enter one-time passcode.
* Open miniOrange Authenticator App and Go to Soft Token Tab.
* Enter the one time passcode shown in miniOrange Authenticator App in textbox, just like Google authenticator.
* Click on submit button to validate the OTP.
* Once you are authenticated, you will be logged in.

= My phone is lost, stolen or discharged. How can I login? =

You can login using our alternate login method. Click on the Forgot Phone link and you will get 2 alternate methods to login. Select "Send a one time passcode to my registered email" to authenticate by OTP over EMAIL or Select "Answer your Security Questions (KBA)" to authenticate by knowledge-based authentication.

= I am upgrading my phone. =

You should go to <b>Setup Two Factor</b> Tab and click on <b>Reconfigure</b> to reconfigure 2-Factor with your new phone.

== Screenshots ==

1. 2FA LearnDash LMS: Setup different 2-Factor methods for users.
2. 2FA LearnDash LMS: Session control.

== Changelog ==

= 1.0.5 =
2FA LearnDash LMS : Updated code according to WordPress guidelines

= 1.0.4 =
2FA LearnDash LMS : Introduced new Premium Plan

= 1.0.3 =
2FA LearnDash LMS : Updated support subject and Email

= 1.0.2 =
2FA LearnDash LMS: Option for the trial of Premium. Also updated the pricing plans

= 1.0.1 =
2FA LearnDash LMS : Fixed plugin deletion issue.

= 1.0.0 =
First version of 2FA LearnDash LMS plugin.

== Upgrade Notice == 

= 1.0.5 =
2FA LearnDash LMS : Updated code according to WordPress guidelines

= 1.0.4 =
2FA LearnDash LMS : Introduced new Premium Plan

= 1.0.3 =
2FA LearnDash LMS : Updated support subject and Email

= 1.0.2 =
2FA LearnDash LMS: Option for the trial of Premium. Also updated the pricing plans

= 1.0.1 =
2FA LearnDash LMS : Fixed plugin deletion issue.

= 1.0.0 =
First version of 2FA LearnDash LMS plugin.