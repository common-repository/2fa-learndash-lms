jQuery(document).ready(
    function () {
        let $mo = jQuery;

        let ajaxurl             = otpverificationObj.siteURL;
        let nonce               = otpverificationObj.nonce;
        let submitSelector      = otpverificationObj.submitSelector;
        let formName            = otpverificationObj.formname;
        let emailSelector       = otpverificationObj.emailselector;
        let phoneSelector       = otpverificationObj.phoneSelector;
        let loaderUrl           = otpverificationObj.loaderUrl;
        let isValidated         = false
        let isEmailValidated    = false
        let isPhoneValidated    = false
        let email_txId          = ""
        let sms_txId            = ""
        let validated           = false
        let isBoth              = false
        let authType            = otpverificationObj.authType;
        let isShortEnabled      = otpverificationObj.isEnabledShortcode;
        let formAction          = jQuery(formName).attr('action');
        let formMethod          = jQuery(formName).attr('method');
        let submitSelectorName  = jQuery(submitSelector).attr('name');
        let submitSelectorId    = jQuery(submitSelector).attr('id')
        let submitSelectorClass = jQuery(submitSelector).attr('class');

        const otp_over_email = '<label for="mo2f_reg_email">OTP Over Email&nbsp;<span class="required">*</span></label> <input type="text" name="mo2f_email_otp" id="mo2f_email_otp" placeholder="Enter OTP sent on email" />';
        const otp_over_sms   = '<label for="mo2f_reg_sms">OTP Over SMS&nbsp;<span class="required">*</span></label> <input type="text" name="mo2f_phone_otp" id="mo2f_phone_otp" placeholder="Enter OTP sent on phone number" />';
        authType             = 'email';

        switch (authType)
        {
        case  'phone':
            jQuery(phoneSelector).wrap("<div class='buttonInsidePhone'></div>");
            jQuery(phoneSelector).after('<button class="mo2f_send_phoneotp_button" id="mo2f_send_ajax_sms">Send OTP</button> ');
            mo2f_ajax_before_reg();
            break;

        case  'email':
            jQuery(emailSelector).wrap("<div class='buttonInsideEmail'></div>");
            jQuery(emailSelector).after('<button class="mo2f_send_emailotp_button" id="mo2f_send_ajax_email">Send OTP</button>');
            mo2f_ajax_before_reg();
            break;

        case  'both':
            isBoth = true;
            jQuery(phoneSelector).wrap("<div class='buttonInsidePhone'></div>");
            jQuery(phoneSelector).after('<button class="mo2f_send_phoneotp_button" id="mo2f_send_ajax_sms">Send OTP</button> ');
            jQuery(emailSelector).wrap("<div class='buttonInsideEmail'></div>");
            jQuery(emailSelector).after('<button class="mo2f_send_emailotp_button" id="mo2f_send_ajax_email">Send OTP</button>');
            mo2f_ajax_before_reg();
            break;

        default:
            break;
        }


        jQuery("#mo2f_send_ajax_email").click(
            function (e) {
                e.preventDefault();
                var mo2f_email = jQuery(emailSelector).val();
                if (validateEmail(mo2f_email)) {
                    let data =
                    {
                        'action'        :   'mo_ajax_register',
                        'mo_action'     :   'send_otp_over_email',
                        'email'         :    mo2f_email,
                        'nonce'         :    nonce,
                        'authTypeSend'  :    'email'
                    }

                    $mo.post(
                        ajaxurl, data,function (response) {
                            jQuery("#mo2f_send_ajax_email").html("Resend OTP");
                            if(response.status=='SUCCESS') {
                                email_txId = response.txId;
                                if(typeof jQuery('#mo2f_email_otp').val() == 'undefined') {
                                    jQuery(".buttonInsideEmail").after(otp_over_email);
                                }
                            }
                        }
                    );
                }else
                {   jQuery("#mo2f_error").empty();
                    jQuery(".buttonInsideEmail").after("<p class='mepr-form-has-errors' style='display:block;'><span id='mo2f_error'>Please enter valid email address</span></p>");
                }

            }
        );

        jQuery("#mo2f_send_ajax_sms").click(
            function (e) {
                e.preventDefault();
                var mo2f_phone = jQuery(phoneSelector).val();
                if (validatePhone(mo2f_phone)) {
                    let data =
                    {
                        'action'        :   'mo_ajax_register',
                        'mo_action'     :   'send_otp_over_sms',
                        'phone'         :    mo2f_phone,
                        'nonce'         :    nonce,
                        'authTypeSend'  :   'phone'
                    }

                    $mo.post(
                        ajaxurl, data,function (response) {
                            jQuery("#mo2f_send_ajax_sms").html("Resend OTP");
                            if(response.status=='SUCCESS') {
                                sms_txId = response.txId;
                                jQuery(".buttonInsidePhone").after(otp_over_sms);
                            }
                        }
                    );
                }else{
                    jQuery("#mo2f_error").empty();
                    jQuery(".buttonInsidePhone").after("<p class='mepr-form-has-errors' style='display:block;'><span id='mo2f_error'>Please enter valid phone number</span></p>");
                }

            }
        );

        function validate_otp(txId,otp,authType,isBoth)
        {
            let data =
            {
                'action'    :  'mo_ajax_register',
                'mo_action' :  'validate',
                'otp'       :   otp,
                'nonce'     :   nonce,
                'txId'      :   txId,
            }

            $mo.post(
                ajaxurl,data,function (response) {
                    if(response.status == 'SUCCESS') {
                        if(authType == 'email') {
                            isEmailValidated = true;
                            if(!isBoth) {
                                mo2f_rcp_ajax();
                            }
                        }
                        else if(authType == 'phone') {
                            isPhoneValidated = true;
                            if(!isBoth) {
                                mo2f_rcp_ajax();
                            }
                        }

                        if(isBoth) {
                            if(isEmailValidated && isPhoneValidated) {
                                mo2f_rcp_ajax();
                            }
                        }
                    }else{
                        jQuery("#mo2f_error").empty();
                        jQuery(".buttonInsideEmail").after("<p class='mepr-form-has-errors' style='display:block;'><span id='mo2f_error'>Please enter valid OTP</span></p>");
                        jQuery('#loading').css('display', 'none');
                        jQuery("#miniorange_submit").prop('disabled',false);
                    }

                }
            );
        }

        jQuery("#miniorange_submit").click(
            function (e) {
                e.preventDefault();
                if(!validated) {
                    jQuery("#miniorange_submit").after("<div id='loading' ><div id='loaderMsg'></div><div><img src='"+loaderUrl+"' alt='' /></div></div>");
                    jQuery("#miniorange_submit").prop('disabled',true);
                    switch(authType){
                    case 'email':
                        if(isEmailValidated) {
                            mo2f_rcp_ajax();
                        } else{
                            otp = jQuery("#mo2f_email_otp").val();
                            if(typeof otp != 'undefined') {
                                addLoaderMessage("Validating Email");
                                validate_otp(email_txId,otp,'email',isBoth);
                            }
                            else{
                                jQuery("#mo2f_error").empty();
                                jQuery(".buttonInsideEmail").after("<p class='mepr-form-has-errors' style='display:block;'><span id='mo2f_error'>Please validate Email</span></p>");
                                jQuery("#loading").css('display','none');
                                jQuery("#miniorange_submit").prop('disabled',false);
                            }
                        }
                        break;
                    case 'phone':
                        if(isPhoneValidated) {
                            mo2f_rcp_ajax();
                        } else{
                            otp = jQuery("#mo2f_phone_otp").val();
                            if(typeof otp != 'undefined') {
                                addLoaderMessage("Validating Phone");
                                validate_otp(sms_txId,otp,'phone',isBoth);
                            }
                            else{
                                jQuery("#mo2f_error").empty();
                                jQuery(".buttonInsidePhone").after("<p class='mepr-form-has-errors' style='display:block;'><span id='mo2f_error'>Please validate Phone</span></p>");
                                jQuery("#loading").css('display','none');
                                jQuery("#miniorange_submit").prop('disabled',false);
                            }
                        }
                        break;
                    default:
                        if(isEmailValidated && isPhoneValidated) {
                            mo2f_rcp_ajax();
                        } else{
                            if(!isEmailValidated) {
                                otp = jQuery("#mo2f_email_otp").val();
                            }
                            if(typeof otp != 'undefined') {
                                addLoaderMessage("Validating Email");
                                validate_otp(email_txId,otp,'email',isBoth);
                            }
                            else{
                                jQuery("#mo2f_error").empty();
                                jQuery(".buttonInsideEmail").after("<p class='mepr-form-has-errors' style='display:block;'><span id='mo2f_error'>Please validate Email</span></p>");
                                jQuery("#loading").css('display','none');
                                jQuery("#miniorange_submit").prop('disabled',false);
                            }
                            if(!isPhoneValidated) {
                                otp = jQuery("#mo2f_phone_otp").val();
                                if(typeof otp != 'undefined') {
                                    addLoaderMessage("Validating Phone");
                                    validate_otp(sms_txId,otp,'phone',isBoth);
                                }
                                else{
                                    jQuery("#mo2f_error").empty();
                                    jQuery(".buttonInsidePhone").after("<p class='mepr-form-has-errors' style='display:block;'><span id='mo2f_error'>Please validate Phone</span></p>");
                                    jQuery("#loading").css('display','none');
                                    jQuery("#miniorange_submit").prop('disabled',false);
                                }
                            }
                        }
                        break;
                    }
                }

            }
        );

        function validateEmail(email_address)
        {
            let email_regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i
            if (!email_regex.test(email_address)) {
                return false
            }
            return true
        }

        function validatePhone(phone)
        {
            let intRegex = /[0-9 -()+]+$/;
            if(phone.length < 10 || phone.length == 0 || (!intRegex.test(phone))) {
                return false
            }
            return true
        }

        function mo2f_ajax_before_reg()
        {
            jQuery(formName).removeAttr('action');
            jQuery(formName).removeAttr('method');

            if(/^#/.test(submitSelector)) {
                jQuery(submitSelector).attr('name','miniorange_submit');
                jQuery('input[name="miniorange_submit"]').attr('id','miniorange_submit');
            }else{
                jQuery(submitSelector).attr('id','miniorange_submit');
                jQuery('#miniorange_submit').attr('name','miniorange_submit');
            }
            jQuery('#miniorange_submit').removeAttr('class');
        }

        function mo2f_rcp_ajax()
        {
            validated = true;
            jQuery("#loading").css('display','none');
            jQuery("#miniorange_submit").prop('disabled',false);
            jQuery(formName).attr('action',formAction);
            jQuery(formName).attr('method',formMethod);
            jQuery("#miniorange_submit").attr('class',submitSelectorClass);
            jQuery('.'+submitSelectorClass).removeAttr('id');
            jQuery('.'+submitSelectorClass).attr('id', submitSelectorId);
            jQuery(submitSelector).click();
        }

        function addLoaderMessage(msg)
        {
            jQuery("#loaderMsg").empty();
            jQuery("#loaderMsg").append(msg);
        }
    }
);
