<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}
 delete_option('flipkart_importer_setting');
 delete_option('flipkartImporter_message');
 ?>