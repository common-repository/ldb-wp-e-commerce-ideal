<?php 

/*
Plugin Name: LDB WP e-Commerce iDEAL
Plugin URI: http://www.lucdebrouwer.nl/wordpress-plugin-ldb-wp-e-commerce-ideal/
Description: LDB WP e-Commerce iDEAL allows you to easily add the iDEAL payment gateway to WP e-Commerce for several Dutch banks and iDEAL integrations.
Version: 2.0.3
Author: Luc De Brouwer
Author URI: http://www.lucdebrouwer.nl
*/

if (!class_exists('LDB_iDEAL_Loader'))
{
	class LDB_iDEAL_Loader {

		function load()
		{

			// Init options & tables during activation & deregister init option
			register_activation_hook( __file__, array(&$this, 'activate' ));
			register_deactivation_hook( __file__, array(&$this, 'deactivate' ));
			if(get_option('ldb_ideal_msg'))
			{
				add_action('admin_notices', $this->echo_error());
				delete_option('ldb_ideal_msg');
			}
		}

		function echo_error()
		{
			echo '<div id="message" class="error"><p>' . get_option('ldb_ideal_msg') . '</p></div>';
		}

		/*
			Activate the plugin
		*/
		function activate()
		{
			$LDBDir = dirname(__file__);
			if(get_option('wpsc_version')){
				if(floatval(get_option('wpsc_version'))>3.7){
					$pluginDir = dirname(dirname(__file__)) . '/wp-e-commerce/wpsc-merchants';
				} else {
					$pluginDir = dirname(dirname(__file__)) . '/wp-e-commerce/merchants';
				}
			} else {
				$pluginDir = dirname(dirname(__file__)) . '/wp-e-commerce/merchants';
			}
			$sourceFile = $LDBDir . '/inc-ldb-wp-e-commerce-ideal.php';
			$destinationFile = $pluginDir . '/inc-ldb-wp-e-commerce-ideal.php';

			// Copy the file to the WP e-Commerce merchants folder
			if(file_exists($pluginDir))
			{
				@copy($sourceFile, $destinationFile);
					if(!file_exists($destinationFile))
					{
						if(get_option('ldb_ideal_msg'))
						{
							update_option('ldb_ideal_msg', '<strong>LDB WP e-Commerce iDEAL :</strong> Please copy inc-ldb-wp-e-commerce-ideal.php manually to wp-e-commerce/merchants.');
						} else {
							add_option('ldb_ideal_msg', '<strong>LDB WP e-Commerce iDEAL :</strong> Please copy inc-ldb-wp-e-commerce-ideal.php manually to wp-e-commerce/merchants.');
						}
					}
			} else {
				if(get_option('ldb_ideal_msg'))
				{
					update_option('ldb_ideal_msg', "WP e-Commerce wasn't found, please install it first.");
				} else {
					add_option('ldb_ideal_msg', "WP e-Commerce wasn't found, please install it first.");
				}
			}
		}

		/*
			Deactivate the plugin
		*/
		function deactivate()
		{
			$wpsc_plugin_dir = dirname(dirname(__file__)) . '/wp-e-commerce/merchants';
			if(file_exists($wpsc_plugin_dir . '/inc-ldb-wp-e-commerce-ideal.php'))
			{
				unlink($wpsc_plugin_dir.'/inc-ldb-wp-e-commerce-ideal.php');
			}
			
		}
	}

	$LDB_iDEAL_Loader = new LDB_iDEAL_Loader();
	$LDB_iDEAL_Loader->load();

}
?>