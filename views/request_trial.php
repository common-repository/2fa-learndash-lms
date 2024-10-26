<?php
?>

<div class="mo_lms_divided_layout molms_trial_box">
    <div class="mo_lms_setting_layout">
        <h3> Trial Request Form :
            <div style="float: right;">
				<?php
				echo '<a class="mo_lms_button mo_lms_button1 molms_offer_contact_us_button" href="' . esc_url( $two_fa ) . '">Back</a>';
				?>
            </div>
        </h3>
        <form method="post">
            <input type="hidden" name="option" value="molms_trial_request_form"/>
            <input type="hidden" name="nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'molms_request_trial-nonce' ) ) ?>">
            <table cellpadding="7" cellspacing="4">
                <tr>
                    <td><strong>Email ID : </strong></td>
                    <td><input required type="email" name="molms_trial_email" style="width: 100%;"
                               value="<?php echo esc_attr( $email ); ?>" placeholder="Email id" value=""/></td>
                </tr>
                <tr>
                    <td valign=top><strong>Request a Trial for : </strong></td>
                    <td>
                        <p style="margin-top:0px">
                            <input type='radio' name='molms_trial_plan' value="All Inclusive" required>All Inclusive
                            (Unlimited Users + Advanced Features)<br>
                        </p>
                        <p><input type='radio' name='molms_trial_plan' value="Enterprise" required>Enterprise(Unlimited
                            sites)<br></p>
                        <p><input type='radio' name='molms_trial_plan' value="notSure" required>I am confused!!<br></p>
                        <a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank">Checkout our Plans</a>
                    </td>
                </tr>
            </table>
            <div style="padding-top: 10px;">
                <input type="submit" name="submit" value="Submit Trial Request"
                       class="mo_lms_button mo_lms_button1 molms_offer_contact_us_button"/>
            </div>
        </form>
    </div>
</div>