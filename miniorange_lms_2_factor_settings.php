<?php
/**
 * Plugin Name: 2FA LearnDash LMS
 * Description: This plugin provides various two-factor authentication methods as an additional layer of security after the default wordpress login. We Support Google/Authy/LastPass Authenticator, QR Code, Push Notification, Soft Token and Security Questions(KBA) for 3 User in the free version of the plugin.
 * Version: 1.0.5
 * Author: miniOrange
 * Author URI: https://miniorange.com
 * Text Domain: 2fa-learndash-lms
 * License: GPL2
 */

define( 'MOLMS_HOST_NAME', 'https://login.xecurify.com' );
define( 'MOLMS_VERSION', '1.0.5' );
define( 'MOLMS_PLUGIN_URL', ( plugin_dir_url( __FILE__ ) ) );
define( 'MOLMS_TEST_MODE', false );
define( 'MOLMS_IS_ONPREM', get_site_option( 'is_onprem' ) );
global $lms_mainDir;
$lms_mainDir = plugin_dir_url( __FILE__ );

class molms_twoFactor {
	public function __construct() {
		register_deactivation_hook( __FILE__, array( $this, 'molms_deactivate' ) );
		register_activation_hook( __FILE__, array( $this, 'molms_activate' ) );
		add_action( 'admin_menu', array( $this, 'molms_widget_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'molms_settings_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'molms_settings_script' ) );
		add_action( 'molms_show_message', array( $this, 'molms_show_message' ), 1, 2 );
		add_action( 'admin_init', array( $this, 'molms_reset_save_settings' ) );
		add_action( 'admin_init', array( $this, 'molms_redirect_page' ) );
		add_filter( 'manage_users_columns', array( $this, 'molms_mapped_email_column' ) );
		add_action( 'admin_notices', array( $this, 'molms_notices' ) );
		add_action( 'manage_users_custom_column', array( $this, 'molms_mapped_email_column_content' ), 10, 3 );

		$actions = add_filter( 'user_row_actions', array( $this, 'molms_reset_users' ), 10, 2 );
		add_action( 'admin_footer', array( $this, 'molms_feedback_request' ) );
		add_action( 'plugins_loaded', array( $this, 'molms_load_textdomain' ) );
		$this->includes();
	}

	public function includes() {
		include 'database' . DIRECTORY_SEPARATOR . 'mo2f_db_options.php';
		include 'helper' . DIRECTORY_SEPARATOR . 'utility.php';
		include 'database' . DIRECTORY_SEPARATOR . 'database_functions_2fa.php';
		include 'helper' . DIRECTORY_SEPARATOR . 'constants.php';
		include 'api' . DIRECTORY_SEPARATOR . 'class-molms-customer-cloud-setup.php';
		include 'api' . DIRECTORY_SEPARATOR . 'class-molms-customer-setup.php';
		include 'api' . DIRECTORY_SEPARATOR . 'class-molms-rba-attributes.php';
		include 'api' . DIRECTORY_SEPARATOR . 'class-molms-two-factor-setup.php';
		include 'handler' . DIRECTORY_SEPARATOR . 'feedback_form.php';
		include 'handler' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'setup_twofa.php';
		include 'handler' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_settings.php';
		include 'handler' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_utility.php';
		include 'handler' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_constants.php';
		include 'handler' . DIRECTORY_SEPARATOR . 'class-molms-session.php';
		include 'helper' . DIRECTORY_SEPARATOR . 'curl.php';
		include 'helper' . DIRECTORY_SEPARATOR . 'messages.php';
		include 'controllers' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_factor_ajax.php';
	}

	function molms_redirect_page() {
		if ( get_site_option( 'molms_plugin_redirect' ) ) {
			delete_site_option( 'molms_plugin_redirect' );
			wp_redirect( admin_url() . 'admin.php?page=molms_two_fa' );
			exit();
		}
		if ( isset( $_POST['molms_register_to_upgrade_nonce'] ) ) {
			$nonce = isset( $_POST['molms_register_to_upgrade_nonce'] ) ? sanitize_text_field( $_POST['molms_register_to_upgrade_nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-molms-user-reg-to-upgrade-nonce' ) ) {
				update_site_option( 'molms_message', "INVALID_REQ" );
			} else {
				$requestOrigin = isset( $_POST['requestOrigin'] ) ? sanitize_text_field( $_POST['requestOrigin'] ) : '';
				update_site_option( 'molms_customer_selected_plan', $requestOrigin );
				header( 'Location: admin.php?page=molms_account' );
			}
		}

	}

	function molms_notices() {
		global $molms_db_queries;
		$is_customer_registered = ( get_site_option( 'mo_2factor_admin_registration_status' ) == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' );
		if ( ! get_site_option( 'molms_trial_query_sent' ) && ! get_site_option( 'molms_donot_show_trial_notice_always' ) && current_user_can( 'administrator' ) ) {
			if ( ! $is_customer_registered ) {
				echo molms_Messages::showMessage( 'FREE_TRIAL_MESSAGE_ACCOUNT_PAGE' );
			} else {
				echo molms_Messages::showMessage( 'FREE_TRIAL_MESSAGE_TRIAL_PAGE' );
			}
		}

	}

	public function molms_feedback_request() {
		if ( isset( $_SERVER['PHP_SELF'] ) && 'plugins.php' != basename( sanitize_text_field( $_SERVER['PHP_SELF'] ) ) ) {
			return;
		}
		global $molms_dirName;

		$email = get_site_option( "mo2f_email" );
		if ( empty( $email ) ) {
			$user  = wp_get_current_user();
			$email = $user->user_email;
		}
		$imagepath = plugins_url( '/includes/images/', __FILE__ );

		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_script( 'utils' );
		wp_enqueue_style( 'mo_lms_admin_plugins_page_style', plugins_url( '/includes/css/style_settings.css', __FILE__ ), array(), MOLMS_VERSION, 'all' );

		include $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'feedback_form.php';
	}

	/**
	 * Function tells where to look for translations.
	 */
	public function molms_load_textdomain() {
		load_plugin_textdomain( '2fa-learndash-lms', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	public function molms_widget_menu() {
		$user         = wp_get_current_user();
		$userID       = $user->ID;
		$onprem_admin = get_site_option( 'mo2f_onprem_admin' );
		$roles        = ( array ) $user->roles;
		$flag         = 0;
		foreach ( $roles as $role ) {
			if ( get_site_option( 'mo2fa_' . $role ) == '1' ) {
				$flag = 1;
			}
		}

		$is_2fa_enabled = ( ( $flag ) or ( $userID == $onprem_admin ) );

		$menu_slug = 'molms_two_fa';

		add_menu_page( 'LMS 2-Factor', 'LMS 2-Factor', 'administrator', $menu_slug, array(
			$this,
			'mo_lms'
		), plugin_dir_url( __FILE__ ) . 'includes/images/miniorange_icon.png' );

		add_submenu_page( $menu_slug, 'LMS 2-Factor', 'Two Factor', 'read', 'molms_two_fa', array(
			$this,
			'mo_lms'
		), 1 );

		add_submenu_page( $menu_slug, 'LMS 2-Factor', 'Troubleshooting', 'administrator', 'molms_troubleshooting', array(
			$this,
			'mo_lms'
		), 2 );
		add_submenu_page( $menu_slug, 'LMS 2-Factor', 'Account', 'administrator', 'molms_account', array(
			$this,
			'mo_lms'
		), 3 );
		add_submenu_page( $menu_slug, 'LMS 2-Factor', 'Request for Trial', 'administrator', 'molms_request_trial', array(
			$this,
			'mo_lms'
		), 6 );
		add_submenu_page( $menu_slug, 'LMS 2-Factor', 'Upgrade', 'administrator', 'molms_upgrade', array(
			$this,
			'mo_lms'
		), 12 );
		$mo2fa_hook_page = add_users_page( 'Reset 2nd Factor', null, 'manage_options', 'reset', array(
			$this,
			'mo_reset_2fa_for_users_by_admin'
		), 66 );
	}

	public function mo_lms() {
		global $molms_db_queries;
		$molms_db_queries->mo_plugin_activate();
		add_site_option( 'totalUsersCloud', 0 );

		include 'controllers' . DIRECTORY_SEPARATOR . 'main_controller.php';
	}

	public function molms_activate() {
		global $molms_db_queries;
		$userid = wp_get_current_user()->ID;

		$molms_db_queries->mo_plugin_activate();
		add_site_option( 'mo2f_activate_plugin', 1 );
		add_site_option( 'mo2f_login_option', 1 );
		add_site_option( 'mo2f_is_NC', 1 );
		add_site_option( 'mo2f_is_NNC', 1 );
		add_site_option( 'mo2fa_administrator', 1 );
		add_site_option( 'is_onprem', 1 );
		add_site_option( 'mo2f_onprem_admin', $userid );
		add_site_option( 'mo2f_number_of_transactions', 1 );
		add_site_option( 'mo2f_set_transactions', 0 );
		add_site_option( 'mo2f_enable_forgotphone', 1 );
		add_site_option( 'mo2f_enable_2fa_for_users', 1 );
		add_site_option( 'mo2f_enable_2fa_prompt_on_login_page', 0 );
		add_site_option( 'mo2f_enable_xmlrpc', 0 );
		add_action( 'mo_auth_show_success_message', array( $this, 'mo_auth_show_success_message' ), 10, 1 );
		add_action( 'mo_auth_show_error_message', array( $this, 'mo_auth_show_error_message' ), 10, 1 );
		add_site_option( 'mo2f_show_sms_transaction_message', 0 );
		add_site_option( 'molms_email_transactions', 30 );
		add_site_option( 'molms_zero_email_transactions', 0 );
		add_site_option( 'mo2f_inline_registration', 1 );
		if ( get_site_option( 'mo2f_activated_time' ) == null ) {
			add_site_option( 'mo2f_activated_time', time() );
		}
		update_site_option( 'molms_plugin_redirect', true );
	}

	public function molms_deactivate() {
		update_site_option( 'molms_activate_plugin', 1 );

		$two_fa_settings = new molms_Miniorange_Authentication();
		$two_fa_settings->mo_auth_deactivate();
	}

	public function molms_settings_style( $hook ) {
		if ( strpos( $hook, 'page_mo_2fa' ) || strpos( $hook, 'page_molms' ) ) {
			wp_enqueue_style( 'mo_2fa_admin_settings_jquery_style', plugins_url( 'includes/css/jquery.ui.css', __FILE__ ), array(), MOLMS_VERSION, 'all' );
			wp_enqueue_style( 'mo_2fa_admin_settings_phone_style', plugins_url( 'includes/css/phone.css', __FILE__ ), array(), MOLMS_VERSION, 'all' );
			wp_enqueue_style( 'mo_lms_admin_settings_style', plugins_url( 'includes/css/style_settings.css', __FILE__ ), array(), MOLMS_VERSION, 'all' );
			wp_enqueue_style( 'mo_lms_admin_settings_phone_style', plugins_url( 'includes/css/phone.css', __FILE__ ), array(), MOLMS_VERSION, 'all' );
			wp_enqueue_style( 'mo_lms_admin_settings_datatable_style', plugins_url( 'includes/css/jquery.dataTables.min.css', __FILE__ ), array(), MOLMS_VERSION, 'all' );
			wp_enqueue_style( 'mo_lms_button_settings_style', plugins_url( 'includes/css/button_styles.css', __FILE__ ), array(), MOLMS_VERSION, 'all' );
			wp_enqueue_style( 'mo_lms_popup_settings_style', plugins_url( 'includes/css/popup.css', __FILE__ ), array(), MOLMS_VERSION, 'all' );
		}
	}

	public function molms_settings_script( $hook ) {
		wp_enqueue_script( 'mo_lms_admin_settings_script', plugins_url( 'includes/js/settings_page.js', __FILE__ ), array( 'jquery' ), array(), MOLMS_VERSION, false );
		if ( strpos( $hook, 'page_mo_2fa' ) || strpos( $hook, 'page_molms' ) ) {
			wp_enqueue_script( 'mo_lms_admin_settings_phone_script', plugins_url( 'includes/js/phone.js', __FILE__ ), array(), MOLMS_VERSION, false );
			wp_enqueue_script( 'mo_lms_admin_datatable_script', plugins_url( 'includes/js/jquery.dataTables.min.js', __FILE__ ), array( 'jquery' ), array(), MOLMS_VERSION, false );
			wp_enqueue_script( 'mo_lms_qrcode_script', plugins_url( "/includes/jquery-qrcode/jquery-qrcode.js", __FILE__ ), array(), MOLMS_VERSION, false );
			wp_enqueue_script( 'mo_lms_min_qrcode_script', plugins_url( "/includes/jquery-qrcode/jquery-qrcode.min.js", __FILE__ ), array(), MOLMS_VERSION, false );
		}
	}

	function molms_show_message( $content, $type ) {
		if ( $type == "CUSTOM_MESSAGE" ) {
			echo "<div class='overlay_not_JQ_success' id='pop_up_success'><p class='popup_text_not_JQ'>" . wp_kses_post( $content ) . "</p> </div>";
			?>
            <script type="text/javascript">
                setTimeout(function () {
                    var element = document.getElementById("pop_up_success");
                    element.classList.toggle("overlay_not_JQ_success");
                    element.innerHTML = "";
                }, 7000);

            </script>
			<?php
		}
		if ( $type == "NOTICE" ) {
			echo "<div class='overlay_not_JQ_error' id='pop_up_error'><p class='popup_text_not_JQ'>" . wp_kses_post( $content ) . "</p> </div>";
			?>
            <script type="text/javascript">
                setTimeout(function () {
                    var element = document.getElementById("pop_up_error");
                    element.classList.toggle("overlay_not_JQ_error");
                    element.innerHTML = "";
                }, 7000);

            </script>
			<?php
		}
		if ( $type == "ERROR" ) {
			echo "<div class='overlay_not_JQ_error' id='pop_up_error'><p class='popup_text_not_JQ'>" . wp_kses_post( $content ) . "</p> </div>";
			?>
            <script type="text/javascript">
                setTimeout(function () {
                    var element = document.getElementById("pop_up_error");
                    element.classList.toggle("overlay_not_JQ_error");
                    element.innerHTML = "";
                }, 7000);

            </script>
			<?php
		}
		if ( $type == "SUCCESS" ) {
			echo "<div class='overlay_not_JQ_success' id='pop_up_success'><p class='popup_text_not_JQ'>" . wp_kses_post( $content ) . "</p> </div>";
			?>
            <script type="text/javascript">
                setTimeout(function () {
                    var element = document.getElementById("pop_up_success");
                    element.classList.toggle("overlay_not_JQ_success");
                    element.innerHTML = "";
                }, 7000);

            </script>
			<?php
		}
	}

	public function molms_reset_users( $actions, $user_object ) {
		global $molms_db_queries;
		$mo2f_configured_2FA_method = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $user_object->ID );

		$tfa_enabled                         = $molms_db_queries->get_user_detail( 'mo2f_2factor_enable_2fa_byusers', $user_object->ID );
		$mo_2factor_user_registration_status = $molms_db_queries->get_user_detail( 'mo_2factor_user_registration_status', $user_object->ID );

		if ( $tfa_enabled == 0 && ( $mo_2factor_user_registration_status != 'MO_2_FACTOR_PLUGIN_SETTINGS' ) && $tfa_enabled != '' ) {
			$mo2f_configured_2FA_method = 1;
		}
		if ( current_user_can( 'administrator', $user_object->ID ) && $mo2f_configured_2FA_method ) {
			if ( get_current_user_id() != $user_object->ID ) {
				$actions['molms_reset_users'] = "<a class='molms_reset_users' href='" . admin_url( "users.php?page=reset&action=reset_edit&amp;user=$user_object->ID" ) . "'>" . __( 'Reset 2 Factor', 'cgc_ub' ) . "</a>";
			}
		}

		return $actions;
	}


	public function molms_mapped_email_column( $columns ) {
		$columns['current_method'] = '2FA Method';

		return $columns;
	}

	public function mo_reset_2fa_for_users_by_admin() {
		$nonce = wp_create_nonce( 'ResetTwoFnonce' );
		if ( isset( $_GET['action'] ) && sanitize_text_field( $_GET['action'] ) == 'reset_edit' ) {
			$user_id   = sanitize_text_field( $_GET['user'] );
			$user_info = get_userdata( $user_id ); ?>
            <form method="post" name="reset2fa" id="reset2fa" action="<?php echo esc_url( 'users.php' ); ?>">

                <div class="wrap">
                    <h1>Reset 2nd Factor</h1>

                    <p>You have specified this user for reset:</p>

                    <ul>
                        <li>ID #<?php echo esc_html( $user_info->ID ); ?>
                            : <?php echo esc_html( $user_info->user_login ); ?></li>
                    </ul>
                    <input type="hidden" name="userid" value="<?php echo esc_html( $user_id ); ?>">
                    <input type="hidden" name="miniorange_reset_2fa_option" value="mo_reset_2fa">
                    <input type="hidden" name="nonce" value="<?php echo esc_html( $nonce ); ?>">
                    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                                             value="Confirm Reset"></p>
                </div>
            </form>
			<?php
		}
	}

	public function molms_reset_save_settings() {
		if ( isset( $_POST['miniorange_reset_2fa_option'] ) && sanitize_text_field( $_POST['miniorange_reset_2fa_option'] ) == 'mo_reset_2fa' ) {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'ResetTwoFnonce' ) ) {
				return;
			}
			$user_id = isset( $_POST['userid'] ) && ! empty( $_POST['userid'] ) ? sanitize_text_field( $_POST['userid'] ) : '';
			if ( ! empty( $user_id ) ) {
				if ( current_user_can( 'edit_user' ) ) {
					global $molms_db_queries;
					delete_user_meta( $user_id, 'mo2f_kba_challenge' );
					delete_user_meta( $user_id, 'mo2f_2FA_method_to_configure' );
					delete_user_meta( $user_id, 'Security Questions' );
					$molms_db_queries->delete_user_details( $user_id );
					delete_user_meta( $user_id, 'mo2f_2FA_method_to_test' );
				}
			}
		}
		if ( isset( $_POST['molms_dismiss_trial'] ) && sanitize_text_field( $_POST['molms_dismiss_trial'] == 'molms_dismiss_trial' ) ) {
			update_site_option( 'molms_donot_show_trial_notice_always', 1 );
		}
	}

	public function molms_mapped_email_column_content( $value, $column_name, $user_id ) {
		global $molms_db_queries;
		$currentMethod = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $user_id );
		if ( ! $currentMethod ) {
			$currentMethod = 'Not Registered for 2FA';
		}
		if ( 'current_method' == $column_name ) {
			return $currentMethod;
		}

		return $value;
	}
}

new molms_twoFactor;
?>