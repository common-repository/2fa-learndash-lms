<?php
echo '	
		<div class="mo_lms_divided_layout_2">

		<div class="mo_lms_support_layout" id="mo_lms_support_layout_tour">
			
			<img src="' . esc_url( dirname( plugin_dir_url( __FILE__ ) ) ) . '/includes/images/support3.png">
			<h1>Support</h1>
			<p>Need any help? We are available any time, Just send us a query so we can help you.</p>
				<form name="f" method="post" action="">
					<input type="hidden" name="option" value="molms_send_query"/>
					<input type="hidden" name="nonce" value=' . esc_attr( wp_create_nonce( "mo2f-support-nonce" ) ) . ' >
					<table class="mo_lms_settings_table">
						<tr><td>
							<input type="email" class="mo_lms_table_textbox" id="query_email" name="query_email" value="' . esc_html( $email ) . '" placeholder="Enter your email" required />
							</td>
						</tr>
						<tr><td>
							<input type="text" class="mo_lms_table_textbox" name="query_phone" id="query_phone" value="' . esc_html( $phone ) . '" placeholder="Enter your phone"/>
							</td>
						</tr>
						<tr>
							<td>
								<textarea id="query" name="query" class="mo_lms_settings_textarea" style="resize: vertical;width:100%" cols="52" rows="7" onkeyup="mo_lms_valid(this)" onblur="mo_lms_valid(this)" onkeypress="mo_lms_valid(this)" placeholder="Write your query here"></textarea>
							</td>
						</tr>
					</table>
					<input type="submit" name="send_query" id="send_query" value="Submit Query" style="margin-bottom:3%;" class="button button-primary" />
				</form>
				<br />			
		</div>
		</div>
		<script>
			function moSharingSizeValidate(e){
				var t=parseInt(e.value.trim());t>60?e.value=60:10>t&&(e.value=10)
			}
			function moSharingSpaceValidate(e){
				var t=parseInt(e.value.trim());t>50?e.value=50:0>t&&(e.value=0)
			}
			function moLoginSizeValidate(e){
				var t=parseInt(e.value.trim());t>60?e.value=60:20>t&&(e.value=20)
			}
			function moLoginSpaceValidate(e){
				var t=parseInt(e.value.trim());t>60?e.value=60:0>t&&(e.value=0)
			}
			function moLoginWidthValidate(e){
				var t=parseInt(e.value.trim());t>1000?e.value=1000:140>t&&(e.value=140)
			}
			function moLoginHeightValidate(e){
				var t=parseInt(e.value.trim());t>50?e.value=50:35>t&&(e.value=35)
			}
		</script>';