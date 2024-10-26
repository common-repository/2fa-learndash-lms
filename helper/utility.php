<?php

/**
 * miniOrange enables user to log in through mobile authentication as an additional layer of security over password.
 * Copyright (C) 2015  miniOrange
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 * @package miniOrange OAuth
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

class molms_Utility {
	public static function icr() {
		$email        = get_site_option( 'mo2f_email' );
		$customer_key = get_site_option( 'mo2f_customerKey' );
		if ( ! $email || ! $customer_key || ! is_numeric( trim( $customer_key ) ) ) {
			return 0;
		} else {
			return 1;
		}
	}

	public static function check_empty_or_null( $value ) {
		if ( ! isset( $value ) || empty( $value ) ) {
			return true;
		}

		return false;
	}

	public static function rand() {
		$length       = wp_rand( 0, 15 );
		$characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ( $i = 0; $i < $length; $i ++ ) {
			$randomString .= $characters[ wp_rand( 0, strlen( $characters ) - 1 ) ];
		}

		return $randomString;
	}

	public static function getFeatureStatus() {
		$status = '';
		$status .= "#";

		if ( get_site_option( 'mo2f_two_factor' ) ) {
			$status .= "TF1";
		}

		$status .= "R" . rand( 0, 1000 );

		return $status;
	}

	public static function get_mo2f_db_option( $value, $type ) {
		if ( $type == 'site_option' ) {
			$db_value = get_site_option( $value, $GLOBALS[ $value ] );
		} else {
			$db_value = get_site_option( $value, $GLOBALS[ $value ] );
		}

		return $db_value;
	}

	public function lockedOutlink() {
		if ( MOLMS_IS_ONPREM ) {
			return molms_Constants::OnPremiseLockedOut;
		} else {
			return molms_Constants::CloudLockedOut;
		}
	}
}
