<?php
echo '
<br><br>
<a class="molms_back_button" style="font-size: 16px; color: #000;" href="' . esc_url( $Back_button ) . '"><span class="dashicons dashicons-arrow-left-alt" style="vertical-align: bottom;"></span> Back To Plugin Configuration</a>';
?>
<br><br>

<?php
global $lms_mainDir;
?>

<br><br>
<link rel="stylesheet"
      href=<?php echo esc_url( $lms_mainDir . 'includes/css/upgrade.css' ); ?>>

<div class="mo2f_upgrade_super_div" id="mo2f_twofa_plans">
    <div class="mo2f_upgrade_main_div">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css"
              integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/"
              crossorigin="anonymous"/>
        <div id="mofa_pricing_tabs_mo" class="mo2fa_pricing_tabs_mo mo2fa_pricing_tabs_mo_enterprise">
            <div class="mo2fa_pricing_head_sky">
                <div id="mo2fa_pricing_head" class="mo2fa_pricing_head_supporter">
                    <center>
                        <h3 class="mo2fa_pricing_head_mo_2fa">Unlimited Sites<br>
                            <div class="mo2fa_web_sec">Website Security</div>
                        </h3>
                    </center>
                </div>
                <br><br>
                <div id="mo2fa_pricing_head_cost" class="mo2fa_pricing_head_supporter">
                    <div class="mo2fa_dollar">
                        <center><span>$</span><span id="mo2fa_pricing_adder">128</span><span class="mo2fa_per_year">/Year</span>
                        </center>
                    </div>

                </div>
            </div>
            <h3 class="mo2fa_plan-type"><b>ENTERPRISE</b></h3>

            <div id="mo2fa_pricing_addons" class="mo2fa_pricing">
                <center>
                    <div id="mo2fa_purchase_user_limit">

                        <center>
                            <select id="mo2fa_user_price" onclick="mo2fa_update_user_limit()"
                                    onchange="mo2fa_update_user_limit()" class="mo2fa_increase_my_limit">
                                <option value="59">5 USERS</option>
                                <option value="128" selected>50 USERS</option>
                                <option value="228">100 USERS</option>
                                <option value="378">500 USERS</option>
                                <option value="528">1K USERS</option>
                                <option value="828">5K USERS</option>
                                <option value="1028">10K USERS</option>
                                <option value="1528">20K USERS</option>

                            </select>
                        </center>
                    </div>


                    <div id="details">
                        <center>
							<?php if ( isset( $is_customer_registered ) && $is_customer_registered ) { ?>
                                <a onclick="molms_upgradeform('wp_2fa_enterprise_plan','2fa_plan')" target="blank">
                                    <button class="mo2fa_upgrade_my_plan_ent">Upgrade</button>
                                </a>
							<?php } else { ?>
                                <a onclick="molms_register_and_upgradeform('wp_2fa_enterprise_plan','2fa_plan')"
                                   target="blank">
                                    <button class="mo2fa_upgrade_my_plan_ent">Upgrade</button>
                                </a>
							<?php } ?>
                        </center>
                    </div>
                </center>
            </div>


            <div id="mo2fa_pricing_feature_collection_supporter" class="mo2fa_pricing_feature_collection_supporter">


                <div id="mo2fa_pricing_feature_collection" class="mo2fa_pricing_feature_collection">
                    <ul class="mo2fa_ul">
                        <center><p class="mo2fa_feature"><strong>Features</strong></p></center>
                        <li class="mo2fa_feature_collect_mo-2fa mo2fa_unltimate_feature">User Session Control</li>
                        <li class="mo2fa_feature_collect_mo-2fa mo2fa_unltimate_feature">Idle Session</li>
                        <li class="mo2fa_feature_collect_mo-2fa mo2fa_unltimate_feature">Set Session Time</li>
                        <li class="mo2fa_feature_collect_mo-2fa mo2fa_unltimate_feature"><span
                                    class="mo2fa_cloud_per_tooltip_methodlist">Cloud <i class="fa fa-info-circle fa-xs"
                                                                                        aria-hidden="true"></i><span
                                        class="mo2fa_methodlist"
                                        t>Users data is stored on the miniOrange Cloud</span></span></li>
                        <li class="mo2fa_feature_collect_mo-2fa mo2fa_unltimate_feature">Multisite Support</li>
                        <li class="mo2fa_feature_collect_mo-2fa mo2fa_unltimate_feature">Email Transactions Extra</li>

                        <li class="mo2fa_ent_feature_collect_mo-2fa mo2fa_unltimate_feature"><span
                                    class="mo2fa_ent_tooltip_methodlist">15+ Authentication Methods <i
                                        class="fa fa-info-circle fa-xs" aria-hidden="true"></i>
		<span class="mo2fa_methodlist" t>
		<ul class="methods-list-mo2fa" style="margin-left: -43px;">
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">Google Authenticator</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">OTP Over SMS</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">OTP Over Email</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">Email Verification</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">OTP Over SMS and Email</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">Security Questions</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">Authy Authenticator</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">Microsoft Authenticator</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">LastPass Authenticator</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">FreeOTP Authenticator</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">Duo Mobile Authenticator</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">miniOrange QR Code Authentication</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">miniOrange Soft Token</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">miniOrange Push Notification</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-size">Hardware Token</li>
		<li class="feature_collect_mo-2fa mo2fa_method-list-mo-size-cross">OTP Over Whatsapp</li>
		</ul>
		</span>
		</span></li>
                        <li class="mo2fa_feature_collect_mo-2fa mo2fa_unltimate_feature"><span
                                    class="mo2fa_ent_enforce_2fa_tooltip_methodlist">Enforce 2FA Set-up For Users <i
                                        class="fa fa-info-circle fa-xs" aria-hidden="true"></i><span
                                        class="mo2fa_methodlist" t>Enforce users to set their 2FA after installing the plugin</span></span>
                        </li>
                        <li class="mo2fa_feature_collect_mo-2fa mo2fa_unltimate_feature"> 3+ Backup Login Methods</li>
                        <li class="mo2fa_feature_collect_mo-2fa mo2fa_unltimate_feature"><span
                                    class="mo2fa_ent_redirect_tooltip_methodlist">Custom Redirection URL <i
                                        class="fa fa-info-circle fa-xs" aria-hidden="true"></i><span
                                        class="mo2fa_methodlist" t>Redirects users to the specific Url after login(can be configured according to user role)</span></span>
                        </li>
                        <li class="mo2fa_feature_collect_mo-2fa mo2fa_unltimate_feature"><span
                                    class="mo2fa_ent_role_tooltip_methodlist">Role-Based 2FA <i
                                        class="fa fa-info-circle fa-xs" aria-hidden="true"></i><span
                                        class="mo2fa_methodlist"
                                        t>You can enable 2FA for specific user role</span></span></li>
                        <li class="mo2fa_feature_collect_mo-2fa mo2fa_unltimate_feature"><span
                                    class="mo2fa_ent_rba_tooltip_methodlist">Remember Device <i
                                        class="fa fa-info-circle fa-xs" aria-hidden="true"></i><span
                                        class="mo2fa_methodlist" t> It give users the option to rememeber device.Skip 2FA for trusted device</span></span>
                        </li>
                        <li class="mo2fa_feature_collect_mo-2fa mo2fa_unltimate_feature">Passwordless login</li>
                        <li class="mo2fa_feature_collect_mo-2fa mo2fa_unltimate_feature"><span
                                    class="mo2fa_ent_custom_sms_tooltip_methodlist">Custom SMS Gateway <i
                                        class="fa fa-info-circle fa-xs" aria-hidden="true"></i><span
                                        class="mo2fa_methodlist" t>
		You can integrate your own SMS gateway with miniOrange</span><span></li>
                        <li class="mo2fa_feature_collect_mo-2fa mo2fa_unltimate_feature"><span
                                    class="mo2fa_shortcode_ent_tooltip_methodlist">Shortcode Addon <i
                                        class="fa fa-info-circle fa-xs" aria-hidden="true"></i> <span
                                        class="mo2fa_methodlist" t>
		1. 2FA Shortcode - Use to add 2FA on any page.<br>
		2. Reconfigure 2FA Addon - Addon to reconfiigure 2FA.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <script>
            var base_price = 0;
            var display_me = parseInt(base_price) + parseInt(30) + parseInt(0) + parseInt(0);
            document.getElementById("mo2fa_pricing_adder").innerHTML = +display_me;
            jQuery('#mo2fa_user_price').click();

            function mo2fa_update_user_limit() {
                var users = document.getElementById("mo2fa_user_price").value;

                var users_addion = parseInt(base_price) + parseInt(users);

                document.getElementById("mo2fa_pricing_adder").innerHTML = +users_addion;

            }


        </script>

    </div>
</div>

<div class="mo_lms_setting_layout molms_setting_layout">
    <div>
        <h2><?php echo molms_lt( 'Steps to upgrade to the Premium Plan :' ); ?></h2>
        <ol class="molms_licensing_plans_ol">
            <li><?php echo molms_lt( 'Click on ' ); ?>
                <b><?php echo molms_lt( 'Proceed' ); ?></b>/<b><?php echo molms_lt( 'Upgrade' ); ?></b><?php echo molms_lt( ' button of your preferred plan above.' ); ?>
            </li>
            <li><?php echo molms_lt( ' You will be redirected to the miniOrange Console. Enter your miniOrange username and password, after which you will be redirected to the payment page.' ); ?></li>

            <li><?php echo molms_lt( 'Select the number of users/sites you wish to upgrade for, and any add-ons if you wish to purchase, and make the payment.' ); ?></li>
            <li><?php echo molms_lt( 'After making the payment, you can find the Enterprise plugin to download from the ' ); ?>
                <b><?php echo molms_lt( 'License' ); ?></b><?php echo molms_lt( ' tab in the left navigation bar of the miniOrange Console.' ); ?>
            </li>
            <li><?php echo molms_lt( 'Download the paid plugin from the ' ); ?>
                <b><?php echo molms_lt( 'Releases and Downloads' ); ?></b><?php echo molms_lt( ' tab through miniOrange Console .' ); ?>
            </li>
            <li><?php echo molms_lt( 'Deactivate and delete the free plugin from ' ); ?>
                <b><?php echo molms_lt( 'WordPress dashboard' ); ?></b><?php echo molms_lt( ' and install the paid plugin downloaded.' ); ?>
            </li>
            <li><?php echo molms_lt( 'Login to the paid plugin with the miniOrange account you used to make the payment, after this your users will be able to set up 2FA.' ); ?></li>
        </ol>
    </div>
    <hr>
    <div>
        <h2><?php echo molms_lt( 'Note :' ); ?></h2>
		<?php echo molms_lt( 'The plugin works with many of the default custom login forms (like Woocommerce / Theme My Login / Login With Ajax / User Pro / Elementor), however if you face any issues with your custom login form, contact us and we will help you with it.' ); ?></li>
    </div>
    <hr>
    <br>
    <div>
        <b class="molms_note"><?php echo molms_lt( 'Refund Policy : ' ); ?></b><?php echo molms_lt( 'At miniOrange, we want to ensure you are 100% happy with your purchase. If the premium plugin you purchased is not working as advertised and you\'ve attempted to resolve any issues with our support team, which couldn\'t get resolved then we will refund the whole amount within 10 days of the purchase. ' ); ?>
    </div>
    <br>
    <hr>
    <br>
    <div>
        <b class="molms_note"><?php echo molms_lt( 'SMS Charges : ' ); ?></b><?php echo molms_lt( 'If you wish to choose OTP Over SMS / OTP Over SMS and Email as your authentication method,
		SMS transaction prices & SMS delivery charges apply and they depend on country. SMS validity is for lifetime.' ); ?>
    </div>
    <br>
    <hr>
    <br>
    <div>
        <b class="molms_note"><?php echo molms_lt( 'Privacy Policy : ' ); ?></b><a
                href="https://www.miniorange.com/2-factor-authentication-for-wordpress-gdpr"><?php echo molms_lt( 'Click Here' ); ?></a><?php echo molms_lt( ' to read our Privacy Policy.' ); ?>
    </div>
    <br>
    <hr>
    <br>
    <div>
        <b class="molms_note"><?php echo molms_lt( 'Contact Us : ' ); ?></b><?php echo molms_lt( 'If you have any doubts regarding the licensing plans, you can mail us at ' ); ?>
        <a href="mailto:2fasupport@xecurify.com"><i><?php echo molms_lt( '2fasupport@xecurify.com' ); ?></i></a><?php echo molms_lt( ' or submit a query using the support form.' ); ?>
    </div>
</div>
</center>
<div id="molms_payment_option" class="mo_lms_setting_layout molms_setting_layout">
    <div>
        <h3>Supported Payment Methods</h3>
        <hr>
        <div class="mo_2fa_container">
            <div class="mo_2fa_card-deck">
                <div class="mo_2fa_card mo_2fa_animation">
                    <div class="mo_2fa_Card-header">
						<?php
						echo '<img src="' . dirname( plugin_dir_url( __FILE__ ) ) . '/includes/images/card.png" class="molms_card">'; ?>
                    </div>
                    <hr class="molms_hr">
                    <div class="mo_2fa_card-body">
                        <p class="molms_payment_p">If payment is done through Credit Card/Intenational debit card, the
                            license would be created automatically once payment is completed. </p>
                        <p class="molms_payment_p"><i><b>For guide
									<?php echo '<a href=' . esc_url( molms_Constants::FAQ_PAYMENT_URL ) . ' target="blank">Click Here.</a>'; ?></b></i>
                        </p>

                    </div>
                </div>
                <div class="mo_2fa_card mo_2fa_animation">
                    <div class="mo_2fa_Card-header">
						<?php
						echo '<img src="' . dirname( plugin_dir_url( __FILE__ ) ) . '/includes/images/paypal.png" class="molms_card">'; ?>
                    </div>
                    <hr class="molms_hr">
                    <div class="mo_2fa_card-body">
						<?php echo '<p class="molms_payment_p">Use the following PayPal id for payment via PayPal.</p><p><i><b style="color:#1261d8"><a href="mailto:' . esc_attr( molms_Constants::SUPPORT_EMAIL ) . '">2fasupport@xecurify.com</a></b></i>'; ?>

                    </div>
                </div>
                <div class="mo_2fa_card mo_2fa_animation">
                    <div class="mo_2fa_Card-header">
						<?php
						echo '<img src="' . dirname( plugin_dir_url( __FILE__ ) ) . '/includes/images/bank-transfer.png" class="molms_card molms_bank_transfer">'; ?>

                    </div>
                    <hr class="molms_hr">
                    <div class="mo_2fa_card-body">
						<?php echo '<p class="molms_payment_p">If you want to use Bank Transfer for payment then contact us at <i><b style="color:#1261d8"><a href="mailto:' . esc_attr( molms_Constants::SUPPORT_EMAIL ) . '">2fasupport@xecurify.com</a></b></i> so that we can provide you bank details. </i></p>'; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="mo_2fa_mo-supportnote">
            <p class="molms_payment_p"><b>Note :</b> Once you have paid through PayPal/Bank Transfer, please inform us
                at <i><b style="color:#1261d8"><a href="mailto:'.esc_attr(molms_Constants::SUPPORT_EMAIL).'">2fasupport@xecurify.com</a></b></i>,
                so that we can confirm and update your License.</p>
        </div>
    </div>
</div>

<form class="molms_display_none_forms" id="molms_loginform"
      action="<?php echo esc_url( MOLMS_HOST_NAME ) . '/moas/login'; ?>"
      target="_blank" method="post">
    <input type="email" name="username" value="<?php echo esc_attr( get_option( 'mo2f_email' ) ); ?>"/>
    <input type="text" name="redirectUrl"
           value="<?php echo esc_url( MOLMS_HOST_NAME ) . '/moas/initializepayment'; ?>"/>
    <input type="text" name="requestOrigin" id="requestOrigin"/>
</form>

<form class="molms_display_none_forms" id="molms_register_to_upgrade_form"
      method="post">
    <input type="hidden" name="requestOrigin"/>
    <input type="hidden" name="molms_register_to_upgrade_nonce"
           value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-lms-user-reg-to-upgrade-nonce' ) ); ?>"/>
</form>
<script type="text/javascript">
    function molms_upgradeform(planType, planname) {
        jQuery('#requestOrigin').val(planType);
        jQuery('#molms_loginform').submit();
        var data = {
            'action': 'molms_two_factor_ajax',
            'mo_2f_two_factor_ajax': 'molms_update_plan',
            'nonce': '<?php echo esc_js( wp_create_nonce( 'molms-upgradeform-nonce' ) );?>',
            'planname': planname,
            'planType': planType,
        }
        jQuery.post(ajaxurl, data, function (response) {
        });
    }

    function molms_register_and_upgradeform(planType, planname) {
        jQuery('#requestOrigin').val(planType);
        jQuery('input[name="requestOrigin"]').val(planType);
        jQuery('#molms_register_to_upgrade_form').submit();
    }

</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
