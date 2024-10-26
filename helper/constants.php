<?php

class molms_Constants {
	const DEFAULT_CUSTOMER_KEY = "16555";
	const DEFAULT_API_KEY = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
	const DB_VERSION = 150;
	const SUPPORT_EMAIL = 'info@xecurify.com';
	//urls
	const MOLMS_HOST_NAME = "https://login.xecurify.com";
	//plugins
	const FAQ_PAYMENT_URL = 'https://faq.miniorange.com/knowledgebase/all-i-want-to-do-is-upgrade-to-a-premium-licence/';
	const LOGIN_ATTEMPTS_EXCEEDED = "User exceeded allowed login attempts.";
	const CloudLockedOut = 'https://faq.miniorange.com/knowledgebase/how-to-gain-access-to-my-website-if-i-get-locked-out/';
	const OnPremiseLockedOut = 'https://faq.miniorange.com/knowledgebase/i-am-locked-cant-access-my-account-what-do-i-do/';

	public function __construct() {
		$this->define_global();
	}

	public function define_global() {
		global $molms_utility, $molms_dirName, $molms_db_queries;
		$molms_utility    = new molms_Utility();
		$molms_dirName    = plugin_dir_path( dirname( __FILE__ ) );
		$molms_db_queries = new molms_DB();
	}
}

new molms_Constants;
