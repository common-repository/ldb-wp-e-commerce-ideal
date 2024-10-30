<?php

	/*
		Pre-2.6 compatibility
	*/


	if(!defined('WP_CONTENT_DIR'))
	{
		define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
	}
	if(!defined('WP_PLUGIN_DIR'))
	{
		define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
	}

	if(file_exists(WP_PLUGIN_DIR . '/ldb-wp-e-commerce-ideal/ldb-wp-e-commerce-ideal.php'))
	{
		include(WP_PLUGIN_DIR . '/ldb-wp-e-commerce-ideal/ldb-wp-e-commerce-ideal.php');
	}

?>