<?php


function molms_configure_kba_questions() {
	?>

    <div class="mo2f_kba_header"><?php echo esc_html( molms_lt( 'Please choose 3 questions' ) ); ?></div>
    <br>
    <table cellspacing="10">
        <tr class="mo2f_kba_header">
            <td>
				<?php echo esc_html( molms_lt( 'Sr. No.' ) ); ?>
            </td>
            <td class="mo2f_kba_tb_data">
				<?php echo esc_html( molms_lt( 'Questions' ) ); ?>
            </td>
            <td>
				<?php echo esc_html( molms_lt( 'Answers' ) ); ?>
            </td>
        </tr>
        <tr class="mo2f_kba_body">
            <td>
                <center>1.</center>
            </td>
            <td class="mo2f_kba_tb_data">
                <select name="mo2f_kbaquestion_1" id="mo2f_kbaquestion_1" class="mo2f_kba_ques" required="true"
                        onchange="mo_option_hide(1)">
                    <option value="" selected="selected">
                        -------------------------<?php echo esc_html( molms_lt( 'Select your question' ) ); ?>
                        -------------------------
                    </option>
                    <option id="mq1_1"
                            value="What is your first company name?"><?php echo esc_html( molms_lt( 'What is your first company name?' ) ); ?></option>
                    <option id="mq2_1"
                            value="What was your childhood nickname?"><?php echo esc_html( molms_lt( 'What was your childhood nickname?' ) ); ?></option>
                    <option id="mq3_1"
                            value="In what city did you meet your spouse/significant other?"><?php echo esc_html( molms_lt( 'In what city did you meet your spouse/significant other?' ) ); ?></option>
                    <option id="mq4_1"
                            value="What is the name of your favorite childhood friend?"><?php echo esc_html( molms_lt( 'What is the name of your favorite childhood friend?' ) ); ?></option>
                    <option id="mq5_1"
                            value="What school did you attend for sixth grade?"><?php echo esc_html( molms_lt( 'What school did you attend for sixth grade?' ) ); ?></option>
                    <option id="mq6_1"
                            value="In what city or town was your first job?"><?php echo esc_html( molms_lt( 'In what city or town was your first job?' ) ); ?></option>
                    <option id="mq7_1"
                            value="What is your favourite sport?"><?php echo esc_html( molms_lt( 'What is your favourite sport?' ) ); ?></option>
                    <option id="mq8_1"
                            value="Who is your favourite sports player?"><?php echo esc_html( molms_lt( 'Who is your favourite sports player?' ) ); ?></option>
                    <option id="mq9_1"
                            value="What is your grandmother's maiden name?"><?php echo esc_html( molms_lt( "What is your grandmother's maiden name?" ) ); ?></option>
                    <option id="mq10_1"
                            value="What was your first vehicle's registration number?"><?php echo esc_html( molms_lt( "What was your first vehicle's registration number?" ) ); ?></option>
                </select>
            </td>
            <td>
                <input class="mo2f_table_textbox" type="password" name="mo2f_kba_ans1" id="mo2f_kba_ans1"
                       title="<?php echo esc_html( molms_lt( 'Only alphanumeric letters with special characters(_@.$#&amp;+-) are allowed.' ) ); ?>"
                       pattern="(?=\S)[A-Za-z0-9_@.$#&amp;+\-\s]{1,100}" required="true" autofocus="true"
                       placeholder="<?php echo esc_html( molms_lt( 'Enter your answer' ) ); ?>"/>
            </td>
        </tr>
        <tr class="mo2f_kba_body">
            <td>
                <center>2.</center>
            </td>
            <td class="mo2f_kba_tb_data">
                <select name="mo2f_kbaquestion_2" id="mo2f_kbaquestion_2" class="mo2f_kba_ques" required="true"
                        onchange="mo_option_hide(2)">
                    <option value="" selected="selected">
                        -------------------------<?php echo esc_html( molms_lt( 'Select your question' ) ); ?>
                        -------------------------
                    </option>
                    <option id="mq1_2"
                            value="What is your first company name?"><?php echo esc_html( molms_lt( 'What is your first company name?' ) ); ?></option>
                    <option id="mq2_2"
                            value="What was your childhood nickname?"><?php echo esc_html( molms_lt( 'What was your childhood nickname?' ) ); ?></option>
                    <option id="mq3_2"
                            value="In what city did you meet your spouse/significant other?"><?php echo esc_html( molms_lt( 'In what city did you meet your spouse/significant other?' ) ); ?></option>
                    <option id="mq4_2"
                            value="What is the name of your favorite childhood friend?"><?php echo esc_html( molms_lt( 'What is the name of your favorite childhood friend?' ) ); ?></option>
                    <option id="mq5_2"
                            value="What school did you attend for sixth grade?"><?php echo esc_html( molms_lt( 'What school did you attend for sixth grade?' ) ); ?></option>
                    <option id="mq6_2"
                            value="In what city or town was your first job?"><?php echo esc_html( molms_lt( 'In what city or town was your first job?' ) ); ?></option>
                    <option id="mq7_2"
                            value="What is your favourite sport?"><?php echo esc_html( molms_lt( 'What is your favourite sport?' ) ); ?></option>
                    <option id="mq8_2"
                            value="Who is your favourite sports player?"><?php echo esc_html( molms_lt( 'Who is your favourite sports player?' ) ); ?></option>
                    <option id="mq9_2"
                            value="What is your grandmother's maiden name?"><?php echo esc_html( molms_lt( 'What is your grandmother\'s maiden name?' ) ); ?></option>
                    <option id="mq10_2"
                            value="What was your first vehicle's registration number?"><?php echo esc_html( molms_lt( 'What was your first vehicle\'s registration number?' ) ); ?></option>
                </select>
            </td>
            <td>
                <input class="mo2f_table_textbox" type="password" name="mo2f_kba_ans2" id="mo2f_kba_ans2"
                       title="<?php echo esc_html( molms_lt( 'Only alphanumeric letters with special characters(_@.$#&amp;+-) are allowed.' ) ); ?>"
                       pattern="(?=\S)[A-Za-z0-9_@.$#&amp;+\-\s]{1,100}" required="true"
                       placeholder="<?php echo esc_html( molms_lt( 'Enter your answer' ) ); ?>"/>
            </td>
        </tr>
        <tr class="mo2f_kba_body">
            <td>
                <center>3.</center>
            </td>
            <td class="mo2f_kba_tb_data">
                <input class="mo2f_kba_ques" type="text" style="width: 100%;" name="mo2f_kbaquestion_3"
                       id="mo2f_kbaquestion_3"
                       required="true"
                       placeholder="<?php echo esc_html( molms_lt( 'Enter your custom question here' ) ); ?>"/>
            </td>
            <td>
                <input class="mo2f_table_textbox" type="password" name="mo2f_kba_ans3" id="mo2f_kba_ans3"
                       title="<?php echo esc_html( molms_lt( 'Only alphanumeric letters with special characters(_@.$#&amp;+-) are allowed.' ) ); ?>"
                       pattern="(?=\S)[A-Za-z0-9_@.$#&amp;+\-\s]{1,100}" required="true"
                       placeholder="<?php echo esc_html( molms_lt( 'Enter your answer' ) ); ?>"/>
            </td>
        </tr>
    </table>

    <script>
        //hidden element in dropdown list 1
        var mo_option_to_hide1;
        //hidden element in dropdown list 2
        var mo_option_to_hide2;

        function mo_option_hide(list) {
            //grab the team selected by the user in the dropdown list
            var list_selected = document.getElementById("mo2f_kbaquestion_" + list).selectedIndex;
            //if an element is currently hidden, unhide it
            if (typeof (mo_option_to_hide1) != "undefined" && mo_option_to_hide1 !== null && list == 2) {
                mo_option_to_hide1.style.display = 'block';
            } else if (typeof (mo_option_to_hide2) != "undefined" && mo_option_to_hide2 !== null && list == 1) {
                mo_option_to_hide2.style.display = 'block';
            }
            //select the element to hide and then hide it
            if (list == 1) {
                if (list_selected != 0) {
                    mo_option_to_hide2 = document.getElementById("mq" + list_selected + "_2");
                    mo_option_to_hide2.style.display = 'none';
                }
            }
            if (list == 2) {
                if (list_selected != 0) {
                    mo_option_to_hide1 = document.getElementById("mq" + list_selected + "_1");
                    mo_option_to_hide1.style.display = 'none';
                }
            }
        }
    </script>
	<?php if ( isset( $_SESSION['mo2f_mobile_support'] ) && sanitize_text_field( $_SESSION['mo2f_mobile_support'] ) == 'MO2F_EMAIL_BACKUP_KBA' ) {
		?>
        <input type="hidden" name="mobile_kba_option" value="mo2f_request_for_kba_as_emailbackup"/>
		<?php
	}
}

function molms_configure_for_mobile_suppport_kba( $user ) {
	?>

    <h3><?php echo esc_html( molms_lt( 'Configure Second Factor - KBA (Security Questions)' ) ); ?>
    </h3>
    <hr/>
    <form name="f" method="post" action="" id="mo2f_kba_setup_form">
		<?php molms_configure_kba_questions(); ?>
        <br>
        <input type="hidden" name="option" value="mo2f_save_kba"/>
        <input type="hidden" name="mo2f_save_kba_nonce"
               value="<?php echo esc_attr( wp_create_nonce( "mo2f-save-kba-nonce" ) ) ?>"/>
        <center>
            <table>
                <tr>
                    <td>
                        <input type="submit" id="mo2f_kba_submit_btn" name="submit"
                               value="<?php echo esc_html( molms_lt( 'Save' ) ); ?>"
                               class="mo_lms_button mo_lms_button1" style="width:100px;line-height:30px;"/>
                    </td>
    </form>

    <td>

        <form name="f" method="post" action="" id="mo2f_go_back_form">
            <input type="hidden" name="option" value="mo2f_go_back"/>
            <input type="hidden" name="mo2f_go_back_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( "mo2f-go-back-nonce" ) ) ?>"/>
            <input type="submit" name="back" id="go_back" class="mo_lms_button mo_lms_button1"
                   value="<?php echo esc_html( molms_lt( 'Back' ) ); ?>"
                   style="width:100px;line-height:30px;"/>

        </form>

    </td>
    </tr>
    </table>
    </center>
    <script>

        jQuery('#mo2f_kba_submit_btn').click(function () {
            jQuery('#mo2f_kba_setup_form').submit();
        });
    </script>
	<?php
}

?>