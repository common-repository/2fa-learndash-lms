jQuery(document).ready(
    function () {
    
        $ = jQuery;

        //Instructions
        $("#mo_lms_help_curl_title").click(
            function () {
                $("#mo_lms_help_curl_desc").slideToggle(600);
            }
        );
    
        $("#mo_lms_issue_in_scanning_QR").click(
            function () {
                $("#mo_lms_issue_in_scanning_QR_solution").slideToggle(600);
            }
        );
    
        $("#mo_lms_help_get_back_to_account").click(
            function () {
                $("#mo_lms_help_get_back_to_account_solution").slideToggle(600);
            }
        );
    
        $("#mo_lms_help_multisite").click(
            function () {
                $("#mo_lms_help_multisite_solution").slideToggle(600);
            }
        );
        $("#mo_lms_help_forgot_password").click(
            function () {
                $("#mo_lms_help_forgot_password_solution").slideToggle(600);
            }
        );
        $("#mo_lms_help_MFA_propmted").click(
            function () {
                $("#mo_lms_help_MFA_propmted_solution").slideToggle(600);
            }
        );
    
        $("#mo_lms_help_redirect_back").click(
            function () {
                $("#mo_lms_help_redirect_back_solution").slideToggle(600);
            }
        );
        $("#mo_lms_help_alternet_login").click(
            function () {
                $("#mo_lms_help_alternet_login_solution").slideToggle(600);
            }
        );
        $("#mo_lms_help_lost_ability").click(
            function () {
                $("#mo_lms_help_lost_ability_solution").slideToggle(600);
            }
        );
        $("#mo_lms_help_translate").click(
            function () {
                $("#mo_lms_help_translate_solution").slideToggle(600);
            }
        );
        $("#mo_lms_help_particular_use_role").click(
            function () {
                $("#mo_lms_help_particular_use_role_solution").slideToggle(600);
            }
        );
        $("#mo_lms_help_enforce_MFA").click(
            function () {
                $("#mo_lms_help_enforce_MFA_solution").slideToggle(600);
            }
        );
        $("#mo_lms_help_reset_MFA").click(
            function () {
                $("#mo_lms_help_reset_MFA_solution").slideToggle(600);
            }
        );

        $(".lms_premium_option :input").attr("disabled",true);

    }
);


function ajaxCall(option,element,hide)
{
    jQuery.ajax(
        {
            url: "",
            type: "GET",
            data: "option="+option,
            crossDomain: !0,
            dataType: "json",
            contentType: "application/json; charset=utf-8",
            success: function (o) {
                if (hide!=undefined) {
                    jQuery(element).slideUp();
                }
            },
            error: function (o, e, n) {}
        }
    );
}

function success_msg(msg)
{ 
    jQuery('#lms_nav_message').empty();
    jQuery('#lms_nav_message').append("<div id='notice_div' class='overlay_success'><div class='popup_text'>&nbsp&nbsp"+msg+"</div></div>");
    window.afterload =  nav_popup();
}

function error_msg(msg)
{
    jQuery('#lms_nav_message').empty();
    jQuery('#lms_nav_message').append("<div id='notice_div' class='overlay_error'><div class='popup_text'>&nbsp&nbsp"+msg+"</div></div>");
    window.afterload =  nav_popup();
}

function nav_popup()
{
    document.getElementById("notice_div").style.width = "40%";
    setTimeout(
        function () {
            jQuery('#notice_div').fadeOut('slow'); }, 3000
    );
}