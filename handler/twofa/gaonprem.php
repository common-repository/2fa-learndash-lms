<?php
global $molms_dirName;
require_once $molms_dirName . 'handler' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'encryption.php';

class molms_Google_auth_onpremise {
	protected $_codeLength = 6;

	public function __construct() {
	}

	public static function mo2f_GAuth_get_site_option( $option, $val = null ) {
		if ( is_multisite() ) {
			$val = get_site_option( $option, $val );
		} else {
			$val = get_site_option( $option, $val );
		}

		return $val;
	}

	public function mo_GAuth_get_details() {
		$user    = wp_get_current_user();
		$user_id = $user->ID;
		if ( ! isset( $_SESSION ) ) {
			session_start();
		}
		if ( ! isset( $_SESSION['secret_ga'] ) ) {
			$_SESSION['secret_ga'] = $this->createSecret();
		}
		$issuer    = get_site_option( 'mo2f_google_appname', 'miniOrangeAu' );
		$email     = $user->user_email;
		$secret_ga = sanitize_text_field( $_SESSION['secret_ga'] );
		$otpcode   = $this->getCode( $secret_ga );

		$url = $this->geturl( $secret_ga, $issuer, $email );
		molms_configure_google_authenticator_onprem( $secret_ga, $url, $otpcode );
	}

	public function createSecret( $secretLength = 16 ) {
		$validChars = $this->_getBase32LookupTable();

		// Valid secret lengths are 80 to 640 bits
		if ( $secretLength < 16 || $secretLength > 128 ) {
			throw new Exception( 'Bad secret length' );
		}
		$secret = '';
		$rnd    = false;
		if ( function_exists( 'random_bytes' ) ) {
			$rnd = random_bytes( $secretLength );
		} elseif ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
			$rnd = openssl_random_pseudo_bytes( $secretLength, $cryptoStrong );
			if ( ! $cryptoStrong ) {
				$rnd = false;
			}
		}
		if ( $rnd !== false ) {
			for ( $i = 0; $i < $secretLength; ++ $i ) {
				$secret .= $validChars[ ord( $rnd[ $i ] ) & 31 ];
			}
		} else {
			throw new Exception( 'No source of secure random' );
		}

		return $secret;
	}

	public function _getBase32LookupTable() {
		return array(
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H', //  7
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'O',
			'P', // 15
			'Q',
			'R',
			'S',
			'T',
			'U',
			'V',
			'W',
			'X', // 23
			'Y',
			'Z',
			'2',
			'3',
			'4',
			'5',
			'6',
			'7', // 31
			'=',  // padding char
		);
	}

	public function getCode( $secret, $timeSlice = null ) {
		if ( $timeSlice === null ) {
			$timeSlice = floor( time() / 30 );
		}

		$secretkey = $this->_base32Decode( $secret );
		// Pack time into binary string
		$time = chr( 0 ) . chr( 0 ) . chr( 0 ) . chr( 0 ) . pack( 'N*', $timeSlice );
		// Hash it with users secret key
		$hm = hash_hmac( 'SHA1', $time, $secretkey, true );

		// Use last nipple of result as index/offset
		$offset = ord( substr( $hm, - 1 ) ) & 0x0F;

		// grab 4 bytes of the result
		$hashpart = substr( $hm, $offset, 4 );
		// Unpak binary value
		$value = unpack( 'N', $hashpart );
		$value = $value[1];
		// Only 32 bits
		$value  = $value & 0x7FFFFFFF;
		$modulo = pow( 10, $this->_codeLength );

		return str_pad( $value % $modulo, $this->_codeLength, '0', STR_PAD_LEFT );
	}

	public function _base32Decode( $secret ) {
		if ( empty( $secret ) ) {
			return '';
		}
		$base32chars        = $this->_getBase32LookupTable();
		$base32charsFlipped = array_flip( $base32chars );

		$paddingCharCount = substr_count( $secret, $base32chars[32] );
		$allowedValues    = array( 6, 4, 3, 1, 0 );
		if ( ! in_array( $paddingCharCount, $allowedValues ) ) {
			return false;
		}


		for ( $i = 0; $i < 4; ++ $i ) {
			if ( $paddingCharCount == $allowedValues[ $i ]
			     && substr( $secret, - ( $allowedValues[ $i ] ) ) != str_repeat( $base32chars[32], $allowedValues[ $i ] )
			) {
				return false;
			}
		}
		$secret       = str_replace( '=', '', $secret );
		$secret       = str_split( $secret );
		$binaryString = '';
		for ( $i = 0; $i < count( $secret ); $i = $i + 8 ) {
			$x = '';
			if ( ! in_array( $secret[ $i ], $base32chars ) ) {
				return false;
			}
			for ( $j = 0; $j < 8; ++ $j ) {
				$x .= str_pad( base_convert( @$base32charsFlipped[ @$secret[ $i + $j ] ], 10, 2 ), 5, '0', STR_PAD_LEFT );
			}
			$eightBits = str_split( $x, 8 );
			for ( $z = 0; $z < count( $eightBits ); ++ $z ) {
				$binaryString .= ( ( $y = chr( base_convert( $eightBits[ $z ], 2, 10 ) ) ) || ord( $y ) == 48 ) ? $y : '';
			}
		}

		return $binaryString;
	}

	public function geturl( $secret, $issuer, $email ) {
		// id can be email or name
		$url = "otpauth://totp/";

		$url .= $email . "?secret=" . $secret . "&issuer=" . $issuer;

		return $url;
	}

	public function mo_GAuth_set_secret( $user_id, $secret ) {
		global $molms_db_queries;
		$key = $this->random_str( 8 );
		update_user_meta( $user_id, 'mo2f_get_auth_rnd_string', $key );
		$secret = molms_2f_GAuth_AESEncryption::encrypt_data_ga( $secret, $key );
		update_user_meta( $user_id, 'mo2f_gauth_key', $secret );
	}

	public function random_str( $length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ) {
		$randomString     = '';
		$charactersLength = strlen( $keyspace );
		for ( $i = 0; $i < $length; $i ++ ) {
			$randomString .= $keyspace[ rand( 0, $charactersLength - 1 ) ];
		}

		return $randomString;
	}

	public function mo_GAuth_get_secret( $user_id ) {
		global $molms_db_queries;
		$key    = get_user_meta( $user_id, 'mo2f_get_auth_rnd_string', true );
		$secret = get_user_meta( $user_id, 'mo2f_gauth_key', true );
		$secret = molms_2f_GAuth_AESEncryption::decrypt_data( $secret, $key );

		return $secret;
	}

	public function verifyCode( $secret, $code, $discrepancy = 3, $currentTimeSlice = null ) {
		global $molms_db_queries;
		$response = array( "status" => 'false' );
		if ( $currentTimeSlice === null ) {
			$currentTimeSlice = floor( time() / 30 );
		}

		if ( strlen( $code ) != 6 ) {
			return wp_json_encode( $response );
		}
		for ( $i = - $discrepancy; $i <= $discrepancy; ++ $i ) {
			$calculatedCode = $this->getCode( $secret, $currentTimeSlice + $i );
			if ( $this->timingSafeEquals( $calculatedCode, $code ) ) {
				update_site_option( 'mo2f_time_slice', $i );
				$response['status'] = 'SUCCESS';

				return wp_json_encode( $response );
			}
		}

		return wp_json_encode( $response );
	}

	public function timingSafeEquals( $safeString, $userString ) {
		if ( function_exists( 'hash_equals' ) ) {
			return hash_equals( $safeString, $userString );
		}
		$safeLen = strlen( $safeString );
		$userLen = strlen( $userString );

		if ( $userLen != $safeLen ) {
			return false;
		}

		$result = 0;

		for ( $i = 0; $i < $userLen; ++ $i ) {
			$result |= ( ord( $safeString[ $i ] ) ^ ord( $userString[ $i ] ) );
		}

		// They are only identical strings if $result is exactly 0...
		return $result === 0;
	}
}
