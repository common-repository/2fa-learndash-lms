<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class molms_TwoFAMoSessions {
	public static function addSessionVar( $key, $val ) {
		switch ( MO2F_SESSION_TYPE ) {
			case 'TRANSIENT':
				if ( ! isset( $_COOKIE["transient_key"] ) ) {
					if ( ! wp_cache_get( "transient_key" ) ) {
						$transient_key = molms_Utility::rand();
						if ( ob_get_contents() ) {
							ob_clean();
						}
						setcookie( 'transient_key', $transient_key, time() + 12 * HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
						wp_cache_add( 'transient_key', $transient_key );
					} else {
						$transient_key = wp_cache_get( "transient_key" );
					}
				} else {
					$transient_key = sanitize_text_field( $_COOKIE["transient_key"] );
				}
				set_site_transient( $transient_key . $key, $val, 12 * HOUR_IN_SECONDS );
				break;
		}
	}

	public static function getSessionVar( $key ) {
		switch ( MO2F_SESSION_TYPE ) {
			case 'TRANSIENT':
				$transient_key = isset( $_COOKIE["transient_key"] )
					? sanitize_text_field( $_COOKIE["transient_key"] ) : wp_cache_get( "transient_key" );

				return get_site_transient( $transient_key . $key );
		}
	}

	public static function unsetSession( $key ) {
		switch ( MO2F_SESSION_TYPE ) {
			case 'TRANSIENT':
				$transient_key = isset( $_COOKIE["transient_key"] )
					? sanitize_text_field( $_COOKIE["transient_key"] ) : wp_cache_get( "transient_key" );
				if ( ! molms_Utility::check_empty_or_null( $transient_key ) ) {
					delete_site_transient( $transient_key . $key );
				}
				break;
		}
	}
}
