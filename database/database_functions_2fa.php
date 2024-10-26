<?php

require_once ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'upgrade.php';

class molms_DB {
	private $userDetailsTable;
	private $userLoginInfoTable;

	public function __construct() {
		global $wpdb;
		$this->userDetailsTable   = $wpdb->base_prefix . 'molms_user_details';
		$this->userLoginInfoTable = $wpdb->base_prefix . 'molms_user_login_info';
	}

	public function mo_plugin_activate() {
		global $wpdb;
		if ( ! get_site_option( 'molms_dbversion' ) ) {
			update_site_option( 'molms_dbversion', molms_Constants::DB_VERSION );
			$this->generate_tables();
		} else {
			$current_db_version = get_site_option( 'molms_dbversion' );
			if ( $current_db_version < molms_Constants::DB_VERSION ) {
				update_site_option( 'molms_dbversion', molms_Constants::DB_VERSION );
				$this->generate_tables();
			}
		}
	}

	public function generate_tables() {
		global $wpdb;

		$tableName = $this->userDetailsTable;

		if ( $wpdb->get_var( "show tables like '$tableName'" ) != $tableName ) {
			$sql = "CREATE TABLE IF NOT EXISTS " . $tableName . " (
				`user_id` bigint NOT NULL, 
				`mo2f_OTPOverSMS_config_status` tinyint, 
				`mo2f_miniOrangePushNotification_config_status` tinyint, 
				`mo2f_miniOrangeQRCodeAuthentication_config_status` tinyint, 
				`mo2f_miniOrangeSoftToken_config_status` tinyint, 
				`mo2f_AuthyAuthenticator_config_status` tinyint, 
				`mo2f_EmailVerification_config_status` tinyint, 
				`mo2f_SecurityQuestions_config_status` tinyint, 
				`mo2f_GoogleAuthenticator_config_status` tinyint, 
				`mo2f_OTPOverEmail_config_status` tinyint, 
				`mobile_registration_status` tinyint, 
				`mo2f_2factor_enable_2fa_byusers` tinyint DEFAULT 1,
				`mo2f_configured_2FA_method` mediumtext NOT NULL , 
				`mo2f_user_phone` mediumtext NOT NULL , 
				`mo2f_user_email` mediumtext NOT NULL,  
				`user_registration_with_miniorange` mediumtext NOT NULL, 
				`mo_2factor_user_registration_status` mediumtext NOT NULL,
				UNIQUE KEY user_id (user_id) );";

			dbDelta( $sql );
		}
		add_site_option( 'molms_email_transactions', 30 );
		add_site_option( 'molms_zero_email_transactions', 0 );
		add_site_option( 'totalUsersCloud', 0 );


		$tableName = $this->userLoginInfoTable;

		if ( $wpdb->get_var( "show tables like '$tableName'" ) != $tableName ) {
			$sql = "CREATE TABLE IF NOT EXISTS " . $tableName . " (
			`session_id` mediumtext NOT NULL, 
			 `mo2f_login_message` mediumtext NOT NULL , 
			 `mo2f_current_user_id` tinyint NOT NULL , 
			 `mo2f_1stfactor_status` mediumtext NOT NULL , 
			 `mo_2factor_login_status` mediumtext NOT NULL , 
			 `mo2f_transactionId` mediumtext NOT NULL , 
			 `mo_2_factor_kba_questions` longtext NOT NULL , 
			 `mo2f_rba_status` longtext NOT NULL , 
			 `ts_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`session_id`(100)));";

			dbDelta( $sql );
		}
	}

	public function insert_user( $user_id ) {
		global $wpdb;
		$sql = "INSERT INTO $this->userDetailsTable (user_id) VALUES($user_id) ON DUPLICATE KEY UPDATE user_id=$user_id";
		$wpdb->query( $sql );
	}

	public function get_user_detail( $column_name, $user_id ) {
		global $wpdb;
		$user_column_detail = $wpdb->get_results( "SELECT " . $column_name . " FROM " . $this->userDetailsTable . " WHERE user_id = " . $user_id . ";" );
		$value              = empty( $user_column_detail ) ? '' : get_object_vars( $user_column_detail[0] );

		return $value == '' ? '' : $value[ $column_name ];
	}

	public function delete_user_details( $user_id ) {
		global $wpdb;
		$wpdb->query(
			"DELETE FROM " . $this->userDetailsTable . "
				 WHERE user_id = " . $user_id
		);

		return;
	}

	public function delete_all_user_details() {
		global $wpdb;
		$wpdb->query(
			"DELETE FROM " . $this->userDetailsTable
		);

		return;
	}

	public function update_user_details( $user_id, $update ) {
		global $wpdb;
		$count = count( $update );
		$sql   = "UPDATE " . $this->userDetailsTable . " SET ";
		$i     = 1;
		foreach ( $update as $key => $value ) {
			$sql .= $key . "='" . $value . "'";
			if ( $i < $count ) {
				$sql .= ' , ';
			}
			$i ++;
		}
		$sql .= " WHERE user_id=" . $user_id . ";";
		$wpdb->query( $sql );

		return;
	}

	public function insert_user_login_session( $session_id ) {
		global $wpdb;
		$sql = "INSERT INTO $this->userLoginInfoTable (session_id) VALUES('$session_id') ON DUPLICATE KEY UPDATE session_id='$session_id'";

		$wpdb->query( $sql );
		$sql = "DELETE FROM $this->userLoginInfoTable WHERE ts_created < DATE_ADD(NOW(),INTERVAL - 2 MINUTE);";
		$wpdb->query( $sql );
	}

	public function save_user_login_details( $session_id, $user_values ) {
		global $wpdb;
		$count = count( $user_values );
		$sql   = "UPDATE " . $this->userLoginInfoTable . " SET ";
		$i     = 1;
		foreach ( $user_values as $key => $value ) {
			$sql .= $key . "='" . $value . "'";
			if ( $i < $count ) {
				$sql .= ' , ';
			}
			$i ++;
		}
		$sql .= " WHERE session_id='" . $session_id . "';";
		$wpdb->query( $sql );

		return;
	}

	public function get_user_login_details( $column_name, $session_id ) {
		global $wpdb;
		$user_column_detail = $wpdb->get_results( "SELECT " . $column_name . " FROM " . $this->userLoginInfoTable . " WHERE session_id = '" . $session_id . "';" );
		$value              = empty( $user_column_detail ) ? '' : get_object_vars( $user_column_detail[0] );

		return $value == '' ? '' : $value[ $column_name ];
	}

	public function delete_user_login_sessions( $session_id ) {
		global $wpdb;
		$wpdb->query(
			"DELETE FROM " . $this->userLoginInfoTable . "
				 WHERE session_id='$session_id';"
		);

		return;
	}

	public function check_alluser_limit_exceeded( $user_id ) {
		global $wpdb;
		$value                   = $wpdb->query(
			"SELECT * FROM " . $this->userDetailsTable
		);
		$user_already_configured = $wpdb->query(
			"SELECT * FROM " . $this->userDetailsTable . " WHERE user_id =" . $user_id
		);

		if ( $value < 3 || $user_already_configured ) {
			return false;
		} else {
			return true;
		}
	}
}
