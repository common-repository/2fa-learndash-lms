<div class="mo_lms_tab" style="margin-top: -1%;width: 98%;">

    <button class="tablinks" onclick="openTab2fa(this)" id="setup_2fa">Setup Two Factor</button>
	<?php
	if ( current_user_can( 'administrator' ) ) {
		?>
        <button class="tablinks" onclick="openTab2fa(this)" id="unlimittedUser_2fa">Settings</button>
        <button class="tablinks" onclick="openTab2fa(this)" id="session_control">Session Control</button>
        <button class="tablinks" onclick="openTab2fa(this)" id="login_option_2fa">Premium Features</button>

		<?php
	}
	?>

</div>
<div id="mo_scan_message" style=" padding-top:8px"></div>
<div class="mo_lms_divided_layout" id="setup_2fa_div">
	<?php require_once $molms_dirName . 'controllers' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'setup_twofa.php'; ?>
</div>
<div class="mo_lms_divided_layout" id="rba_2fa_div">
	<?php
	if ( get_site_option( 'mo2f_rba_installed' ) ) {
		molms_rba_description( $mo2f_user_email );
	} else {
		include_once $molms_dirName . 'controllers' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_rba.php';
	}
	?>
	<?php
	if ( get_site_option( 'mo2f_personalization_installed' ) ) {
		molms_personalization_description( $mo2f_user_email );
	} else {
		include_once $molms_dirName . 'controllers' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_custom_login.php';
	}
	?>
	<?php
	if ( get_site_option( 'mo2f_shortcode_installed' ) ) {
		molms_shortcode_description( $mo2f_user_email );
	} else {
		include_once $molms_dirName . 'controllers' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_shortcode.php';
	}
	?>
</div>
<div class="mo_lms_divided_layout" id="custom_login_2fa_div">
	<?php
	if ( get_site_option( 'mo2f_personalization_installed' ) && false ) {
		molms_personalization_description( $mo2f_user_email );
	} else {
		include_once $molms_dirName . 'controllers' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_premium_feature.php';
	}
	?>
</div>
<div class="mo_lms_divided_layout" id="login_option_2fa_div">
	<?php require_once $molms_dirName . 'controllers' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_login_option.php'; ?>
</div>
<div class="mo_lms_divided_layout" id="unlimittedUser_2fa_div">
	<?php require_once $molms_dirName . 'controllers' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_unlimittedUser.php'; ?>
</div>
<div class="mo_lms_divided_layout" id="session_control_div">
	<?php require_once $molms_dirName . 'controllers' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_session_control.php'; ?>
</div>


<script>
    show_customizations_prem();
    jQuery("#setup_2fa_div").css("display", "block");

    jQuery("#rba_2fa_div").css("display", "none");
    jQuery("#custom_login_2fa_div").css("display", "none");
    jQuery("#login_option_2fa_div").css("display", "none");
    jQuery("#custom_form_2fa_div").css("display", "none");

    jQuery("#setup_2fa").addClass("active");

    function openTab2fa(elmt) {
        var tabname = elmt.id;
        var tabarray = ["setup_2fa", "rba_2fa", "custom_login_2fa", "login_option_2fa", "custom_form_2fa", "unlimittedUser_2fa", "session_control"];
        for (var i = 0; i < tabarray.length; i++) {
            if (tabarray[i] == tabname) {
                jQuery("#" + tabarray[i]).addClass("active");
                jQuery("#" + tabarray[i] + "_div").css("display", "block");
            } else {
                jQuery("#" + tabarray[i]).removeClass("active");
                jQuery("#" + tabarray[i] + "_div").css("display", "none");
            }
        }
        localStorage.setItem("last_tab", tabname);
    }

    var tour = '<?php echo esc_html( molms_Utility::get_mo2f_db_option( 'mo2f_two_factor_tour', 'get_site_option' ) );?>';

    if (tour != 1)
        var tab = localStorage.getItem("last_tab");
    else
        var tab = '<?php echo esc_html( get_site_option( "mo2f_tour_tab" ) );?>';
    var is_onprem = '<?php echo esc_html( MOLMS_IS_ONPREM );?>';
    if (tab == "setup_twofa") {
        document.getElementById("setup_2fa").click();
    } else if (tab == "rba_2fa") {
        document.getElementById("rba_2fa").click();
    } else if (tab == "custom_login_2fa") {
        document.getElementById("custom_login_2fa").click();
    } else if (tab == "login_option_2fa") {
        document.getElementById("login_option_2fa").click();
    } else if (tab == "custom_form_2fa") {
        document.getElementById("custom_form_2fa").click();
    } else if (tab == "unlimittedUser_2fa") {
        document.getElementById("unlimittedUser_2fa").click();
    } else if (tab == "session_control") {
        document.getElementById("session_control").click();
    } else {
        document.getElementById("setup_2fa").click();
    }
</script>
